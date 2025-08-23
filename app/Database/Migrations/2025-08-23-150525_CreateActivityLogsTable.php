<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateActivityLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true], // pelaku (opsional)
            'vendor_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true], // vendor terkait (opsional)
            'module'      => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true], // contoh: auth, leads, products
            'action'      => ['type' => 'VARCHAR', 'constraint' => 100],              // contoh: login, logout, create, update
            'description' => ['type' => 'TEXT', 'null' => true],
            'ip_address'  => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],   // IPv4/IPv6
            'user_agent'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'default' => new RawSql('CURRENT_TIMESTAMP')],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('vendor_id');
        $this->forge->addKey(['module', 'action']);
        $this->forge->addKey('created_at');

        // FK ke tabel yang sudah ada
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('vendor_id', 'vendor_profiles', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('activity_logs', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('activity_logs', true);
    }
}
