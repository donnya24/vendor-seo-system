<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateLeadsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type'=>'INT','unsigned'=>true,'auto_increment'=>true],
            'vendor_id'     => ['type'=>'INT','unsigned'=>true],
            'service_id'    => ['type'=>'INT','unsigned'=>true,'null'=>true],
            'customer_name' => ['type'=>'VARCHAR','constraint'=>100],
            'contact'       => ['type'=>'VARCHAR','constraint'=>100],
            'status'        => ['type'=>'ENUM','constraint'=>['new','in_progress','closed','rejected'],'default'=>'new'],
            'assigned_by'   => ['type'=>'INT','unsigned'=>true,'null'=>true],
            'created_at'    => ['type'=>'DATETIME','default'=>new RawSql('CURRENT_TIMESTAMP')],
            'updated_at'    => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['vendor_id','service_id','assigned_by']);
        $this->forge->addForeignKey('vendor_id','vendor_profiles','id','CASCADE','CASCADE');
        $this->forge->addForeignKey('service_id','services','id','SET NULL','CASCADE');
        $this->forge->addForeignKey('assigned_by','users','id','SET NULL','CASCADE');
        $this->forge->createTable('leads', true, ['ENGINE'=>'InnoDB','CHARSET'=>'utf8mb4','COLLATE'=>'utf8mb4_unicode_ci']);
    }
    public function down()
    {
        $this->forge->dropTable('leads', true);
    }
}
