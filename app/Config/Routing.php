<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Routing extends BaseConfig
{
    /**
     * Route files loaded in order, first match wins.
     *
     * @var list<string>
     */
    public array $routeFiles = [
        APPPATH . 'Config/Routes.php',
    ];

    public string $defaultNamespace = '\\';
    public string $defaultController = 'Home';
    public string $defaultMethod = 'index';
    public bool $translateURIDashes = false;
    public string $override404 = '';
    public bool $autoRoute = false;
    public bool $prioritize = false;
}
