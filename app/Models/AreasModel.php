<?php

namespace App\Models;

use CodeIgniter\Model;

class AreasModel extends Model
{
    protected $table      = 'areas';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    // Kolom di tabel: id, name, type, created_at, updated_at
    protected $allowedFields = ['name', 'type', 'created_at', 'updated_at'];

    // Timestamps tidak di-auto (controller sudah set created_at sendiri)
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';

    // ---- Helper opsional agar controller rapih ----

    /** Cari nama (LIKE) untuk auto-suggest. */
    public function searchByName(string $q, int $limit = 30): array
    {
        $builder = $this->select('id, name, type')->orderBy('name', 'ASC');
        if ($q !== '') {
            $builder->like('name', $q);
        }
        // PENTING: findAll(limit) agar LIKE/ORDER diterapkan
        return $builder->findAll($limit);
    }

    /** Pastikan "Seluruh Indonesia" ada; kembalikan ID-nya. */
    public function getAllIndonesiaId(): int
    {
        $row = $this->where('name', 'Seluruh Indonesia')->first();
        if ($row) return (int)$row['id'];

        $this->insert([
            'name'       => 'Seluruh Indonesia',
            'type'       => 'region',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return (int) $this->getInsertID();
    }
}
