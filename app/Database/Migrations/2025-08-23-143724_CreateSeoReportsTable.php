<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateSeoReportsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'vendor_id'  => ['type' => 'INT', 'unsigned' => true],
            'keyword'    => ['type' => 'VARCHAR', 'constraint' => 150],
            'project'    => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'position'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'change'     => ['type' => 'INT', 'null' => true, 'comment' => 'perubahan posisi (+/-)'],
            'trend'      => ['type' => 'ENUM', 'constraint' => ['up','down','stable'], 'default' => 'stable'],
            'volume'     => ['type' => 'INT', 'null' => true],
            'status'     => ['type' => 'ENUM', 'constraint' => ['active','inactive'], 'default' => 'active'],
            'created_at' => ['type' => 'DATETIME', 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['vendor_id', 'keyword']);
        $this->forge->addForeignKey('vendor_id', 'vendor_profiles', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('seo_reports', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('seo_reports', true);
    }
}
