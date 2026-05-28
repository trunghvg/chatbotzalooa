<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class UserAgents extends BaseConfig
{
    public array $platforms = [
        'windows nt 10.0' => 'Windows 10',
        'windows nt 6.3'  => 'Windows 8.1',
        'windows nt 6.2'  => 'Windows 8',
        'windows nt 6.1'  => 'Windows 7',
        'windows nt 6.0'  => 'Windows Vista',
        'windows nt 5.2'  => 'Windows 2003',
        'windows nt 5.1'  => 'Windows XP',
        'windows nt 5.0'  => 'Windows 2000',
        'windows'         => 'Windows',
        'os x'            => 'Mac OS X',
        'linux'           => 'Linux',
        'ubuntu'          => 'Ubuntu Linux',
        'android'         => 'Android',
        'iphone'          => 'iPhone',
        'ipad'            => 'iPad',
    ];

    public array $browsers = [
        'OPR'     => 'Opera',
        'Flock'   => 'Flock',
        'Edge'    => 'Edge',
        'Edg'     => 'Edge',
        'Chrome'  => 'Chrome',
        'Firefox' => 'Firefox',
        'Safari'  => 'Safari',
        'MSIE'    => 'Internet Explorer',
    ];

    public array $mobiles = [
        'mobileexplorer' => 'Mobile Explorer',
        'android'        => 'Android',
        'iphone'         => 'Apple iPhone',
        'ipad'           => 'Apple iPad',
        'kindle'         => 'Kindle',
    ];

    public array $robots = [
        'googlebot'     => 'Googlebot',
        'bingbot'       => 'MSNBot',
        'slurp'         => 'Inktomi Slurp',
        'yahoo'         => 'Yahoo',
        'facebookbot'   => 'Facebook Bot',
        'twitterbot'    => 'Twitter Bot',
    ];
}
