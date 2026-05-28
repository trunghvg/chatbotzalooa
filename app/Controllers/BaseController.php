<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    protected $request;

    protected $helpers = ['url', 'form', 'html', 'text'];

    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
    }

    /**
     * Kiem tra da dang nhap chua (cho admin)
     */
    protected function requireAuth(): void
    {
        $session = session();
        if (!$session->get('admin_logged_in')) {
            redirect()->to('/auth/login')->send();
            exit;
        }
    }

    /**
     * Tra ve JSON response
     */
    protected function jsonResponse(array $data, int $statusCode = 200): ResponseInterface
    {
        return $this->response
            ->setStatusCode($statusCode)
            ->setContentType('application/json')
            ->setBody(json_encode($data, JSON_UNESCAPED_UNICODE));
    }
}
