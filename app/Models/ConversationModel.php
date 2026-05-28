<?php

namespace App\Models;

use CodeIgniter\Model;

class ConversationModel extends Model
{
    protected $table         = 'conversations';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'zalo_user_id', 'user_name', 'user_avatar',
        'last_message', 'last_message_at', 'message_count',
        'status', 'created_at', 'updated_at',
    ];
    protected $useTimestamps = false;

    /**
     * Lay hoac tao cuoc hoi thoai cho nguoi dung Zalo
     */
    public function findOrCreate(string $zaloUserId, array $userProfile = []): array
    {
        $conversation = $this->where('zalo_user_id', $zaloUserId)->first();

        if (!$conversation) {
            $id = $this->insert([
                'zalo_user_id'    => $zaloUserId,
                'user_name'       => $userProfile['display_name'] ?? 'Học viên',
                'user_avatar'     => $userProfile['avatar'] ?? '',
                'last_message'    => '',
                'last_message_at' => date('Y-m-d H:i:s'),
                'message_count'   => 0,
                'status'          => 'active',
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ], true);

            $conversation = $this->find($id);
        }

        return $conversation;
    }

    /**
     * Cap nhat thong tin sau khi co tin nhan moi
     */
    public function updateLastMessage(int $id, string $message): void
    {
        $this->update($id, [
            'last_message'    => mb_substr($message, 0, 200),
            'last_message_at' => date('Y-m-d H:i:s'),
            'message_count'   => $this->db->query(
                "SELECT message_count FROM conversations WHERE id = $id"
            )->getRow()->message_count + 1,
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Lay danh sach cuoc hoi thoai voi phan trang
     */
    public function getConversationsWithPaging(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        return $this->orderBy('last_message_at', 'DESC')
                    ->limit($perPage, $offset)
                    ->findAll();
    }

    /**
     * Lay cuoc hoi thoai cung thong tin chi tiet
     */
    public function getWithMessages(int $id): ?array
    {
        $conversation = $this->find($id);
        if (!$conversation) {
            return null;
        }

        $messageModel = new MessageModel();
        $conversation['messages'] = $messageModel->getMessagesByConversation($id);

        return $conversation;
    }

    /**
     * Thong ke
     */
    public function getStats(): array
    {
        $db = \Config\Database::connect();
        return [
            'total'     => $this->countAllResults(),
            'today'     => $this->where('DATE(created_at)', date('Y-m-d'))->countAllResults(),
            'active'    => $this->where('status', 'active')
                               ->where('last_message_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')))
                               ->countAllResults(),
        ];
    }
}
