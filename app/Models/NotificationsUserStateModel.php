<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationsUserStateModel extends Model
{
    protected $table            = 'notification_user_state';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;

    // Kolom yang boleh diisi massal
    protected $allowedFields    = [
        'notification_id',
        'user_id',
        'is_read',
        'read_at',
        'hidden',
        'hidden_at',
        'created_at',
    ];

    // Aktifkan timestamps otomatis jika perlu
    protected $useTimestamps = false; // tabel ini pakai created_at manual
    protected $returnType    = 'array';

    /**
     * Tandai notifikasi sudah dibaca
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        return $this->where('notification_id', $notificationId)
                    ->where('user_id', $userId)
                    ->set([
                        'is_read' => 1,
                        'read_at' => date('Y-m-d H:i:s'),
                    ])
                    ->update();
    }

    /**
     * Tandai notifikasi disembunyikan
     */
    public function hideNotification(int $notificationId, int $userId): bool
    {
        return $this->where('notification_id', $notificationId)
                    ->where('user_id', $userId)
                    ->set([
                        'hidden'    => 1,
                        'hidden_at' => date('Y-m-d H:i:s'),
                    ])
                    ->update();
    }

    /**
     * Ambil semua notifikasi user (opsional filter hanya unread)
     */
    public function getUserNotifications(int $userId, bool $onlyUnread = false, int $limit = 20): array
    {
        $builder = $this->where('user_id', $userId)
                        ->join('notifications n', 'n.id = notification_user_state.notification_id', 'left')
                        ->orderBy('n.created_at', 'DESC')
                        ->limit($limit);

        if ($onlyUnread) {
            $builder->where('is_read', 0);
        }

        return $builder->findAll();
    }
}
