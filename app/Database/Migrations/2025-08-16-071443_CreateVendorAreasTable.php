<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVendorAreasTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'        => ['type'=>'INT','unsigned'=>true,'auto_increment'=>true],
            'vendor_id' => ['type'=>'INT','unsigned'=>true],
            'area_id'   => ['type'=>'INT','unsigned'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('vendor_id','vendor_profiles','id','CASCADE','CASCADE');
        $this->forge->addForeignKey('area_id','areas','id','CASCADE','CASCADE');
        $this->forge->createTable('vendor_areas');
    }

    public function down()
    {
        $this->forge->dropTable('vendor_areas');
    }
}
