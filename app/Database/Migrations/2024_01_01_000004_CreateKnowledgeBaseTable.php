<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKnowledgeBaseTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 300,
                'null'       => false,
            ],
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'default'    => 'general',
            ],
            'source_document' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
            'content' => [
                'type' => 'LONGTEXT',
                'null' => false,
            ],
            'keywords' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Comma-separated keywords for matching',
            ],
            'is_active' => [
                'type'    => 'TINYINT',
                'default' => 1,
            ],
            'sort_order' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('category');
        $this->forge->addKey('is_active');

        $this->forge->createTable('knowledge_base', true, [
            'ENGINE'  => 'InnoDB',
            'charset' => 'utf8mb4',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('knowledge_base', true);
    }
}
