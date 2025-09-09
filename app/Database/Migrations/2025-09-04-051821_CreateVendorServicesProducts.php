<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVendorServicesProducts extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'vendor_id'         => [
                'type'           => 'INT',
                'unsigned'       => true,
            ],
            'service_name'      => [
                'type'           => 'VARCHAR',
                'constraint'     => 255,
            ],
            'service_description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'product_name'      => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'product_description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'price' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
            ],
            'attachment' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('vendor_services_products');
    }

    public function down()
    {
        $this->forge->dropTable('vendor_services_products');
    }
}
