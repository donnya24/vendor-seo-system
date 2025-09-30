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

            // status vendor
            'status'             => [
                'type'       => 'ENUM',
                'constraint' => ['verified', 'rejected', 'inactive', 'pending'],
                'default'    => 'pending',
            ],

            // foto profil vendor
            'profile_image'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],

            // timestamps
            'created_at'         => ['type' => 'DATETIME', 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'updated_at'         => ['type' => 'DATETIME', 'null' => true],

            // manajemen komisi
            'requested_commission'        => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'requested_commission_nominal'=> ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
            'commission_type'             => [
                'type'       => 'ENUM',
                'constraint' => ['percent', 'nominal'],
                'null'       => true,
            ],

            // alasan & approval
            'rejection_reason'   => ['type' => 'TEXT', 'null' => true],
            'inactive_reason'    => ['type' => 'TEXT', 'null' => true],
            'approved_at'        => ['type' => 'DATETIME', 'null' => true],
            'action_by'          => ['type' => 'INT', 'null' => true], // optional unsigned
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
