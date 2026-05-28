<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class Auth extends BaseController
{
    public function login(): string|ResponseInterface
    {
        if (session()->get('admin_logged_in')) {
            return redirect()->to('/admin');
        }

        return view('auth/login', ['title' => 'Đăng nhập Admin']);
    }

    public function doLogin(): ResponseInterface
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $adminUsername = env('ADMIN_USERNAME', 'admin');
        $adminPassword = env('ADMIN_PASSWORD', 'Admin@123456');

        if ($username === $adminUsername && $password === $adminPassword) {
            session()->set([
                'admin_logged_in' => true,
                'admin_username'  => $username,
            ]);

            log_message('info', "[Auth] Admin login: $username");
            return redirect()->to('/admin');
        }

        log_message('warning', "[Auth] Failed login attempt: $username");
        return redirect()->to('/auth/login')
                         ->with('error', 'Tên đăng nhập hoặc mật khẩu không đúng!');
    }

    public function logout(): ResponseInterface
    {
        session()->destroy();
        return redirect()->to('/auth/login')
                         ->with('success', 'Đã đăng xuất thành công.');
    }
}
