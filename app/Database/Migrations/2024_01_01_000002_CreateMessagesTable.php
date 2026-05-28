<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMessagesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'conversation_id' => [
                'type'     => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null'     => false,
            ],
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['user', 'assistant'],
                'null'       => false,
            ],
            'content' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'zalo_msg_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'tokens_used' => [
                'type'    => 'INT',
                'default' => 0,
                'null'    => true,
            ],
            'processing_time_ms' => [
                'type'    => 'INT',
                'default' => 0,
                'null'    => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('conversation_id');
        $this->forge->addKey('role');
        $this->forge->addKey('created_at');
        $this->forge->addForeignKey('conversation_id', 'conversations', 'id',
            'CASCADE', 'CASCADE');

        $this->forge->createTable('messages', true, [
            'ENGINE'  => 'InnoDB',
            'charset' => 'utf8mb4',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('messages', true);
    }
}
