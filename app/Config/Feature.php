<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Feature extends BaseConfig
{
    public bool $limitZeroAsAll       = true;
    public bool $strictLocaleNegotiation = true;
    public bool $autoRoutesImproved   = false;
    public bool $multilineStringInCSP = false;
}
