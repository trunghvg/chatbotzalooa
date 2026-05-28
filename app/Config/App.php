<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class App extends BaseConfig
{
    // Base URL
    public string $baseURL = 'http://localhost:8080/';
    public array $allowedHostnames = [];
    public string $indexPage = '';
    public string $uriProtocol = 'REQUEST_URI';

    // Locale
    public string $defaultLocale = 'vi';
    public bool $negotiateLocale = false;
    public array $supportedLocales = ['vi', 'en'];

    // App settings
    public string $appTimezone = 'Asia/Ho_Chi_Minh';
    public string $charset = 'UTF-8';
    public bool $forceGlobalSecureRequests = false;
    public array $proxyIPs = [];

    // Session
    public string $sessionDriver         = 'CodeIgniter\Session\Handlers\FileHandler';
    public string $sessionCookieName     = 'ci_session';
    public int    $sessionExpiration     = 7200;
    public string $sessionSavePath       = WRITEPATH . 'session';
    public bool   $sessionMatchIP        = false;
    public int    $sessionTimeToUpdate   = 300;
    public bool   $sessionRegenerateDestroy = false;

    // Cookie
    public string  $cookiePrefix   = '';
    public string  $cookieDomain   = '';
    public string  $cookiePath     = '/';
    public bool    $cookieSecure   = false;
    public bool    $cookieHTTPOnly = true;
    public ?string $cookieSameSite = 'Lax';

    // CSRF
    public string $CSRFTokenName  = 'csrf_test_name';
    public string $CSRFHeaderName = 'X-CSRF-TOKEN';
    public string $CSRFCookieName = 'csrf_cookie_name';
    public int    $CSRFExpire     = 7200;
    public bool   $CSRFRegenerate = true;
    public bool   $CSRFRedirect   = false;
    public string $CSRFSameSite   = 'Lax';

    // Content Security Policy
    public bool $CSPEnabled = false;
}
