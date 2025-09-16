<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use CodeIgniter\Session\Session;
use CodeIgniter\Validation\Validation;
use CodeIgniter\Pager\Pager;

abstract class BaseController extends Controller
{
    /**
     * @var \CodeIgniter\HTTP\CLIRequest|\CodeIgniter\HTTP\IncomingRequest
     */
    protected $request;

    /** Autoload helpers untuk semua controller (TANPA type-hint) */
    protected $helpers = ['url', 'form', 'text'];

    /** Services umum */
    protected ?Session $session = null;
    protected $auth = null;
    protected ?Validation $validation = null;
    protected ?Pager $pager = null;

    /** Pagination default */
    protected int $perPage = 15;

    /** Info user aktif (jika login) */
    protected ?\CodeIgniter\Shield\Entities\User $user = null;
    protected bool $isAdmin  = false;
    protected bool $isSeo    = false;
    protected bool $isVendor = false;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);

        date_default_timezone_set('Asia/Jakarta');

        $this->session    = service('session');
        $this->validation = service('validation');
        $this->pager      = service('pager');
        $this->auth       = service('auth');

        if ($this->auth && $this->auth->loggedIn()) {
            $this->user     = $this->auth->user();
            $this->isAdmin  = $this->user?->inGroup('admin')   ?? false;
            $this->isSeo    = $this->user?->inGroup('seoteam') ?? false;
            $this->isVendor = $this->user?->inGroup('vendor')  ?? false;
        }
    }

    /** User aktif (kalau perlu di controller lain) */
    protected function currentUser(): ?\CodeIgniter\Shield\Entities\User
    {
        return $this->user;
    }

    protected function q(): string
    {
        return trim((string) ($this->request->getGet('q') ?? ''));
    }

    protected function perPage(): int
    {
        $pp = (int) ($this->request->getGet('per_page') ?? $this->perPage);
        return ($pp > 0 && $pp <= 100) ? $pp : $this->perPage;
    }

    protected function jsonOK(array $data = [], int $status = 200)
    {
        return $this->response
            ->setStatusCode($status)
            ->setHeader('X-CSRF-RENEW', csrf_hash())
            ->setJSON(['status' => 'ok'] + $data);
    }

    protected function jsonFail(string $message, int $status = 400, array $extra = [])
    {
        return $this->response
            ->setStatusCode($status)
            ->setHeader('X-CSRF-RENEW', csrf_hash())
            ->setJSON(['status' => 'error', 'message' => $message] + $extra);
    }

    protected function view(string $view, array $data = [])
    {
        $globals = [
            'authUser' => $this->user,
            'isAdmin'  => $this->isAdmin,
            'isSeo'    => $this->isSeo,
            'isVendor' => $this->isVendor,
        ];
        return view($view, $globals + $data);
    }

    /**
     * Inject data notifikasi & unread ke header di SEMUA halaman.
     * Bisa dipakai langsung: array_merge($this->headerDataForUser(), [...])
     *
     * @param int|null   $userId
     * @param array|null $notificationsOverride  (opsional) daftar notifikasi yang sudah ada (biar tidak query ulang)
     * @param int|null   $unreadOverride         (opsional) unread count kalau sudah dihitung
     */
    // app/Controllers/BaseController.php

protected function headerDataForUser(int $userId, ?array $items = null, ?int $unread = null): array
{
    $db = db_connect();

    if ($items === null) {
        $rows = $db->table('notifications')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit(20)
            ->get()->getResultArray();
    } else {
        $rows = $items;
    }

    $normalized = [];
    $u = 0;

    foreach ($rows as $r) {
        $isRead = (int)($r['is_read'] ?? 0);
        $normalized[] = [
            'id'      => (int)($r['id'] ?? 0),
            'title'   => (string)($r['title'] ?? ''),
            'message' => (string)($r['message'] ?? ''),
            'is_read' => $isRead,
            // selalu sediakan 'date'
            'date'    => !empty($r['date'])
                ? $r['date']
                : (!empty($r['created_at']) ? date('Y-m-d H:i', strtotime($r['created_at'])) : '-'),
        ];
        if ($isRead === 0) $u++;
    }

    if ($unread !== null) {
        $u = (int)$unread;
    }

    $stats['unread'] = $u;

    return [
        'notifications' => $normalized,
        'stats'         => $stats,
    ];
}

    protected function viewVendorMaster(array $data = [], ?int $userId = null)
    {
        $layoutData = array_merge(
            $this->headerDataForUser($userId),
            $data,
            [
                // jaga-jaga nilai default agar layout tidak error
                'title'        => $data['title']        ?? 'Vendor Dashboard',
                'content_view' => $data['content_view'] ?? '',
                'content_data' => $data['content_data'] ?? [],
            ]
        );

        // flag global untuk header/sidebar
        $layoutData += [
            'authUser' => $this->user,
            'isAdmin'  => $this->isAdmin,
            'isSeo'    => $this->isSeo,
            'isVendor' => $this->isVendor,
        ];

        return view('vendoruser/layouts/vendor_master', $layoutData);
    }
}
