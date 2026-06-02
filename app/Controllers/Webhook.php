<?php

namespace App\Controllers;

use App\Libraries\ClaudeAI;
use App\Libraries\ZaloOA;
use App\Models\ConversationModel;
use App\Models\MessageModel;
use App\Models\SettingModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Xu ly Webhook tu Zalo OA
 *
 * GET  /webhook → Xac minh webhook URL (Zalo verification)
 * POST /webhook → Nhan tin nhan tu nguoi dung
 */
class Webhook extends BaseController
{
    private ZaloOA            $zalo;
    private ClaudeAI          $claude;
    private ConversationModel $conversationModel;
    private MessageModel      $messageModel;
    private SettingModel      $settingModel;

    public function __construct()
    {
        $this->zalo              = new ZaloOA();
        $this->claude            = new ClaudeAI();
        $this->conversationModel = new ConversationModel();
        $this->messageModel      = new MessageModel();
        $this->settingModel      = new SettingModel();
    }

    // ----------------------------------------------------------------
    // XAC MINH WEBHOOK (GET)
    // Zalo gui GET request den URL webhook de xac minh
    // ----------------------------------------------------------------
    public function verify(): ResponseInterface
    {
        $challenge = $this->request->getGet('challenge');

        if ($challenge) {
            // Tra lai challenge de xac minh URL
            return $this->response
                ->setContentType('text/plain')
                ->setBody($challenge);
        }

        // Hien thi trang thong tin neu truy cap truc tiep
        return $this->response
            ->setContentType('application/json')
            ->setBody(json_encode([
                'status'  => 'ok',
                'message' => 'Zalo OA Webhook is running',
                'time'    => date('Y-m-d H:i:s'),
            ]));
    }

    // ----------------------------------------------------------------
    // NHAN TIN NHAN (POST)
    // ----------------------------------------------------------------
    public function receive(): ResponseInterface
    {
        $startTime = microtime(true);
        $rawBody   = $this->request->getBody();

        // Respond 200 OK to Zalo immediately so it never times out
        http_response_code(200);
        header('Content-Type: application/json');
        echo '{"status":"ok"}';

        // Flush response to client now, keep processing in background
        // PHP-FPM (nginx/Docker) dung fastcgi_finish_request
        // LiteSpeed / OpenLiteSpeed (CyberPanel) dung litespeed_finish_request
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } elseif (function_exists('litespeed_finish_request')) {
            litespeed_finish_request();
        } else {
            // Apache mod_php / fallback: flush output buffer
            if (session_id()) {
                session_write_close();
            }
            @ob_end_flush();
            @flush();
        }

        // --- Everything below runs after Zalo already received 200 ---
        try {
            $signature = $this->request->getHeaderLine('X-Zevent-Signature')
                      ?: $this->request->getGet('mac');
            if ($signature && !$this->zalo->verifyWebhook($rawBody, $signature)) {
                log_message('warning', '[Webhook] Signature mismatch — check ZALO_APP_SECRET');
            }

            $payload = json_decode($rawBody, true);
            if (!$payload) {
                log_message('info', '[Webhook] Non-JSON POST: ' . substr($rawBody, 0, 200));
                return $this->response->setStatusCode(200);
            }

            $eventName = $payload['event_name'] ?? 'unknown';
            $senderLog = $payload['sender'] ?? $payload['follower'] ?? [];
            log_message('info', '[Webhook] Event: ' . $eventName
                . ' sender_id=' . ($senderLog['id'] ?? '?')
                . ' name=' . ($senderLog['display_name'] ?? $senderLog['name'] ?? '(none)'));

            $eventName = $payload['event_name'] ?? $eventName;

            match ($eventName) {
                'user_send_text'  => $this->handleTextMessage($payload, $startTime),
                'user_send_image' => $this->handleImageMessage($payload),
                'user_send_sticker', 'user_send_audio', 'user_send_video',
                'user_send_file'  => $this->handleMediaMessage($payload, $eventName),
                'follow'          => $this->handleFollow($payload),
                'unfollow'        => $this->handleUnfollow($payload),
                default           => null,
            };
        } catch (\Throwable $e) {
            log_message('error', '[Webhook] Processing error: ' . $e->getMessage());
        }

