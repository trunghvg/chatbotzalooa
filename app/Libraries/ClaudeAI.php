<?php

namespace App\Libraries;

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

    private SettingModel $settingModel;
    private MessageModel $messageModel;

    public function __construct()
    {
        $this->settingModel = new SettingModel();
        $this->messageModel = new MessageModel();

        $this->apiKey    = env('CLAUDE_API_KEY', '');
        $this->model     = env('CLAUDE_MODEL', 'claude-opus-4-7');
        $this->maxTokens = (int) env('CLAUDE_MAX_TOKENS', 2048);

        // Lay system prompt tu DB (co the chinh sua qua admin)
        $this->systemPrompt = $this->settingModel->get('claude_system_prompt')
            ?? $this->getDefaultSystemPrompt();
    }

    /**
     * Tao phan hoi tu Claude dua tren tin nhan cua hoc vien
     * Bao gom lich su hoi thoai de tro chuyen co nguon canh
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

        $payload = [
            'model'      => $this->model,
            'max_tokens' => $this->maxTokens,
            'system'     => $this->systemPrompt,
            'messages'   => $messages,
        ];

        $response = $this->callAPI('/messages', $payload);

        if (isset($response['content'][0]['text'])) {
            return $response['content'][0]['text'];
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
     * System prompt mac dinh cho giao vien AI cua khoa hoc
     */
    private function getDefaultSystemPrompt(): string
    {
        return <<<PROMPT
Bạn là trợ lý AI thông minh của một trung tâm đào tạo. Nhiệm vụ của bạn là hỗ trợ học viên 24/7 qua Zalo.

## Vai trò
- Tư vấn viên học tập thân thiện, chuyên nghiệp
- Giải đáp thắc mắc về khóa học, lịch học, bài tập
- Hỗ trợ kỹ thuật cơ bản cho nền tảng học trực tuyến
- Động viên và hỗ trợ tinh thần học viên

## Nguyên tắc trả lời
1. Luôn trả lời bằng tiếng Việt
2. Thân thiện, kiên nhẫn, dễ hiểu
3. Câu trả lời ngắn gọn, súc tích (dưới 500 từ nếu có thể)
4. Dùng emoji phù hợp để tạo cảm giác gần gũi 😊
5. Nếu không biết, hãy nói thật và hướng dẫn liên hệ hỗ trợ trực tiếp

## Giới hạn
- Không tiết lộ thông tin cá nhân của học viên khác
- Không hứa hẹn điều bạn không chắc chắn
- Với câu hỏi phức tạp về học phí, hợp đồng → hướng dẫn liên hệ nhân viên

## Thông tin liên hệ hỗ trợ
- Hotline: [Cấu hình trong Admin > Cài đặt]
- Email: [Cấu hình trong Admin > Cài đặt]
- Giờ làm việc: 8:00 - 22:00 mỗi ngày
PROMPT;
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
        return $this->systemPrompt;
    }
}
