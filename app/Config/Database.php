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

        // Priority 1: individual Railway vars (immune to parse_url special-char bugs)
        if (getenv('MYSQLHOST') !== false) {
            $hostname = getenv('MYSQLHOST');
            $port     = (int) (getenv('MYSQLPORT')     ?: 3306);
            $username = getenv('MYSQLUSER')     ?: 'root';
            $password = getenv('MYSQLPASSWORD') ?: '';
            $database = getenv('MYSQLDATABASE') ?: 'railway';
        } elseif (getenv('DB_HOST') !== false) {
            // Priority 2: generic DB_* vars
            $hostname = getenv('DB_HOST');
            $port     = (int) (getenv('DB_PORT') ?: 3306);
            $username = getenv('DB_USER') ?: 'root';
            $password = getenv('DB_PASS') ?: '';
            $database = getenv('DB_NAME') ?: 'railway';
        } else {
            // Priority 3: parse MYSQL_URL / DATABASE_URL (may fail on special chars)
            $url      = getenv('MYSQL_URL') ?: getenv('DATABASE_URL') ?: '';
            $parsed   = $url ? parse_url($url) : [];
            $hostname = $parsed['host'] ?? '127.0.0.1';
            $port     = (int) ($parsed['port'] ?? 3306);
            $username = isset($parsed['user']) ? urldecode($parsed['user']) : 'root';
            $password = isset($parsed['pass']) ? urldecode($parsed['pass']) : '';
            $database = ltrim($parsed['path'] ?? '', '/') ?: 'railway';
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
