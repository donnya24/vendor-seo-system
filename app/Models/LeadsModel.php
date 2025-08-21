<?php
namespace App\Models;

use CodeIgniter\Model;

class LeadsModel extends Model
{
    protected $table      = 'leads';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'customer_name','vendor_id','service_id','status','source',
        'wa_chat_id','wa_message_id','created_at','updated_at'
    ];
    protected $useTimestamps = false;
}
