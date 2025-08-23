<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateLeadDistributionTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'lead_id'      => ['type' => 'INT', 'unsigned' => true],
            'vendor_id'    => ['type' => 'INT', 'unsigned' => true],
            'assigned_by'  => ['type' => 'INT', 'unsigned' => true, 'null' => true], // user (admin/seo) yang assign
            'status'       => ['type' => 'ENUM', 'constraint' => ['new','contacted','processing','converted','rejected'], 'default' => 'new'],
            'note'         => ['type' => 'TEXT', 'null' => true],
            'assigned_at'  => ['type' => 'DATETIME', 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('lead_id');
        $this->forge->addKey('vendor_id');
        $this->forge->addKey('status');
        $this->forge->addKey('assigned_at');

        // Kalau suatu lead hanya boleh punya 1 vendor, aktifkan unique berikut:
        // $this->forge->addUniqueKey('lead_id');

        // Kalau memperbolehkan lead di-assign ke beberapa vendor tapi tidak ganda ke vendor yang sama:
        $this->forge->addUniqueKey(['lead_id', 'vendor_id']);

        // Foreign keys (samakan dengan tabel yang sudah ada di DB kamu)
        $this->forge->addForeignKey('lead_id',   'leads',           'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('vendor_id', 'vendor_profiles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('assigned_by', 'users',         'id', 'SET NULL', 'SET NULL');

        $this->forge->createTable('lead_distribution', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('lead_distribution', true);
    }
}
