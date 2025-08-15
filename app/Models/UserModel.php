<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users'; // Sesuai tabel Shield atau custom
    protected $primaryKey = 'id';

    protected $allowedFields = ['name', 'email', 'password', 'role', 'status'];

    protected $useTimestamps = true;
}
