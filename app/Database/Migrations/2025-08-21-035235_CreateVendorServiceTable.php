<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVendorServicesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'vendor_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
            'service_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
            'approval_status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending','approved','rejected'],
                'default'    => 'pending',
            ],
            'commission_rate' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
            ],
            'start_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'end_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('vendor_id', 'vendor_profiles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('service_id', 'services', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('vendor_services', true);
    }

    public function down()
    {
        $this->forge->dropTable('vendor_services', true);
    }
}
