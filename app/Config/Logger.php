<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use Psr\Log\LogLevel;

class Logger extends BaseConfig
{
    public int    $threshold  = 4;
    public string $dateFormat = 'Y-m-d H:i:s';

    public array $handlers = [
        'CodeIgniter\Log\Handlers\FileHandler' => [
            'extension'  => 'log',
            'dateFormat' => 'Y-m-d H:i:s',
            'handles'    => [
                LogLevel::EMERGENCY,
                LogLevel::ALERT,
                LogLevel::CRITICAL,
                LogLevel::ERROR,
                LogLevel::WARNING,
                LogLevel::NOTICE,
                LogLevel::INFO,
                LogLevel::DEBUG,
            ],
        ],
    ];
}
