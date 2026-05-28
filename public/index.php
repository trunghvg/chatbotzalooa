<?php

declare(strict_types=1);

/**
 * CodeIgniter bootstrap file.
 *
 * This file routes all requests through the CodeIgniter front controller.
 */

// Deny direct access to this script from the CLI
if (PHP_SAPI === 'cli') {
    exit('No CLI access allowed.');
}

/*
 * ---------------------------------------------------------------
 * Set the current directory correctly for CLI usage
 * ---------------------------------------------------------------
 */
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

chdir(dirname(__DIR__));

/*
 * ---------------------------------------------------------------
 * BOOTSTRAP THE APPLICATION
 * ---------------------------------------------------------------
 */
require FCPATH . '../vendor/autoload.php';
require FCPATH . '../app/Config/Paths.php';

// ^^^ NOTHING SHOULD APPEAR BEFORE THIS LINE ^^^

$paths = new Config\Paths();
require $paths->systemDirectory . '/bootstrap.php';

$app = Config\Services::codeigniter();
$app->initialize();
$context = is_cli() ? 'php-cli' : 'web';
$app->setContext($context);
$app->run();