        return $this->response->setStatusCode(200);
    }

    // ----------------------------------------------------------------
    // XU LY TIN NHAN VAN BAN
    // ----------------------------------------------------------------
    private function handleTextMessage(array $payload, float $startTime): ResponseInterface
    {
        $senderId    = $payload['sender']['id']      ?? $payload['user_id_by_app'] ?? '';
        $messageText = $payload['message']['text']   ?? '';
        $zaloMsgId   = $payload['message']['msg_id'] ?? '';

        if (empty($senderId) || empty($messageText)) {
            return $this->jsonResponse(['error' => 'Missing sender or message'], 400);
        }

        // Lay ten tu webhook payload truoc (khong can API call, khong can permission)
        $senderName   = trim($payload['sender']['display_name'] ?? $payload['sender']['name'] ?? '');
        $senderAvatar = trim($payload['sender']['avatar']       ?? '');

        // Neu co ten tu payload → dung luon, khong can goi getUserProfile
        if ($senderName) {
            $userProfile = [
                'data' => [
                    'display_name' => $senderName,
                    'avatar'       => $senderAvatar,
                ],
            ];
        } else {
            // Fallback: goi API (can quyen manage_followers)
            $userProfile = [];
            try {
                $userProfile = $this->zalo->getUserProfile($senderId);
            } catch (\Throwable $e) {
                log_message('warning', '[Webhook] Could not fetch user profile: ' . $e->getMessage());
            }
        }

        $conversation = $this->conversationModel->findOrCreate($senderId, $userProfile);
        $convId       = $conversation['id'];

        // Luu tin nhan cua nguoi dung
        $this->messageModel->saveMessage([
            'conversation_id' => $convId,
            'role'            => 'user',
            'content'         => $messageText,
            'zalo_msg_id'     => $zaloMsgId,
        ]);

        // Kiem tra bot co dang bat khong
        $botEnabled = $this->settingModel->get('bot_enabled', '1');
        if ($botEnabled !== '1') {
            log_message('info', "[Webhook] Bot is disabled, skipping reply for user $senderId");
            return $this->jsonResponse(['status' => 'bot_disabled']);
        }

        // Kiem tra tu khoa dac biet (chuyen sang nhan vien)
        $transferKeywords = $this->settingModel->get('transfer_keywords', '');
        if ($transferKeywords && $this->containsKeyword($messageText, $transferKeywords)) {
            return $this->handleTransferToHuman($senderId, $convId);
        }

        // Goi Claude AI
        try {
            $aiResponse = $this->claude->chat($senderId, $messageText, $convId);
        } catch (\Throwable $e) {
            log_message('error', '[Webhook] Claude AI error: ' . $e->getMessage());
            $aiResponse = 'Xin lỗi, hệ thống đang bận. Vui lòng thử lại sau ít phút!';
        }

        $processingTime = (int) ((microtime(true) - $startTime) * 1000);

        // Luu phan hoi cua bot
        $this->messageModel->saveMessage([
            'conversation_id'    => $convId,
            'role'               => 'assistant',
            'content'            => $aiResponse,
            'processing_time_ms' => $processingTime,
        ]);

        // Cap nhat cuoc hoi thoai
        $this->conversationModel->updateLastMessage($convId, $messageText);

        // Gui phan hoi ve Zalo — log ket qua de debug
        $sendResult = $this->zalo->sendTextMessage($senderId, $aiResponse);
        if (!empty($sendResult['error']) && $sendResult['error'] !== 0) {
            log_message('error', "[Webhook] sendTextMessage FAILED to $senderId: " . json_encode($sendResult));
        } else {
            log_message('info', "[Webhook] Replied to $senderId in {$processingTime}ms: " . json_encode($sendResult));
        }

        return $this->jsonResponse(['status' => 'ok', 'processing_ms' => $processingTime]);
    }

    // ----------------------------------------------------------------
    // XU LY TIN NHAN HINH ANH
    // ----------------------------------------------------------------
    private function handleImageMessage(array $payload): ResponseInterface
    {
        $senderId = $payload['sender']['id'] ?? $payload['user_id_by_app'] ?? '';
        if (empty($senderId)) {
            return $this->jsonResponse(['error' => 'Missing sender'], 400);
        }

        $replyText = $this->settingModel->get('image_reply',
            '📷 Cảm ơn bạn đã gửi hình ảnh! Hiện tại tôi chỉ có thể xử lý tin nhắn văn bản. '
            . 'Bạn hãy mô tả câu hỏi bằng chữ để tôi hỗ trợ nhé!'
        );

        $this->zalo->sendTextMessage($senderId, $replyText);

        return $this->jsonResponse(['status' => 'ok']);
    }

    // ----------------------------------------------------------------
    // XU LY TIN NHAN MEDIA (sticker, audio, video, file)
    // ----------------------------------------------------------------
    private function handleMediaMessage(array $payload, string $type): ResponseInterface
    {
        $senderId = $payload['sender']['id'] ?? $payload['user_id_by_app'] ?? '';
        if (empty($senderId)) {
            return $this->jsonResponse(['error' => 'Missing sender'], 400);
        }

        $typeMap = [
            'user_send_sticker' => 'nhãn dán',
            'user_send_audio'   => 'tin nhắn thoại',
            'user_send_video'   => 'video',
            'user_send_file'    => 'tệp đính kèm',
        ];

        $typeName  = $typeMap[$type] ?? 'nội dung';
        $replyText = "Cảm ơn bạn đã gửi $typeName! Hiện tại tôi chỉ hỗ trợ tin nhắn văn bản. "
                   . "Bạn hãy gõ câu hỏi để tôi giúp đỡ nhé 😊";

        $this->zalo->sendTextMessage($senderId, $replyText);

        return $this->jsonResponse(['status' => 'ok']);
    }

    // ----------------------------------------------------------------
    // XU LY NGUOI DUNG QUAN TAM OA (follow)
    // ----------------------------------------------------------------
    private function handleFollow(array $payload): ResponseInterface
    {
        $senderId = $payload['follower']['id'] ?? $payload['user_id_by_app'] ?? '';
        if (empty($senderId)) {
            return $this->jsonResponse(['status' => 'ok']);
        }

        // Lay ten tu payload follow
        $followerName   = trim($payload['follower']['display_name'] ?? $payload['follower']['name'] ?? '');
        $followerAvatar = trim($payload['follower']['avatar'] ?? '');

        if ($followerName) {
            $userProfile = ['data' => ['display_name' => $followerName, 'avatar' => $followerAvatar]];
        } else {
            $userProfile = [];
            try {
                $userProfile = $this->zalo->getUserProfile($senderId);
            } catch (\Throwable $e) {}
        }

        $this->conversationModel->findOrCreate($senderId, $userProfile);

        // Gui tin nhan chao mung
        $welcomeMsg = $this->settingModel->get('welcome_message',
            "🎉 Xin chào! Cảm ơn bạn đã quan tâm đến chúng tôi!\n\n"
            . "Tôi là trợ lý AI, sẵn sàng hỗ trợ bạn 24/7 về:\n"
            . "📚 Thông tin khóa học\n"
            . "📅 Lịch học và bài tập\n"
            . "❓ Giải đáp thắc mắc\n\n"
            . "Bạn cần hỗ trợ gì, hãy nhắn tin cho tôi nhé!"
        );

        $this->zalo->sendTextMessage($senderId, $welcomeMsg);

        log_message('info', "[Webhook] New follower: $senderId");

        return $this->jsonResponse(['status' => 'ok']);
    }

    // ----------------------------------------------------------------
    // XU LY NGUOI DUNG HUY QUAN TAM (unfollow)
    // ----------------------------------------------------------------
    private function handleUnfollow(array $payload): ResponseInterface
    {
        $senderId = $payload['follower']['id'] ?? $payload['user_id_by_app'] ?? '';
        if ($senderId) {
            log_message('info', "[Webhook] User unfollowed: $senderId");
        }

        return $this->jsonResponse(['status' => 'ok']);
    }

    // ----------------------------------------------------------------
    // CHUYEN SANG NHAN VIEN HOI TRO TRUC TIEP
    // ----------------------------------------------------------------
    private function handleTransferToHuman(string $senderId, int $convId): ResponseInterface
    {
        $transferMsg = $this->settingModel->get('transfer_message',
            "🙋 Tôi đã ghi nhận yêu cầu của bạn và sẽ kết nối bạn với nhân viên hỗ trợ ngay!\n\n"
            . "⏰ Thời gian làm việc: 8:00 - 22:00\n"
            . "Nhân viên sẽ phản hồi bạn trong thời gian sớm nhất. Cảm ơn bạn đã kiên nhẫn!"
        );

        $this->zalo->sendTextMessage($senderId, $transferMsg);

        // Danh dau cuoc hoi thoai can nhan vien xu ly
        $this->conversationModel->update($convId, ['status' => 'pending_human']);

        return $this->jsonResponse(['status' => 'transferred']);
    }

    // ----------------------------------------------------------------
    // HELPER
    // ----------------------------------------------------------------
    private function containsKeyword(string $text, string $keywordsStr): bool
    {
        $keywords = array_map('trim', explode(',', $keywordsStr));
        $textLower = mb_strtolower($text);
        foreach ($keywords as $kw) {
            if (!empty($kw) && str_contains($textLower, mb_strtolower($kw))) {
                return true;
            }
        }
        return false;
    }
}
