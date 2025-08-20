<?php
// app/Database/Migrations/2025-08-20-000005_CreateVendorAreasTable.php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateVendorAreasTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'vendor_id'  => ['type' => 'INT', 'unsigned' => true],
            'area_id'    => ['type' => 'INT', 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'default' => new RawSql('CURRENT_TIMESTAMP')],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['vendor_id','area_id']);
        $this->forge->addForeignKey('vendor_id', 'vendor_profiles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('area_id', 'areas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('vendor_areas', true, ['ENGINE'=>'InnoDB','CHARSET'=>'utf8mb4','COLLATE'=>'utf8mb4_unicode_ci']);
    }

    public function down()
    {
        $this->forge->dropTable('vendor_areas', true);
    }
}
