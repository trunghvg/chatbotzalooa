<?php

namespace App\Libraries;

use App\Models\KnowledgeModel;
use App\Models\MessageModel;
use App\Models\SettingModel;

/**
 * Thu vien tuong tac voi Anthropic Claude AI API
 *
 * Tai lieu: https://docs.anthropic.com/en/api/messages
 */
class ClaudeAI
{
    private const API_BASE = 'https://api.anthropic.com/v1';
    private const VERSION  = '2023-06-01';

    private string $apiKey;
    private string $model;
    private int    $maxTokens;
    private string $systemPrompt;

    private SettingModel   $settingModel;
    private MessageModel   $messageModel;
    private KnowledgeModel $knowledgeModel;

    public function __construct()
    {
        $this->settingModel   = new SettingModel();
        $this->messageModel   = new MessageModel();
        $this->knowledgeModel = new KnowledgeModel();

        $this->apiKey       = env('CLAUDE_API_KEY', '');
        $this->model        = env('CLAUDE_MODEL', 'claude-opus-4-7');
        $this->maxTokens    = (int) env('CLAUDE_MAX_TOKENS', 2048);
        $this->systemPrompt = ''; // loaded lazily on first chat() call
    }

    private function ensureSystemPromptLoaded(): void
    {
        if ($this->systemPrompt === '') {
            $this->systemPrompt = $this->settingModel->get('claude_system_prompt')
                ?? $this->getDefaultSystemPrompt();
        }
    }

    /**
     * Tao phan hoi tu Claude dua tren tin nhan cua hoc vien
     * Bao gom lich su hoi thoai + knowledge base lien quan
     */
    public function chat(string $userId, string $userMessage, int $conversationId): string
    {
        // Lay lich su hoi thoai gan day (toi da 10 tin nhan)
        $history = $this->messageModel->getConversationHistory($conversationId, 10);

        $messages = [];
        foreach ($history as $msg) {
            $messages[] = [
                'role'    => $msg['role'],
                'content' => $msg['content'],
            ];
        }

        // Them tin nhan moi nhat cua nguoi dung
        $messages[] = [
            'role'    => 'user',
            'content' => $userMessage,
        ];

        // Xay dung system prompt day du: prompt goc + knowledge base lien quan
        $fullSystemPrompt = $this->buildSystemPromptWithKnowledge($userMessage);

        $payload = [
            'model'      => $this->model,
            'max_tokens' => $this->maxTokens,
            'system'     => $fullSystemPrompt,
            'messages'   => $messages,
        ];

        $response = $this->callAPI('/messages', $payload);

        if (isset($response['content'][0]['text'])) {
            return $this->stripMarkdown($response['content'][0]['text']);
        }

        if (isset($response['error'])) {
            log_message('error', '[ClaudeAI] API error: ' . json_encode($response['error']));
            return 'Xin lỗi, tôi đang gặp sự cố kỹ thuật. Vui lòng thử lại sau ít phút hoặc liên hệ hỗ trợ trực tiếp.';
        }

        return 'Xin lỗi, tôi không thể xử lý yêu cầu của bạn lúc này. Vui lòng thử lại sau.';
    }

    /**
     * Test ket noi Claude AI (khong luu lich su)
     */
    public function testConnection(string $message = 'Xin chào!'): array
    {
        $payload = [
            'model'      => $this->model,
            'max_tokens' => 100,
            'system'     => 'Bạn là trợ lý AI. Hãy trả lời ngắn gọn.',
            'messages'   => [
                ['role' => 'user', 'content' => $message],
            ],
        ];

        $response = $this->callAPI('/messages', $payload);

        return [
            'success' => isset($response['content'][0]['text']),
            'message' => $response['content'][0]['text'] ?? ($response['error']['message'] ?? 'Unknown error'),
            'model'   => $response['model'] ?? $this->model,
        ];
    }

