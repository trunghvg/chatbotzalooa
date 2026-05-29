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

    public string $defaultNamespace = 'App\Controllers';
    public string $defaultController = 'Admin';
    public string $defaultMethod = 'index';
    public bool $translateURIDashes = false;
    public string $override404 = 'App\Controllers\Errors::show404';
    public bool $autoRoute = false;
    public bool $prioritize = false;
    public bool $useControllerAttributes = false;
    public bool $multipleSegmentsOneParam = false;
}
