<?php

namespace App\Models;

use CodeIgniter\Model;

class AnnouncementsModel extends Model
{
    protected $table         = 'announcements';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'title',
        'content',
        'audience',
        'publish_at',
        'expires_at',
        'status',
        'created_at',
        'updated_at'
    ];
}
