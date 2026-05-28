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

// Path to the front controller (this file)
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Ensure the current directory is pointing to the front controller's directory
if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(FCPATH);
}

// Define all CI4 path constants required by the framework
define('ROOTPATH',   realpath(FCPATH . '..') . DIRECTORY_SEPARATOR);
define('APPPATH',    ROOTPATH . 'app' . DIRECTORY_SEPARATOR);
define('SYSTEMPATH', ROOTPATH . 'vendor' . DIRECTORY_SEPARATOR . 'codeigniter4' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
define('WRITEPATH',     ROOTPATH . 'writable' . DIRECTORY_SEPARATOR);
define('TESTPATH',      ROOTPATH . 'tests' . DIRECTORY_SEPARATOR);
define('VIEWPATH',      APPPATH . 'Views' . DIRECTORY_SEPARATOR);
define('COMPOSER_PATH', ROOTPATH . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

require FCPATH . '../vendor/autoload.php';
require SYSTEMPATH . 'Common.php';

// Add system/Config as fallback so CI4.7 system configs are found
// when not overridden in app/Config/
service('autoloader')->addNamespace('Config', SYSTEMPATH . 'Config');

$app = \Config\Services::codeigniter();
$app->initialize();
$context = is_cli() ? 'php-cli' : 'web';
$app->setContext($context);
$app->run();
