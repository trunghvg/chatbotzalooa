<?php

namespace App\Controllers;

use App\Libraries\ClaudeAI;
use App\Libraries\ZaloOA;
use App\Models\ConversationModel;
use App\Models\KnowledgeModel;
use App\Models\MessageModel;
use App\Models\SettingModel;
use CodeIgniter\HTTP\ResponseInterface;

class Admin extends BaseController
{
    private ConversationModel $conversationModel;
    private MessageModel      $messageModel;
    private SettingModel      $settingModel;
    private KnowledgeModel    $knowledgeModel;

    public function __construct()
    {
        $this->conversationModel = new ConversationModel();
        $this->messageModel      = new MessageModel();
        $this->settingModel      = new SettingModel();
        $this->knowledgeModel    = new KnowledgeModel();
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
                $this->settingModel->saveSetting($key, $value);
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
        $this->settingModel->saveSetting('claude_system_prompt', $prompt,
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

    /**
     * Tra ve tin nhan moi hon lastId cho mot cuoc hoi thoai (dung de polling)
     */
    public function apiConversationMessages(int $id): ResponseInterface
    {
        $this->requireAuth();

        $lastId   = (int) ($this->request->getGet('after') ?? 0);
        $messages = $this->messageModel
            ->where('conversation_id', $id)
            ->where('id >', $lastId)
            ->orderBy('id', 'ASC')
            ->findAll();

        $conv = $this->conversationModel->find($id);

        return $this->jsonResponse([
            'messages'      => $messages,
            'message_count' => $conv['message_count'] ?? 0,
        ]);
    }

    /**
     * Tra ve danh sach cuoc hoi thoai moi nhat (dung de polling)
     */
    public function apiConversationsList(): ResponseInterface
    {
        $this->requireAuth();

        $convs = $this->conversationModel->getConversationsWithPaging(1, 20);
        $total = $this->conversationModel->countAllResults(false);

        return $this->jsonResponse([
            'total'         => $total,
            'conversations' => $convs,
        ]);
    }

    // ----------------------------------------------------------------
    // KNOWLEDGE BASE
    // ----------------------------------------------------------------
    public function knowledge(): string
    {
        $this->requireAuth();

        $page    = (int) ($this->request->getGet('page') ?? 1);
        $page    = max(1, $page);
        $perPage = 20;

        $total   = $this->knowledgeModel->countAllResults(false);
        $entries = $this->knowledgeModel
                        ->orderBy('sort_order', 'ASC')
                        ->orderBy('category', 'ASC')
                        ->limit($perPage, ($page - 1) * $perPage)
                        ->findAll();

        return view('admin/knowledge', [
            'title'       => 'Cơ sở kiến thức (Knowledge Base)',
            'entries'     => $entries,
            'categories'  => $this->knowledgeModel->getCategories(),
            'currentPage' => $page,
            'totalPages'  => ceil($total / $perPage),
            'total'       => $total,
        ]);
    }

    public function knowledgeCreate(): string
    {
        $this->requireAuth();
        return view('admin/knowledge_form', [
            'title'  => 'Thêm kiến thức mới',
            'entry'  => null,
        ]);
    }

    public function knowledgeStore(): ResponseInterface
    {
        $this->requireAuth();

        $now = date('Y-m-d H:i:s');
        $this->knowledgeModel->insert([
            'title'           => $this->request->getPost('title'),
            'category'        => $this->request->getPost('category') ?: 'general',
            'source_document' => $this->request->getPost('source_document'),
            'content'         => $this->request->getPost('content'),
            'keywords'        => $this->request->getPost('keywords'),
            'sort_order'      => (int) ($this->request->getPost('sort_order') ?? 0),
            'is_active'       => $this->request->getPost('is_active') ? 1 : 0,
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);

        return redirect()->to('/admin/knowledge')
                         ->with('success', 'Đã thêm mục kiến thức mới!');
    }

    public function knowledgeEdit(int $id): string
    {
        $this->requireAuth();

        $entry = $this->knowledgeModel->find($id);
        if (!$entry) {
            return redirect()->to('/admin/knowledge')
                             ->with('error', 'Không tìm thấy mục kiến thức!');
        }

        return view('admin/knowledge_form', [
            'title' => 'Chỉnh sửa kiến thức',
            'entry' => $entry,
        ]);
    }

    public function knowledgeUpdate(int $id): ResponseInterface
    {
        $this->requireAuth();

        $this->knowledgeModel->update($id, [
            'title'           => $this->request->getPost('title'),
            'category'        => $this->request->getPost('category') ?: 'general',
            'source_document' => $this->request->getPost('source_document'),
            'content'         => $this->request->getPost('content'),
            'keywords'        => $this->request->getPost('keywords'),
            'sort_order'      => (int) ($this->request->getPost('sort_order') ?? 0),
            'is_active'       => $this->request->getPost('is_active') ? 1 : 0,
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin/knowledge')
                         ->with('success', 'Đã cập nhật mục kiến thức!');
    }

    public function knowledgeDelete(int $id): ResponseInterface
    {
        $this->requireAuth();

        $this->knowledgeModel->delete($id);
        return $this->jsonResponse(['status' => 'ok']);
    }

    public function knowledgeToggle(int $id): ResponseInterface
    {
        $this->requireAuth();

        $entry = $this->knowledgeModel->find($id);
        if (!$entry) {
            return $this->jsonResponse(['error' => 'Not found'], 404);
        }

        $newStatus = $entry['is_active'] ? 0 : 1;
        $this->knowledgeModel->update($id, ['is_active' => $newStatus]);

        return $this->jsonResponse(['status' => 'ok', 'is_active' => $newStatus]);
    }

    /**
     * Import du lieu tu file Excel upload len
     */
    public function knowledgeImport(): ResponseInterface
    {
        $this->requireAuth();

        $file = $this->request->getFile('excel_file');

        if (!$file || !$file->isValid()) {
            return redirect()->to('/admin/knowledge')
                             ->with('error', 'Vui lòng chọn file Excel hợp lệ!');
        }

        if (!in_array($file->getClientMimeType(), [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'application/octet-stream',
        ])) {
            return redirect()->to('/admin/knowledge')
                             ->with('error', 'Chỉ hỗ trợ file .xlsx hoặc .xls!');
        }

        $savedPath = $file->store('uploads/excel', $file->getRandomName());
        $fullPath  = WRITEPATH . $savedPath;

        try {
            $importer = new \App\Libraries\ExcelKnowledgeImporter();
            $count    = $importer->import($fullPath);

            return redirect()->to('/admin/knowledge')
                             ->with('success', "Đã import $count mục kiến thức từ file Excel!");
        } catch (\Throwable $e) {
            log_message('error', '[Admin] Excel import error: ' . $e->getMessage());
            return redirect()->to('/admin/knowledge')
                             ->with('error', 'Lỗi import: ' . $e->getMessage());
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
