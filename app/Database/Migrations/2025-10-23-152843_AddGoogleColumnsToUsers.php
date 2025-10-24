<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGoogleColumnsToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'google_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'username'
            ],
            'google_profile' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'google_id'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'google_id');
        $this->forge->dropColumn('users', 'google_profile');
    }
}