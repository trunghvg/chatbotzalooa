<?php

namespace App\Models;

use CodeIgniter\Model;

class MessageModel extends Model
{
    protected $table         = 'messages';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'conversation_id', 'role', 'content', 'zalo_msg_id',
        'tokens_used', 'processing_time_ms', 'created_at',
    ];
    protected $useTimestamps  = false;
    protected $dateFormat     = 'datetime';

    /**
     * Luu tin nhan vao DB
     */
    public function saveMessage(array $data): int|false
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert($data, true);
    }

    /**
     * Lay lich su hoi thoai de gui len Claude (chi lay role + content)
     */
    public function getConversationHistory(int $conversationId, int $limit = 10): array
    {
        return $this->select('role, content')
                    ->where('conversation_id', $conversationId)
                    ->orderBy('created_at', 'ASC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Lay tat ca tin nhan cua mot cuoc hoi thoai (de hien thi admin)
     */
    public function getMessagesByConversation(int $conversationId): array
    {
        return $this->where('conversation_id', $conversationId)
                    ->orderBy('created_at', 'ASC')
                    ->findAll();
    }

    /**
     * Dem so tin nhan trong khoang thoi gian
     */
    public function countMessagesByDate(string $date): int
    {
        return $this->where('DATE(created_at)', $date)->countAllResults();
    }

    /**
     * Thong ke tin nhan theo ngay (7 ngay gan nhat)
     */
    public function getMessageStatsByWeek(): array
    {
        $db = \Config\Database::connect();
        return $db->query("
            SELECT DATE(created_at) as date, COUNT(*) as total,
                   SUM(role = 'user') as user_msgs,
                   SUM(role = 'assistant') as bot_msgs
            FROM messages
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ")->getResultArray();
    }
}
