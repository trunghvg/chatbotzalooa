<?php

namespace App\Controllers;

use App\Libraries\ClaudeAI;
use App\Libraries\ZaloOA;
use App\Models\ConversationModel;
use App\Models\MessageModel;
use App\Models\SettingModel;

class Admin extends BaseController
{
    private ConversationModel $conversationModel;
    private MessageModel      $messageModel;
    private SettingModel      $settingModel;

    public function __construct()
    {
        $this->conversationModel = new ConversationModel();
        $this->messageModel      = new MessageModel();
        $this->settingModel      = new SettingModel();
    }

    // ----------------------------------------------------------------
    // DASHBOARD
    // ----------------------------------------------------------------
    public function index(): string
    {
        $this->requireAuth();

        $convStats    = $this->conversationModel->getStats();
        $msgThisWeek  = $this->messageModel->getMessageStatsByWeek();
        $recentConvs  = $this->conversationModel->getConversationsWithPaging(1, 5);

        return view('admin/dashboard', [
            'title'       => 'Dashboard',
            'convStats'   => $convStats,
            'weeklyStats' => $msgThisWeek,
            'recentConvs' => $recentConvs,
            'botEnabled'  => $this->settingModel->get('bot_enabled', '1'),
        ]);
    }

    // ----------------------------------------------------------------
    // QUAN LY CUOC HOI THOAI
    // ----------------------------------------------------------------
    public function conversations(): string
    {
        $this->requireAuth();

        $page  = (int) ($this->request->getGet('page') ?? 1);
        $page  = max(1, $page);
        $total = $this->conversationModel->countAllResults(false);
        $convs = $this->conversationModel->getConversationsWithPaging($page, 20);

        return view('admin/conversations', [
            'title'         => 'Cuộc hội thoại',
            'conversations' => $convs,
            'currentPage'   => $page,
            'totalPages'    => ceil($total / 20),
            'total'         => $total,
        ]);
    }

    public function conversationDetail(int $id): string
    {
        $this->requireAuth();

        $conversation = $this->conversationModel->getWithMessages($id);
        if (!$conversation) {
            return redirect()->to('/admin/conversations')
                             ->with('error', 'Không tìm thấy cuộc hội thoại!');
        }

        return view('admin/conversation_detail', [
            'title'        => 'Chi tiết hội thoại #' . $id,
            'conversation' => $conversation,
        ]);
    }

    public function deleteConversation(int $id): ResponseInterface
    {
        $this->requireAuth();

        $db = \Config\Database::connect();
        $db->table('messages')->where('conversation_id', $id)->delete();
        $this->conversationModel->delete($id);

        return $this->jsonResponse(['status' => 'ok']);
    }

    // ----------------------------------------------------------------
    // CAI DAT HE THONG
    // ----------------------------------------------------------------
    public function settings(): string
    {
        $this->requireAuth();

        $settings = $this->settingModel->getAllSettings();

        return view('admin/settings', [
            'title'    => 'Cài đặt hệ thống',
            'settings' => $settings,
        ]);
    }

    public function saveSettings(): ResponseInterface
    {
        $this->requireAuth();

        $keys = [
            'bot_enabled', 'welcome_message', 'image_reply',
            'transfer_keywords', 'transfer_message',
            'contact_hotline', 'contact_email', 'contact_hours',
        ];

        foreach ($keys as $key) {
            $value = $this->request->getPost($key);
            if ($value !== null) {
                $this->settingModel->set($key, $value);
            }
        }

        return redirect()->to('/admin/settings')
                         ->with('success', 'Đã lưu cài đặt thành công!');
    }

    // ----------------------------------------------------------------
    // SYSTEM PROMPT CHO CLAUDE
    // ----------------------------------------------------------------
    public function prompt(): string
    {
        $this->requireAuth();

        return view('admin/prompt', [
            'title'  => 'System Prompt Claude AI',
            'prompt' => $this->settingModel->get('claude_system_prompt', ''),
        ]);
    }

    public function savePrompt(): ResponseInterface
    {
        $this->requireAuth();

        $prompt = $this->request->getPost('system_prompt');
        $this->settingModel->set('claude_system_prompt', $prompt,
            'System prompt cho Claude AI');

        return redirect()->to('/admin/prompt')
                         ->with('success', 'Đã cập nhật System Prompt!');
    }

    // ----------------------------------------------------------------
    // API AJAX
    // ----------------------------------------------------------------
    public function apiStats(): ResponseInterface
    {
        $this->requireAuth();

        return $this->jsonResponse([
            'conversations' => $this->conversationModel->getStats(),
            'messages'      => [
                'today' => $this->messageModel->countMessagesByDate(date('Y-m-d')),
            ],
        ]);
    }

    public function testClaude(): ResponseInterface
    {
        $this->requireAuth();

        $message = $this->request->getPost('message') ?? 'Xin chào! Bạn là ai?';
        $claude  = new ClaudeAI();

        try {
            $result = $claude->testConnection($message);
            return $this->jsonResponse($result);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function testZalo(): ResponseInterface
    {
        $this->requireAuth();

        $userId  = $this->request->getPost('user_id');
        $message = $this->request->getPost('message') ?? 'Test từ Admin Panel 🤖';

        if (!$userId) {
            return $this->jsonResponse(['success' => false, 'message' => 'Thiếu user_id'], 400);
        }

        try {
            $zalo   = new ZaloOA();
            $result = $zalo->sendTextMessage($userId, $message);
            return $this->jsonResponse([
                'success' => ($result['error'] ?? -1) === 0,
                'result'  => $result,
            ]);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function refreshZaloToken(): ResponseInterface
    {
        $this->requireAuth();

        try {
            $zalo   = new ZaloOA();
            $result = $zalo->refreshAccessToken();
            return $this->jsonResponse([
                'success' => isset($result['access_token']),
                'result'  => $result,
            ]);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ----------------------------------------------------------------
    // MESSAGES LIST
    // ----------------------------------------------------------------
    public function messages(): string
    {
        $this->requireAuth();

        $page     = (int) ($this->request->getGet('page') ?? 1);
        $page     = max(1, $page);
        $perPage  = 50;

        $total    = $this->messageModel->countAllResults(false);
        $messages = $this->messageModel
            ->orderBy('created_at', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->findAll();

        return view('admin/messages', [
            'title'       => 'Nhật ký tin nhắn',
            'messages'    => $messages,
            'currentPage' => $page,
            'totalPages'  => ceil($total / $perPage),
            'total'       => $total,
        ]);
    }
}
