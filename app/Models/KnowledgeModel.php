<?php

namespace App\Models;

use CodeIgniter\Model;

class KnowledgeModel extends Model
{
    protected $table         = 'knowledge_base';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'title', 'category', 'source_document', 'content',
        'keywords', 'is_active', 'sort_order', 'created_at', 'updated_at',
    ];
    protected $useTimestamps = false;

    /**
     * Lay tat ca knowledge dang hoat dong, ghep lai thanh context cho Claude
     */
    public function buildContextForClaude(): string
    {
        $entries = $this->where('is_active', 1)
                        ->orderBy('sort_order', 'ASC')
                        ->orderBy('category', 'ASC')
                        ->findAll();

        if (empty($entries)) {
            return '';
        }

        $context = "\n\n## CƠ SỞ KIẾN THỨC - VĂN BẢN PHÁP LÝ VÀ QUY ĐỊNH\n\n";
        $context .= "Sử dụng các thông tin sau để trả lời câu hỏi của học viên một cách chính xác:\n\n";

        $currentCategory = '';
        foreach ($entries as $entry) {
            if ($entry['category'] !== $currentCategory) {
                $currentCategory = $entry['category'];
                $context .= "### " . strtoupper($currentCategory) . "\n\n";
            }

            $context .= "**" . $entry['title'] . "**";
            if (!empty($entry['source_document'])) {
                $context .= " _(Nguồn: " . $entry['source_document'] . ")_";
            }
            $context .= "\n" . $entry['content'] . "\n\n";
        }

        return $context;
    }

    /**
     * Tim kiem knowledge theo keyword trong cau hoi cua hoc vien
     * Dung khi chi muon inject cac doan lien quan (tiet kiem token)
     */
    public function findRelevant(string $userMessage, int $limit = 5): array
    {
        $words = array_filter(
            explode(' ', preg_replace('/[^\p{L}\p{N}\s]/u', ' ', mb_strtolower($userMessage))),
            fn($w) => mb_strlen($w) >= 3
        );

        if (empty($words)) {
            return $this->where('is_active', 1)->orderBy('sort_order')->limit($limit)->findAll();
        }

        // Tim theo keywords field truoc
        $builder = $this->where('is_active', 1);
        $orGroup = [];
        foreach (array_slice($words, 0, 8) as $word) {
            $orGroup[] = "keywords LIKE '%" . $this->db->escapeLikeString($word) . "%'";
            $orGroup[] = "title LIKE '%" . $this->db->escapeLikeString($word) . "%'";
            $orGroup[] = "content LIKE '%" . $this->db->escapeLikeString($word) . "%'";
        }

        if ($orGroup) {
            $builder->where('(' . implode(' OR ', $orGroup) . ')');
        }

        $results = $builder->orderBy('sort_order')->limit($limit)->findAll();

        // Fallback: lay tat ca neu khong tim duoc
        if (empty($results)) {
            return $this->where('is_active', 1)->orderBy('sort_order')->limit(3)->findAll();
        }

        return $results;
    }

    /**
     * Lay theo category
     */
    public function getByCategory(string $category): array
    {
        return $this->where('is_active', 1)
                    ->where('category', $category)
                    ->orderBy('sort_order')
                    ->findAll();
    }

    /**
     * Danh sach cac category
     */
    public function getCategories(): array
    {
        return $this->db->query(
            "SELECT DISTINCT category, COUNT(*) as count
             FROM knowledge_base WHERE is_active = 1
             GROUP BY category ORDER BY category"
        )->getResultArray();
    }
}
