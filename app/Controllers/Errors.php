<?php

namespace App\Controllers;

class Errors extends BaseController
{
    public function show404(): string
    {
        $this->response->setStatusCode(404);
        return '<h1>404 - Không tìm thấy trang</h1><p><a href="/">Về trang chủ</a></p>';
    }
}
