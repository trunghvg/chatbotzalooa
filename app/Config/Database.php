<?php

namespace Config;

use CodeIgniter\Database\Config;

class Database extends Config
{
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

    public string $defaultGroup = 'default';

    public array $default = [];

    public array $tests = [
        'DSN'          => '',
        'hostname'     => 'localhost',
        'username'     => 'root',
        'password'     => '',
        'database'     => 'chatbotzalooa_test',
        'DBDriver'     => 'MySQLi',
        'DBPrefix'     => '',
        'pConnect'     => false,
        'DBDebug'      => true,
        'charset'      => 'utf8mb4',
        'DBCollat'     => 'utf8mb4_unicode_ci',
        'swapPre'      => '',
        'encrypt'      => false,
        'compress'     => false,
        'strictOn'     => false,
        'failover'     => [],
        'port'         => 3306,
        'numberNative' => false,
    ];

    public function __construct()
    {
        parent::__construct();

        // Railway / Docker: parse MYSQL_URL hoac DATABASE_URL
        $mysqlUrl = getenv('MYSQL_URL') ?: getenv('DATABASE_URL') ?: '';

        if ($mysqlUrl) {
            $parsed = parse_url($mysqlUrl);
            $this->default = [
                'DSN'          => '',
                'hostname'     => $parsed['host'] ?? 'localhost',
                'username'     => $parsed['user'] ?? 'root',
                'password'     => $parsed['pass'] ?? '',
                'database'     => ltrim($parsed['path'] ?? '/chatbotzalooa', '/'),
                'DBDriver'     => 'MySQLi',
                'DBPrefix'     => '',
                'pConnect'     => false,
                'DBDebug'      => false,
                'charset'      => 'utf8mb4',
                'DBCollat'     => 'utf8mb4_unicode_ci',
                'swapPre'      => '',
                'encrypt'      => false,
                'compress'     => false,
                'strictOn'     => false,
                'failover'     => [],
                'port'         => (int) ($parsed['port'] ?? 3306),
                'numberNative' => false,
                'dateFormat'   => [
                    'date'     => 'Y-m-d',
                    'datetime' => 'Y-m-d H:i:s',
                    'time'     => 'H:i:s',
                ],
            ];
        } else {
            // Local / .env tung bien rieng
            $this->default = [
                'DSN'          => '',
                'hostname'     => getenv('DB_HOST') ?: env('database.default.hostname', '127.0.0.1'),
                'username'     => getenv('DB_USER') ?: env('database.default.username', 'root'),
                'password'     => getenv('DB_PASS') ?: env('database.default.password', ''),
                'database'     => getenv('DB_NAME') ?: env('database.default.database', 'chatbotzalooa'),
                'DBDriver'     => 'MySQLi',
                'DBPrefix'     => '',
                'pConnect'     => false,
                'DBDebug'      => env('CI_ENVIRONMENT') !== 'production',
                'charset'      => 'utf8mb4',
                'DBCollat'     => 'utf8mb4_unicode_ci',
                'swapPre'      => '',
                'encrypt'      => false,
                'compress'     => false,
                'strictOn'     => false,
                'failover'     => [],
                'port'         => (int) (getenv('DB_PORT') ?: env('database.default.port', 3306)),
                'numberNative' => false,
                'dateFormat'   => [
                    'date'     => 'Y-m-d',
                    'datetime' => 'Y-m-d H:i:s',
                    'time'     => 'H:i:s',
                ],
            ];
        }
    }
}
