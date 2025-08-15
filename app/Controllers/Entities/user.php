<?php namespace App\Entities;

use CodeIgniter\Entity\Entity;
use CodeIgniter\Shield\Entities\User as ShieldUser;

class User extends ShieldUser
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [];
    
    // Additional methods specific to your application
    public function getDisplayName(): string
    {
        return $this->name ?? $this->email;
    }
}