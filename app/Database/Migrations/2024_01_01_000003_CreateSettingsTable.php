<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSettingsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'key' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'value' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('key');

        $this->forge->createTable('settings', true, [
            'ENGINE'  => 'InnoDB',
            'charset' => 'utf8mb4',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('settings', true);
    }
}
