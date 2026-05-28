<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Debug\ExceptionHandler;

class Exceptions extends BaseConfig
{
    public bool   $log              = true;
    public int    $sensitivityLevel = 1;
    public array  $ignoredExceptions = [];
    public string $handler          = ExceptionHandler::class;
}
