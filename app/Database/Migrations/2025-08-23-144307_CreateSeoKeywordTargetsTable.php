<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateSeoKeywordTargetsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'vendor_id'        => ['type' => 'INT', 'unsigned' => true],
            // nama proyek bebas (belum ada tabel projects di SQL yang kamu kirim)
            'project_name'     => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],

            // keyword yang dibidik
            'keyword'          => ['type' => 'VARCHAR', 'constraint' => 180],

            // posisi sekarang & target (SERP)
            'current_position' => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true],
            'target_position'  => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true],

            // deadline/target waktu
            'deadline'         => ['type' => 'DATE', 'null' => true],

            // status & prioritas — selaras dengan UI Optimasi (pending/in_progress/completed)
            'status'           => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'in_progress', 'completed'],
                'default'    => 'pending',
            ],
            'priority'         => [
                'type'       => 'ENUM',
                'constraint' => ['low', 'medium', 'high'],
                'default'    => 'medium',
            ],

            'notes'            => ['type' => 'TEXT', 'null' => true],

            'created_at'       => ['type' => 'DATETIME', 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('vendor_id');
        $this->forge->addKey('keyword');
        $this->forge->addForeignKey('vendor_id', 'vendor_profiles', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable(
            'seo_keyword_targets',
            true,
            ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_unicode_ci']
        );
    }

    public function down()
    {
        $this->forge->dropTable('seo_keyword_targets', true);
    }
}
