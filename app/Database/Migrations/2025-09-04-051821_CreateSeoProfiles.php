<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSeoProfiles extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'null'           => false,
            ],
            'name' => [
                'type'           => 'VARCHAR',
                'constraint'     => 100,
                'null'           => true,
            ],
            'phone' => [
                'type'           => 'VARCHAR',
                'constraint'     => 50,
                'null'           => true,
            ],
            'profile_image' => [
                'type'           => 'VARCHAR',
                'constraint'     => 255,
                'null'           => true,
            ],
            'status' => [
                'type'           => 'ENUM',
                'constraint'     => ['active', 'inactive'],
                'null'           => true,
                'default'        => 'active',
            ],
            'created_at' => [
                'type'           => 'DATETIME',
                'null'           => true,
                'default'        => 'CURRENT_TIMESTAMP',
            ],
            'updated_at' => [
                'type'           => 'DATETIME',
                'null'           => true,
                'default'        => 'CURRENT_TIMESTAMP',
                'on update'      => 'CURRENT_TIMESTAMP',
            ],
        ]);

        // Primary dan index
        $this->forge->addKey('id', true);       // Primary key
        $this->forge->addKey('user_id');        // Index

        $this->forge->createTable('seo_profiles');
    }

    public function down()
    {
        $this->forge->dropTable('seo_profiles');
    }
}
