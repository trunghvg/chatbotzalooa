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
 * Sau khi lay van ban tho, dung Claude AI phan tich va tao knowledge entries
 * phu hop voi hoat dong cua Trung tam chinh tri phuong Le Chan.
 */
class DocumentImporter
{
    private const API_BASE = 'https://api.anthropic.com/v1';
    private const VERSION  = '2023-06-01';
    private const MAX_TEXT_CHARS = 14000;

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
    // PHAN TICH VA CAU TRUC BANG CLAUDE AI
    // ----------------------------------------------------------------
    private function extractWithClaude(string $text, string $sourceName): int
    {
        // Gioi han do dai van ban
        $text = mb_substr($text, 0, self::MAX_TEXT_CHARS);

        $prompt = <<<EOT
Bạn là chuyên gia phân tích văn bản hành chính của Phường Lê Chân, thành phố Hải Phòng.

Hãy đọc tài liệu dưới đây và trích xuất các mục kiến thức để đưa vào hệ thống hỗ trợ người dân qua chatbot.

YÊU CẦU:
1. Chỉ trích xuất nội dung liên quan đến hoạt động của Phường Lê Chân: đào tạo lý luận chính trị, quy chế học tập, thủ tục hành chính, tổ chức, lịch học, thông tin liên hệ
2. Viết theo văn phong chính trị - nhà nước, trang trọng, rõ ràng
3. TUYỆT ĐỐI không dùng ký hiệu markdown (*, **, #, ##) trong trường content
4. Mỗi mục phải đầy đủ, có thể trả lời độc lập cho người dân khi hỏi
5. Danh mục (category) chọn một trong: quy-che, lich-hoc, thu-tuc, lien-he, khoa-hoc, quy-dinh, to-chuc, hoat-dong, chung

Trả về JSON array. Mỗi phần tử có các trường:
- "title": tiêu đề ngắn gọn (tối đa 150 ký tự)
- "category": danh mục slug
- "content": nội dung chi tiết (không dùng markdown)
- "keywords": các từ khóa tìm kiếm, cách nhau bằng dấu phẩy

TÀI LIỆU:
$text

Trả về CHỈ JSON array hợp lệ, không có text giải thích trước hoặc sau.
EOT;

        $apiKey = env('CLAUDE_API_KEY', '');
        $model  = env('CLAUDE_MODEL', 'claude-opus-4-7');

        $payload = [
            'model'      => $model,
            'max_tokens' => 4096,
            'system'     => 'Bạn là chuyên gia phân tích văn bản hành chính nhà nước. Trả về chỉ JSON array hợp lệ.',
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

        $result   = json_decode($response, true);
        $jsonText = $result['content'][0]['text'] ?? '';

        // Lay phan JSON tu response (de phong Claude them text ngoai)
        if (preg_match('/\[.*\]/su', $jsonText, $matches)) {
            $jsonText = $matches[0];
        }

        $entries = json_decode($jsonText, true);
        if (!is_array($entries) || empty($entries)) {
            log_message('error', '[DocumentImporter] Claude response: ' . mb_substr($jsonText, 0, 500));
            throw new \RuntimeException(
                "Claude AI không trả về dữ liệu hợp lệ. "
                . "Preview: " . mb_substr($jsonText, 0, 200)
            );
        }

        $now   = date('Y-m-d H:i:s');
        $count = 0;

        foreach ($entries as $i => $entry) {
            $title   = trim($entry['title']   ?? '');
            $content = trim($entry['content'] ?? '');

            if (empty($title) || empty($content)) {
                continue;
            }

            $this->model->insert([
                'title'           => mb_substr($title, 0, 300),
                'category'        => $entry['category'] ?? 'chung',
                'source_document' => mb_substr(pathinfo($sourceName, PATHINFO_FILENAME), 0, 200),
                'content'         => $content,
                'keywords'        => mb_substr($entry['keywords'] ?? '', 0, 1000),
                'is_active'       => 1,
                'sort_order'      => ($i + 1) * 10,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);

            $count++;
        }

        return $count;
    }
}
