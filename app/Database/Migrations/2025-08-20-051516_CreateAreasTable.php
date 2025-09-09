<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAreasTable extends Migration
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
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 191,
            ],
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'parent_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'unsigned'   => true,
            ],
            'parent_key' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
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
        $this->forge->addForeignKey('parent_id', 'areas', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('areas');
    }

    public function down()
    {
        $this->forge->dropTable('areas');
    }
}
