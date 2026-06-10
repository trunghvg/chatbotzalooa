<?php

namespace App\Libraries;

use App\Models\KnowledgeModel;

/**
 * Import kien thuc tu nhieu dinh dang file:
 *   - Excel (.xlsx, .xls)  → ExcelKnowledgeImporter
 *   - Word   (.docx)       → parse XML trong ZIP
 *   - PDF    (.pdf)        → pdftotext (poppler-utils)
 *   - Text   (.txt)        → doc thang
 *
 * Tai lieu dai duoc chia thanh nhieu doan (chunk), moi doan goi Claude AI
 * rieng de dam bao toan bo noi dung deu duoc xu ly (khong bi cat bot).
 */
class DocumentImporter
{
    private const API_BASE = 'https://api.anthropic.com/v1';
    private const VERSION  = '2023-06-01';

    // Kich thuoc moi doan van ban gui cho Claude AI (ky tu)
    private const CHUNK_CHARS = 18000;

    private KnowledgeModel $model;

    public function __construct()
    {
        $this->model = new KnowledgeModel();
    }

    /**
     * Import file va tra ve so muc da luu
     */
    public function import(string $filePath, string $originalName, bool $replaceAll = false): int
    {
        // Tai lieu lon co the mat vai phut de xu ly tung doan qua Claude AI
        @set_time_limit(0);

        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        // Excel dung importer rieng
        if (in_array($ext, ['xlsx', 'xls'])) {
            if ($replaceAll) {
                $this->model->where('1=1')->delete();
            }
            $importer = new ExcelKnowledgeImporter();
            return $importer->import($filePath);
        }

        $text = match ($ext) {
            'docx'       => $this->extractFromDocx($filePath),
            'doc'        => $this->extractFromDocx($filePath),
            'pdf'        => $this->extractFromPdf($filePath),
            'txt'        => (string) file_get_contents($filePath),
            default      => throw new \RuntimeException("Định dạng .$ext chưa được hỗ trợ."),
        };

        if (empty(trim($text))) {
            throw new \RuntimeException("Không trích xuất được nội dung từ file. File có thể bị lỗi hoặc chỉ chứa hình ảnh.");
        }

        if ($replaceAll) {
            $this->model->where('1=1')->delete();
        }

        return $this->extractWithClaude($text, $originalName);
    }

    // ----------------------------------------------------------------
    // TRICH XUAT VAN BAN TU WORD (.docx)
    // ----------------------------------------------------------------
    private function extractFromDocx(string $filePath): string
    {
        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \RuntimeException("Không thể mở file Word.");
        }

        $content = $zip->getFromName('word/document.xml');
        $zip->close();

        if (!$content) {
            throw new \RuntimeException("Không tìm thấy nội dung trong file Word.");
        }

        // Xuong dong theo paragraph va line break
        $content = preg_replace('/<w:p[ >]/', "\n", $content);
        $content = preg_replace('/<w:br[^>]*\/>/', "\n", $content);
        $content = preg_replace('/<w:tab\/>/', "\t", $content);
        $content = strip_tags($content);
        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $content = preg_replace('/\n{3,}/', "\n\n", $content);

