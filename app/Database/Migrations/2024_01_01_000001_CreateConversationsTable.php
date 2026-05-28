<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateConversationsTable extends Migration
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
            'zalo_user_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'user_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'default'    => 'Học viên',
            ],
            'user_avatar' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'last_message' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'default'    => '',
            ],
            'last_message_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'message_count' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'pending_human', 'closed'],
                'default'    => 'active',
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
        $this->forge->addUniqueKey('zalo_user_id');
        $this->forge->addKey('last_message_at');
        $this->forge->addKey('status');

        $this->forge->createTable('conversations', true, [
            'ENGINE'  => 'InnoDB',
            'charset' => 'utf8mb4',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('conversations', true);
    }
}
