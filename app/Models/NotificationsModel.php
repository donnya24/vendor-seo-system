<?php
// app/Models/NotificationsModel.php
namespace App\Models;

use CodeIgniter\Model;

class NotificationsModel extends Model
{
    protected $table         = 'notifications';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'user_id', 'title', 'message', 'link_url',
        'is_read', 'read_at',
        'created_at', 'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function markAsRead(int $id): bool
    {
        return (bool) $this->update($id, ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')]);
    }

    public function unreadCountForUser(int $userId): int
    {
        return $this->where(['user_id' => $userId, 'is_read' => 0])->countAllResults();
    }
}
