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
}