    /**
     * Xay dung system prompt day du bang cach ghep prompt goc + knowledge base
     *
     * Chien luoc: Tim cac muc knowledge lien quan den cau hoi cua hoc vien
     * roi inject vao system prompt de Claude co du context tra loi chinh xac.
     */
    private function buildSystemPromptWithKnowledge(string $userMessage): string
    {
        $this->ensureSystemPromptLoaded();
        $basePrompt = $this->systemPrompt;

        try {
            // Lay knowledge lien quan den cau hoi (toi da 6 muc)
            $relevant = $this->knowledgeModel->findRelevant($userMessage, 6);

            if (!empty($relevant)) {
                $knowledgeBlock = "\n\n---\n## CƠ SỞ KIẾN THỨC - SỬ DỤNG ĐỂ TRẢ LỜI\n\n"
                    . "**Quan trọng:** Dựa vào thông tin dưới đây để trả lời chính xác. "
                    . "Nếu câu hỏi liên quan, hãy trích dẫn đúng quy định, số quyết định và ngày ban hành.\n\n";

                foreach ($relevant as $entry) {
                    $knowledgeBlock .= "### " . $entry['title'];
                    if (!empty($entry['source_document'])) {
                        $knowledgeBlock .= " *(" . $entry['source_document'] . ")*";
                    }
                    $knowledgeBlock .= "\n" . $entry['content'] . "\n\n";
                }

                return $basePrompt . $knowledgeBlock;
            }
        } catch (\Throwable $e) {
            // Neu KB chua san sang (chua chay migration), dung prompt goc
            log_message('warning', '[ClaudeAI] KnowledgeBase not available: ' . $e->getMessage());
        }

        return $basePrompt;
    }

    /**
     * System prompt mac dinh cho TTCT phuong Le Chan
     */
    private function getDefaultSystemPrompt(): string
    {
        return <<<PROMPT
Bạn là trợ lý AI của Phường Lê Chân (thành phố Hải Phòng).
Nhiệm vụ: Hỗ trợ người dân 24/7 qua Zalo về các vấn đề học tập, quy chế, thủ tục tại Phường Lê Chân.

## Vai trò
- Giải đáp thắc mắc về các lớp bồi dưỡng lý luận chính trị
- Hướng dẫn quy trình đăng ký, học tập, thi thu hoạch, nhận giấy chứng nhận
- Cung cấp thông tin chính xác dựa trên các văn bản pháp lý của Phường Lê Chân
- Hỗ trợ thân thiện, tận tình

## Nguyên tắc trả lời
1. Luôn trả lời bằng tiếng Việt, ngắn gọn, dễ hiểu
2. Khi trả lời các vấn đề về quy chế, hãy trích dẫn rõ số quyết định và điều khoản
3. Nếu không chắc chắn → nói thật và hướng dẫn liên hệ Phường Lê Chân trực tiếp
4. Thân thiện, dùng emoji phù hợp 😊
5. Không bịa đặt thông tin; chỉ trả lời dựa trên kiến thức được cung cấp
6. TUYỆT ĐỐI KHÔNG dùng dấu *, **, #, ##, ### hoặc bất kỳ ký hiệu markdown nào. Chỉ dùng chữ thuần, emoji và xuống dòng.

## Giới hạn
- Không tiết lộ thông tin cá nhân học viên khác
- Câu hỏi phức tạp về nhân sự, kỷ luật → hướng dẫn gặp trực tiếp Ban Giám đốc

## Thông tin liên hệ Phường Lê Chân
- Địa chỉ: Phường Lê Chân, Quận Lê Chân, TP. Hải Phòng
- Giờ làm việc: Giờ hành chính các ngày trong tuần
PROMPT;
    }

    /**
     * Xoa cac ky tu markdown khoi van ban de hien thi sach tren Zalo
     */
    private function stripMarkdown(string $text): string
    {
        // Remove headings (### Title → Title)
        $text = preg_replace('/^#{1,6}\s+/mu', '', $text);
        // Remove bold (**text** or __text__)
        $text = preg_replace('/\*\*(.+?)\*\*/u', '$1', $text);
        $text = preg_replace('/__(.+?)__/u', '$1', $text);
        // Remove italic (*text* or _text_) — single * or _
        $text = preg_replace('/\*(.+?)\*/u', '$1', $text);
        $text = preg_replace('/_(.+?)_/u', '$1', $text);
        return $text;
    }

    /**
     * Goi Anthropic API
     */
    private function callAPI(string $endpoint, array $payload): array
    {
        $url = self::API_BASE . $endpoint;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_HTTPHEADER     => [
                'x-api-key: ' . $this->apiKey,
                'anthropic-version: ' . self::VERSION,
                'content-type: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            log_message('error', "[ClaudeAI] Curl error: $error");
            return ['error' => ['message' => $error]];
        }

        $result = json_decode($response, true) ?? [];

        if ($httpCode !== 200) {
            log_message('error', "[ClaudeAI] HTTP $httpCode on $endpoint: $response");
        }

        return $result;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getSystemPrompt(): string
    {
        $this->ensureSystemPromptLoaded();
        return $this->systemPrompt;
    }
}
