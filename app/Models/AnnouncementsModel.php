<?php

namespace App\Models;

use CodeIgniter\Model;

class AnnouncementsModel extends Model
{
    protected $table         = 'announcements';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'title','content','audience','publish_date','expire_date','is_pinned',
        'created_at','updated_at'
    ];
}
