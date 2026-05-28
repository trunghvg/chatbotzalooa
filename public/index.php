<?php

declare(strict_types=1);

$minPhpVersion = '8.1';
if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
    exit(sprintf(
        'Your PHP version must be %s or higher to run CodeIgniter. Current version: %s',
        $minPhpVersion,
        PHP_VERSION
    ));
}

define('FCPATH',         __DIR__ . DIRECTORY_SEPARATOR);
define('ROOTPATH',       realpath(FCPATH . '..') . DIRECTORY_SEPARATOR);
define('APPPATH',        ROOTPATH . 'app'     . DIRECTORY_SEPARATOR);
define('SYSTEMPATH',     ROOTPATH . 'vendor'  . DIRECTORY_SEPARATOR . 'codeigniter4' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
define('WRITEPATH',      ROOTPATH . 'writable'. DIRECTORY_SEPARATOR);
define('TESTPATH',       ROOTPATH . 'tests'   . DIRECTORY_SEPARATOR);
define('VIEWPATH',       APPPATH  . 'Views'   . DIRECTORY_SEPARATOR);
define('COMPOSER_PATH',  ROOTPATH . 'vendor'  . DIRECTORY_SEPARATOR . 'autoload.php');
define('APP_NAMESPACE',  'App');

// Environment and CI_DEBUG must be defined before CI4 boots
define('ENVIRONMENT', $_SERVER['CI_ENVIRONMENT'] ?? 'production');
define('CI_DEBUG',    ENVIRONMENT !== 'production');

if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(FCPATH);
}

require COMPOSER_PATH;
require SYSTEMPATH . 'Common.php';

// Register system/Config as fallback so CI4.7 system configs
// (Logger, Exceptions, Cookie, Feature, etc.) are auto-resolved
service('autoloader')->addNamespace('Config', SYSTEMPATH . 'Config');

$app = \Config\Services::codeigniter();
$app->initialize();
$context = is_cli() ? 'php-cli' : 'web';
$app->setContext($context);
$app->run();
