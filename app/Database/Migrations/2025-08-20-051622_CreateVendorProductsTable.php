<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateVendorProductsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type'=>'INT','unsigned'=>true,'auto_increment'=>true],
            'vendor_id'    => ['type'=>'INT','unsigned'=>true],
            'product_name' => ['type'=>'VARCHAR','constraint'=>150],
            'description'  => ['type'=>'TEXT','null'=>true],
            'price'        => ['type'=>'DECIMAL','constraint'=>'12,2','null'=>true],
            'created_at'   => ['type'=>'DATETIME','default'=>new RawSql('CURRENT_TIMESTAMP')],
            'updated_at'   => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('vendor_id');
        $this->forge->addForeignKey('vendor_id','vendor_profiles','id','CASCADE','CASCADE');
        $this->forge->createTable('vendor_products', true, ['ENGINE'=>'InnoDB','CHARSET'=>'utf8mb4','COLLATE'=>'utf8mb4_unicode_ci']);
    }
    public function down()
    {
        $this->forge->dropTable('vendor_products', true);
    }
}
