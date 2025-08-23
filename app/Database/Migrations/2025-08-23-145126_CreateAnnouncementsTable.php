<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateAnnouncementsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'title'       => ['type' => 'VARCHAR', 'constraint' => 180],
            'body'        => ['type' => 'TEXT', 'null' => true],
            // target audiens pengumuman
            'audience'    => [
                'type'       => 'ENUM',
                'constraint' => ['all', 'vendor', 'seo_team', 'admin'],
                'default'    => 'all',
            ],
            // waktu tayang & kadaluarsa (opsional)
            'publish_at'  => ['type' => 'DATETIME', 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'expires_at'  => ['type' => 'DATETIME', 'null' => true],

            'is_active'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],

            'created_at'  => ['type' => 'DATETIME', 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['audience', 'is_active']);
        $this->forge->createTable(
            'announcements',
            true,
            ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_unicode_ci']
        );
    }

    public function down()
    {
        $this->forge->dropTable('announcements', true);
    }
}
