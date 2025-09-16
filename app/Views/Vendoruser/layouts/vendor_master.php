<?php
/**
 * Master layout Vendor:
 * - Auto load vendor profile
 * - Auto load notifications + unread (private / vendor-wide / global)
 * - Render header, konten halaman, global modals, dan footer
 */

$auth = service('auth');
$user = $auth ? $auth->user() : null;
$uid  = (int) ($user->id ?? 0);

/** ===== Vendor profile untuk header/sidebar ===== */
if (!isset($vp)) {
    try {
        $vp = $uid
            ? (new \App\Models\VendorProfilesModel())->where('user_id', $uid)->first()
            : null;
    } catch (\Throwable $e) {
        $vp = null;
    }
}
$isVerified = $isVerified ?? ((($vp['status'] ?? '') === 'verified'));

/** ===== Notifications loader (SELALU load di sini untuk header) ===== */
$notifications     = [];            // selalu sediakan
$stats             = $stats ?? [];  // field lain biarkan, 'unread' diisi di bawah
$stats['unread']   = 0;
$openNotifModal    = !empty($openNotifModal);   // flag dari controller
$suppress_content  = !empty($suppress_content); // opsi jika controller mau halaman kosong (popup only)

if ($uid > 0) {
    try {
        $db = db_connect();

        if ($db->tableExists('notifications')) {
            $vendorId = (int) ($vp['id'] ?? 0);
            $hasState = $db->tableExists('notification_user_state');

            $b = $db->table('notifications n');

            // Normalisasi is_read:
            // - private (n.user_id = uid)         -> n.is_read
            // - vendor/global (n.user_id IS NULL) -> nus.is_read per user (default 0)
            $select = "
                n.id, n.user_id, n.vendor_id, n.type, n.title, n.message,
                n.created_at AS date
            ";
            if ($hasState) {
                $select .= ",
                COALESCE(CASE WHEN n.user_id = {$uid} THEN n.is_read ELSE nus.is_read END, 0) AS is_read
                ";
                $b->join('notification_user_state nus', "nus.notification_id = n.id AND nus.user_id = {$uid}", 'left');
            } else {
                $select .= ",
                CASE WHEN n.user_id = {$uid} THEN COALESCE(n.is_read,0) ELSE 0 END AS is_read
                ";
            }

            $b->select($select, false);

            // Scope:
            //  a) Private              : n.user_id = uid
            //  b) Vendor-wide          : n.user_id IS NULL AND n.vendor_id = current vendor id
            //  c) Global announcement  : n.user_id IS NULL AND n.vendor_id IS NULL AND n.type='announcement'
            $b->groupStart()
                  ->where('n.user_id', $uid)
                  ->orGroupStart()
                      ->where('n.user_id', null)
                      ->where('n.vendor_id', $vendorId)
                  ->groupEnd()
                  ->orGroupStart()
                      ->where('n.user_id', null)
                      ->where('n.vendor_id', null)
                      ->where('n.type', 'announcement')
                  ->groupEnd()
              ->groupEnd();

            // Exclude hidden per-user bila tabel state ada
            if ($hasState) {
                $b->groupStart()
                      ->where('nus.hidden', 0)
                      ->orWhere('nus.hidden IS NULL', null, false)
                  ->groupEnd();
            }

            $rows = $b->orderBy('n.created_at', 'DESC')
                      ->limit(20)
                      ->get()->getResultArray();

            // Normalisasi tanggal + hitung unread
            $unread = 0;
            foreach ($rows as &$r) {
                $r['date']    = !empty($r['date']) ? date('Y-m-d H:i', strtotime($r['date'])) : '-';
                $r['is_read'] = (int)($r['is_read'] ?? 0);
                if ($r['is_read'] === 0) $unread++;
            }
            unset($r);

            $notifications   = $rows;
            $stats['unread'] = $unread;
        }
    } catch (\Throwable $e) {
        // kalau DB error, biarkan notifikasi kosong agar header tetap aman
        log_message('error', 'Failed loading notifications in vendor_master: '.$e->getMessage());
        $notifications   = [];
        $stats['unread'] = $stats['unread'] ?? 0;
    }
}

/** ===== Title & konten ===== */
$title        = $title        ?? 'Vendor Dashboard';
$content_view = $content_view ?? '';
$content_data = $content_data ?? [];

/** ===== Render header (sidebar + topbar di dalamnya) ===== */
echo view('vendoruser/layouts/header', get_defined_vars());
?>

<!-- ====== PAGE CONTENT ====== -->
<div class="w-full min-h-[calc(100vh-3.5rem)]">
  <?php if ($suppress_content || $openNotifModal): ?>
    <!-- Sengaja kosong: halaman ini hanya memicu modal notifikasi -->
  <?php elseif (!empty($content_html)): ?>
    <?= $content_html ?>
  <?php elseif (!empty($content_view)): ?>
    <?= view($content_view, $content_data ?? []) ?>
  <?php else: ?>
    <div class="p-4 text-sm text-gray-500">Tidak ada konten (content_view belum diisi).</div>
  <?php endif; ?>
</div>

<div x-show="$store.ui.loading"
     x-transition.opacity.duration.200ms
     class="fixed inset-0 bg-black/40 backdrop-blur-[2px] z-[100]"
     style="display:none;">
</div>

<?php
// ===== Global Modals (bisa dibuka dari header di halaman mana pun) =====
echo view('vendoruser/profile/edit', ['vp' => $vp ?? []]);
echo view('vendoruser/profile/ubahpassword');

// ===== Footer (script global, CSRF sync, dll) =====
echo view('vendoruser/layouts/footer', get_defined_vars());
