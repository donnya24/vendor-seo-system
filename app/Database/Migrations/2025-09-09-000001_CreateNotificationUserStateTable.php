<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationUserStateTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'notification_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'is_read' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => '0 = belum dibaca, 1 = sudah dibaca',
            ],
            'read_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'hidden' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => '0 = tampil, 1 = disembunyikan',
            ],
            'hidden_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['notification_id', 'user_id']);
        $this->forge->addForeignKey('notification_id', 'notifications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('notification_user_state');
    }

    public function down()
    {
        $this->forge->dropTable('notification_user_state');
    }
}
