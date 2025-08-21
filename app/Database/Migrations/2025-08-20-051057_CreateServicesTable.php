<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateServicesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'         => ['type' => 'VARCHAR', 'constraint' => 100],
            'service_type' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'description'  => ['type' => 'TEXT', 'null' => true],
            'status'       => ['type' => 'ENUM', 'constraint' => ['active','inactive'], 'default' => 'active'],
            'created_at'   => ['type' => 'DATETIME', 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('services', true, ['ENGINE' => 'InnoDB', 'CHARSET'=>'utf8mb4', 'COLLATE'=>'utf8mb4_unicode_ci']);
    }

    public function down()
    {
        $this->forge->dropTable('services', true);
    }
}
