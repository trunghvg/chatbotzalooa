<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Cookie\Cookie;

class Cookie extends BaseConfig
{
    public string $prefix   = '';
    public int    $expires  = 0;
    public string $path     = '/';
    public string $domain   = '';
    public bool   $secure   = false;
    public bool   $httponly = true;
    public string $samesite = Cookie::SAMESITE_LAX;
    public bool   $raw      = false;
}
