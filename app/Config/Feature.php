<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Feature extends BaseConfig
{
    public bool $multipleFilters          = false;
    public bool $strictLocaleNegotiation  = true;
    public bool $commandLineRoutes        = true;
    public bool $autoRoutesImproved       = false;
}
