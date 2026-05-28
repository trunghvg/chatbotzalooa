<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class ContentSecurityPolicy extends BaseConfig
{
    public bool         $reportOnly              = false;
    public array|string $defaultSrc              = 'none';
    public array|string $scriptSrc               = 'self';
    public array|string $styleSrc                = 'self';
    public array|string $imageSrc                = 'self';
    public array|string $baseURI                 = 'self';
    public array|string $childSrc               = '';
    public array|string $connectSrc             = 'self';
    public array|string $fontSrc                = '';
    public array|string $formAction             = 'self';
    public array|string $frameAncestors         = 'none';
    public array|string $frameSrc               = '';
    public array|string $mediaSrc               = '';
    public array|string $objectSrc              = 'none';
    public string       $sandbox                 = '';
    public array|string $scriptSrcAttr          = '';
    public array|string $scriptSrcElem          = '';
    public array|string $styleSrcAttr           = '';
    public array|string $styleSrcElem           = '';
    public array|string $workerSrc              = 'self';
    public array        $requireTrustedTypesFor  = [];
    public array        $trustedTypes            = [];
    public array        $reportURI               = [];
    public bool         $autoNonce               = true;
    public string       $styleNonce              = '';
    public string       $scriptNonce             = '';
    public string       $upgradeInsecureRequests = '';
}
