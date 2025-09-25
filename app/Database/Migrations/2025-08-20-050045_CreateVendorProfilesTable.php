<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateVendorProfilesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                 => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'            => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'business_name'      => ['type' => 'VARCHAR', 'constraint' => 150],
            'owner_name'         => ['type' => 'VARCHAR', 'constraint' => 100],
            'phone'              => ['type' => 'VARCHAR', 'constraint' => 30],
            'whatsapp_number'    => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],

            // status vendor (lebih fleksibel)
            'status'             => [
                'type'       => 'ENUM',
                'constraint' => ['verified', 'rejected', 'inactive', 'pending'],
                'default'    => 'pending',
            ],

            'is_verified'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],

            // tambahan field untuk manajemen approval
            'requested_commission' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'rejection_reason'     => ['type' => 'TEXT', 'null' => true],
            'inactive_reason'      => ['type' => 'TEXT', 'null' => true],
            'approved_at'          => ['type' => 'DATETIME', 'null' => true],
            'action_by'            => ['type' => 'INT', 'unsigned' => true, 'null' => true],

            // timestamps
            'created_at'         => ['type' => 'DATETIME', 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'updated_at'         => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');

        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable(
            'vendor_profiles',
            true,
            ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_unicode_ci']
        );
    }

    public function down()
    {
        $this->forge->dropTable('vendor_profiles', true);
    }
}
