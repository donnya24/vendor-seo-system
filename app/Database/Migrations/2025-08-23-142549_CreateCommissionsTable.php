<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateCommissionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'vendor_id'      => ['type' => 'INT', 'unsigned' => true],

            // Periode komisi (setengah bulan / bulanan — bebas kamu pakai)
            'period_start'   => ['type' => 'DATE', 'null' => false],
            'period_end'     => ['type' => 'DATE', 'null' => false],

            // Ringkasan angka
            'leads_count'    => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'amount'         => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00'],

            // Status pembayaran
            'status'         => ['type' => 'ENUM', 'constraint' => ['unpaid', 'paid'], 'default' => 'unpaid'],

            // Waktu2
            'paid_at'        => ['type' => 'DATETIME', 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        // Cegah duplikat komisi untuk vendor & periode yang sama
        $this->forge->addUniqueKey(['vendor_id', 'period_start', 'period_end']);
        $this->forge->addKey('vendor_id');

        // Ikuti pola FK lain: referensi ke vendor_profiles.id dengan CASCADE
        $this->forge->addForeignKey('vendor_id', 'vendor_profiles', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('commissions', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('commissions', true);
    }
}