        return trim($content);
    }

    // ----------------------------------------------------------------
    // TRICH XUAT VAN BAN TU PDF
    // ----------------------------------------------------------------
    private function extractFromPdf(string $filePath): string
    {
        $safeFile = escapeshellarg($filePath);
        $output   = shell_exec("pdftotext -enc UTF-8 -layout $safeFile - 2>/dev/null");

        if ($output && mb_strlen(trim($output)) > 20) {
            return trim($output);
        }

        throw new \RuntimeException(
            "Không đọc được file PDF. Có thể file bị mã hóa hoặc chỉ chứa ảnh scan. "
            . "Vui lòng chuyển sang file Word (.docx) hoặc Text (.txt)."
        );
    }

    // ----------------------------------------------------------------
    // CHIA VAN BAN DAI THANH NHIEU DOAN (theo trang / doan van)
    // ----------------------------------------------------------------
    private function splitIntoChunks(string $text, int $maxChars): array
    {
        // pdftotext chen ky tu form-feed (\f) giua cac trang
        $units = str_contains($text, "\f")
            ? explode("\f", $text)
            : preg_split('/\n{2,}/', $text);

        $chunks  = [];
        $current = '';

        foreach ($units as $unit) {
            $unit = trim($unit);
            if ($unit === '') {
                continue;
            }

            // Don vi (trang/doan) qua dai → tach nho hon theo doan van
            if (mb_strlen($unit) > $maxChars) {
                $subUnits = preg_split('/\n{2,}/', $unit);
                foreach ($subUnits as $sub) {
                    $sub = trim($sub);
                    if ($sub === '') {
                        continue;
                    }
                    if (mb_strlen($sub) > $maxChars) {
                        // Cau qua dai → cat cung theo so ky tu
                        $sub = mb_substr($sub, 0, $maxChars);
                    }
                    if (mb_strlen($current) + mb_strlen($sub) + 2 > $maxChars) {
                        if ($current !== '') {
                            $chunks[] = $current;
                        }
                        $current = $sub;
                    } else {
                        $current .= ($current === '' ? '' : "\n\n") . $sub;
                    }
                }
                continue;
            }

            if (mb_strlen($current) + mb_strlen($unit) + 2 > $maxChars) {
                if ($current !== '') {
                    $chunks[] = $current;
                }
                $current = $unit;
            } else {
                $current .= ($current === '' ? '' : "\n\n") . $unit;
            }
        }

        if (trim($current) !== '') {
            $chunks[] = $current;
        }

        return $chunks;
    }

    // ----------------------------------------------------------------
    // PHAN TICH TOAN BO TAI LIEU (chia doan, goi Claude AI cho tung doan)
    // ----------------------------------------------------------------
    private function extractWithClaude(string $text, string $sourceName): int
    {
        $chunks      = $this->splitIntoChunks($text, self::CHUNK_CHARS);
        $totalChunks = count($chunks);
        $sortOrder   = 0;
        $totalSaved  = 0;

        foreach ($chunks as $i => $chunk) {
            $entries = $this->callClaude($chunk, $totalChunks > 1 ? $i + 1 : 0, $totalChunks);

            if (empty($entries)) {
                log_message('warning', "[DocumentImporter] Chunk " . ($i + 1) . "/$totalChunks returned [], retrying with simple prompt");
                $entries = $this->callClaudeSimple($chunk);
            }

            $totalSaved += $this->saveEntries($entries, $sourceName, $sortOrder);
        }

        if ($totalSaved === 0) {
            throw new \RuntimeException(
                "Claude AI không trích xuất được nội dung nào từ tài liệu. "
                . "Vui lòng kiểm tra lại nội dung file."
            );
        }

        return $totalSaved;
    }

    /**
     * Goi Claude AI voi prompt chi tiet cho 1 doan van ban
     * Tra ve mang entries (co the rong neu loi)
     */
    private function callClaude(string $text, int $chunkNum, int $totalChunks): array
    {
        $partNote = $totalChunks > 1
            ? "\n\nLƯU Ý: Đây là phần $chunkNum/$totalChunks của một tài liệu dài hơn. Chỉ trích xuất nội dung có trong phần này."
            : '';

        $prompt = <<<EOT
Bạn là chuyên gia phân tích văn bản hành chính phục vụ hệ thống chatbot Phường Lê Chân, Hải Phòng.

Hãy đọc nội dung tài liệu dưới đây và trích xuất TẤT CẢ thông tin hữu ích thành các mục kiến thức riêng biệt, kể cả khi tài liệu không nhắc đến tên "Phường Lê Chân" — hệ thống sẽ sử dụng nội dung này để trả lời người dân.{$partNote}

YÊU CẦU:
1. Trích xuất TẤT CẢ nội dung có giá trị: quy chế, quy định, thủ tục, lịch học, đối tượng, điều kiện, thông tin liên hệ, văn bản pháp lý, số liệu, kết quả, kế hoạch, v.v.
2. Mỗi mục là một chủ đề độc lập, đủ để trả lời một câu hỏi cụ thể của người dân
3. Viết theo văn phong hành chính nhà nước, trang trọng, rõ ràng
4. TUYỆT ĐỐI không dùng ký hiệu markdown (*, **, #, ##) trong trường content
5. Danh mục (category) chọn một trong: quy-che, lich-hoc, thu-tuc, lien-he, khoa-hoc, quy-dinh, to-chuc, hoat-dong, chung
6. Nếu phần này không có nội dung đáng kể, trả về mảng rỗng []
7. Tối đa 25 mục

Trả về JSON array. Mỗi phần tử có các trường:
- "title": tiêu đề ngắn gọn mô tả nội dung (tối đa 150 ký tự)
- "category": danh mục slug (một trong các giá trị trên)
- "content": nội dung đầy đủ, trình bày rõ ràng (không dùng markdown)
- "keywords": các từ khóa tìm kiếm quan trọng, cách nhau bằng dấu phẩy

NỘI DUNG:
$text

Trả về CHỈ JSON array hợp lệ, bắt đầu bằng [ và kết thúc bằng ]. Không có text giải thích.
EOT;

        $jsonText = $this->callClaudeAPI(
            $prompt,
            'Bạn là chuyên gia phân tích văn bản hành chính nhà nước. Trả về chỉ JSON array hợp lệ.'
        );

        return $this->parseEntries($jsonText);
    }

    /**
     * Fallback: prompt don gian hon cho 1 doan van ban
     */
    private function callClaudeSimple(string $text): array
    {
        $prompt = <<<EOT
Tóm tắt và trích xuất nội dung tài liệu sau thành các mục thông tin. Trả về JSON array, mỗi phần tử gồm: title (string), category (string, ví dụ: "chung"), content (string, không dùng *, #), keywords (string).

Tài liệu:
$text

Chỉ trả về JSON array.
EOT;

        $jsonText = $this->callClaudeAPI($prompt, 'Trả về chỉ JSON array hợp lệ.');
        $entries  = $this->parseEntries($jsonText);

        if (!empty($entries)) {
            return $entries;
        }

        // Phuong an cuoi: luu nguyen doan van ban thanh 1 muc
        $trimmed = trim($text);
        if ($trimmed === '') {
            return [];
        }

        return [[
            'title'    => mb_substr($trimmed, 0, 100),
            'category' => 'chung',
            'content'  => mb_substr($trimmed, 0, 3000),
            'keywords' => '',
        ]];
    }

    /**
     * Goi Claude API, tra ve text response
     */
    private function callClaudeAPI(string $prompt, string $systemPrompt): string
    {
        $apiKey = env('CLAUDE_API_KEY', '');
        $model  = env('CLAUDE_MODEL', 'claude-opus-4-7');

        $payload = [
            'model'      => $model,
            'max_tokens' => 4096,
            'system'     => $systemPrompt,
            'messages'   => [['role' => 'user', 'content' => $prompt]],
        ];

        $ch = curl_init(self::API_BASE . '/messages');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_HTTPHEADER     => [
                'x-api-key: ' . $apiKey,
                'anthropic-version: ' . self::VERSION,
                'content-type: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!$response || $httpCode !== 200) {
            throw new \RuntimeException("Lỗi gọi Claude AI (HTTP $httpCode). Kiểm tra CLAUDE_API_KEY.");
        }

        $result = json_decode($response, true);
        $text   = $result['content'][0]['text'] ?? '';

        log_message('info', '[DocumentImporter] Claude raw (' . mb_strlen($text) . ' chars): ' . mb_substr($text, 0, 300));

        return $text;
    }

    /**
     * Parse JSON array tu response cua Claude
     */
    private function parseEntries(string $jsonText): array
    {
        if (preg_match('/\[.*\]/su', $jsonText, $matches)) {
            $jsonText = $matches[0];
        }

        $entries = json_decode($jsonText, true);

        return is_array($entries) ? $entries : [];
    }

    /**
     * Luu danh sach entries vao DB, tang $sortOrder theo tham chieu
     */
    private function saveEntries(array $entries, string $sourceName, int &$sortOrder): int
    {
        $now   = date('Y-m-d H:i:s');
        $count = 0;

        foreach ($entries as $entry) {
            $title   = trim($entry['title']   ?? '');
            $content = trim($entry['content'] ?? '');

            if (empty($title) || empty($content)) {
                continue;
            }

            $sortOrder += 10;

            $this->model->insert([
                'title'           => mb_substr($title, 0, 300),
                'category'        => $entry['category'] ?? 'chung',
                'source_document' => mb_substr(pathinfo($sourceName, PATHINFO_FILENAME), 0, 200),
                'content'         => $content,
                'keywords'        => mb_substr($entry['keywords'] ?? '', 0, 1000),
                'is_active'       => 1,
                'sort_order'      => $sortOrder,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);

            $count++;
        }

        return $count;
    }
}
