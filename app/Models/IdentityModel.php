<?php
// app/Models/IdentityModel.php

namespace App\Models;

use CodeIgniter\Model;

class IdentityModel extends Model
{
    protected $table = 'auth_identities';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'type', 'secret', 'secret2', 'created_at', 'updated_at'];
    protected $useTimestamps = false;
    protected $returnType = 'array';
}