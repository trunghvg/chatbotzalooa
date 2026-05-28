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

        // Resolve connection params from all Railway MySQL env var patterns
        $mysqlUrl = getenv('MYSQL_URL') ?: getenv('DATABASE_URL') ?: '';

        if ($mysqlUrl) {
            $parsed   = parse_url($mysqlUrl);
            $hostname = $parsed['host'] ?? '';
            $port     = (int) ($parsed['port'] ?? 3306);
            $username = $parsed['user'] ?? 'root';
            $password = $parsed['pass'] ?? '';
            $database = ltrim($parsed['path'] ?? '', '/');
        } else {
            // Railway also exposes individual MYSQL* vars
            $hostname = getenv('MYSQLHOST')     ?: getenv('DB_HOST') ?: '127.0.0.1';
            $port     = (int) (getenv('MYSQLPORT')     ?: getenv('DB_PORT') ?: 3306);
            $username = getenv('MYSQLUSER')     ?: getenv('DB_USER') ?: 'root';
            $password = getenv('MYSQLPASSWORD') ?: getenv('DB_PASS') ?: '';
            $database = getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'railway';
        }

        $this->default = [
            'DSN'          => '',
            'hostname'     => $hostname,
            'username'     => $username,
            'password'     => $password,
            'database'     => $database,
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
            'port'         => $port,
            'numberNative' => false,
            'dateFormat'   => [
                'date'     => 'Y-m-d',
                'datetime' => 'Y-m-d H:i:s',
                'time'     => 'H:i:s',
            ],
        ];
    }
}
