<?php
/**
 * Master layout Vendor
 */

$auth = service('auth');
$user = $auth ? $auth->user() : null;
$uid  = (int) ($user->id ?? 0);

/** ===== Vendor profile ===== */
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

/** ===== Notifications ===== */
$notifications     = [];
$stats             = $stats ?? [];
$stats['unread']   = 0;
$openNotifModal    = !empty($openNotifModal);
$suppress_content  = !empty($suppress_content);

if ($uid > 0) {
    try {
        $db = db_connect();
        if ($db->tableExists('notifications')) {
            $vendorId = (int) ($vp['id'] ?? 0);
            $hasState = $db->tableExists('notification_user_state');
            $b = $db->table('notifications n');

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

            if ($hasState) {
                $b->groupStart()
                      ->where('nus.hidden', 0)
                      ->orWhere('nus.hidden IS NULL', null, false)
                  ->groupEnd();
            }

            $rows = $b->orderBy('n.created_at', 'DESC')
                      ->limit(20)
                      ->get()->getResultArray();

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
        log_message('error', 'Failed loading notifications: '.$e->getMessage());
    }
}

/** ===== Title & konten ===== */
$title        = $title        ?? 'Vendor Dashboard';
$content_view = $content_view ?? '';
$content_data = $content_data ?? [];

/** ===== Render header ===== */
echo view('vendoruser/layouts/header', get_defined_vars());
?>

<!-- ====== PAGE CONTENT ====== -->
<div id="page-wrapper"
     x-data
     x-transition:enter="transform transition ease-out duration-400"
     x-transition:enter-start="translate-y-10 translate-x-5 opacity-0"
     x-transition:enter-end="translate-y-0 translate-x-0 opacity-100"
     class="w-full min-h-[calc(100vh-3.5rem)]">

  <?php if ($suppress_content || $openNotifModal): ?>
    <!-- kosong -->
  <?php elseif (!empty($content_html)): ?>
    <?= $content_html ?>
  <?php elseif (!empty($content_view)): ?>
    <?= view($content_view, $content_data ?? []) ?>
  <?php else: ?>
    <div class="p-4 text-sm text-gray-500">Tidak ada konten.</div>
  <?php endif; ?>
</div>

<!-- Loading Bar -->
<div x-data="loadingBar()" 
     x-init="init()"
     x-show="active"
     class="fixed top-0 left-0 h-1 bg-blue-500 z-[100] transition-all duration-300"
     :style="`width:${width}%; opacity:${active?1:0}`"></div>

<script>
function loadingBar() {
  return {
    width: 0,
    active: false,

    init() {
      // Browser events
      document.addEventListener('DOMContentLoaded', () => this.to(30));
      window.addEventListener('load', () => this.finish());

      // SPA custom events
      window.addEventListener('spa-loading-start', () => this.start());
      window.addEventListener('spa-loading-done', () => this.finish());

      // Tangkap semua klik <a> internal
      document.addEventListener('click', e => {
        const a = e.target.closest('a');
        if (!a) return;
        const url = a.getAttribute('href') || '';
        // abaikan link eksternal / #hash
        if (url.startsWith('http') && !url.includes(location.host)) return;
        if (url.startsWith('#')) return;

        // Trigger bar langsung
        window.dispatchEvent(new Event('spa-loading-start'));
      });
    },

    start() {
      this.active = true;
      this.width = 0;
      this.grow();
    },

    grow() {
      if (!this.active) return;
      this.width += (100 - this.width) * 0.08;
      if (this.width < 98) {
        requestAnimationFrame(() => this.grow());
      }
    },

    to(val) {
      this.width = Math.max(this.width, val);
    },

    finish() {
      this.width = 100;
      setTimeout(() => this.active = false, 400);
    }
  }
}
</script>


<?php
// ===== Global Modals =====
echo view('vendoruser/profile/edit', ['vp' => $vp ?? []]);
echo view('vendoruser/profile/ubahpassword');

// ===== Footer =====
echo view('vendoruser/layouts/footer', get_defined_vars());
?>
