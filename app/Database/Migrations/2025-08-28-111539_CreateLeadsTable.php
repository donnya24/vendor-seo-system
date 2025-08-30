<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLeadsTable extends Migration
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
            'vendor_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'tanggal' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'jumlah_leads_masuk' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'jumlah_leads_diproses' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'jumlah_leads_ditolak' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'jumlah_leads_closing' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'service_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'reported_by_vendor' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'default'    => 0,
            ],

            'assigned_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('vendor_id', 'vendor_profiles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('service_id', 'services', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('leads', true);
    }

    public function down()
    {
        $this->forge->dropTable('leads', true);
    }
}
