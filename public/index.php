<?php

declare(strict_types=1);

/*
 * ---------------------------------------------------------------
 * CHECK PHP VERSION
 * ---------------------------------------------------------------
 */
$minPhpVersion = '8.1';
if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
    $message = sprintf(
        'Your PHP version must be %s or higher to run CodeIgniter. Current version: %s',
        $minPhpVersion,
        PHP_VERSION
    );

    exit($message);
}

// Path to the front controller (this file)
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Ensure the current directory is pointing to the front controller's directory
if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(FCPATH);
}

/*
 * ---------------------------------------------------------------
 * BOOTSTRAP THE APPLICATION (CI4.5+ approach)
 * ---------------------------------------------------------------
 */
require FCPATH . '../vendor/autoload.php';

$app = \Config\Services::codeigniter();
$app->initialize();
$context = is_cli() ? 'php-cli' : 'web';
$app->setContext($context);
$app->run();
