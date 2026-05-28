<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Debug\Toolbar\Collectors\Database;
use CodeIgniter\Debug\Toolbar\Collectors\Events;
use CodeIgniter\Debug\Toolbar\Collectors\Files;
use CodeIgniter\Debug\Toolbar\Collectors\Logs;
use CodeIgniter\Debug\Toolbar\Collectors\Routes;
use CodeIgniter\Debug\Toolbar\Collectors\Timers;
use CodeIgniter\Debug\Toolbar\Collectors\Views;

class Toolbar extends BaseConfig
{
    public bool   $enabled    = false; // disabled in production
    public int    $maxHistory = 20;
    public int    $maxQueries = 100;
    public array  $collectors = [
        Timers::class,
        Database::class,
        Logs::class,
        Views::class,
        Files::class,
        Routes::class,
        Events::class,
    ];
    public array  $watch      = [];
}
