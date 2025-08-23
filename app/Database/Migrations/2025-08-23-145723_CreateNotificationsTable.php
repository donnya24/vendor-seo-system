<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateNotificationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],  // penerima (opsional)
            'vendor_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],  // vendor terkait (opsional)
            'type'       => [
                'type'       => 'ENUM',
                'constraint' => ['lead', 'commission', 'announcement', 'system'],
                'default'    => 'system',
            ],
            'title'      => ['type' => 'VARCHAR', 'constraint' => 150],
            'message'    => ['type' => 'TEXT', 'null' => true],
            'is_read'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'read_at'    => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'default' => new RawSql('CURRENT_TIMESTAMP')],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('vendor_id');
        $this->forge->addKey(['type', 'is_read']);

        // FK ke tabel yang sudah ada di dump kamu
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('vendor_id', 'vendor_profiles', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('notifications', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('notifications', true);
    }
}
