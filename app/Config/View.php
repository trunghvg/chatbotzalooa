<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class View extends BaseConfig
{
    public string $layout     = '';
    public bool   $saveData   = true;
    public array  $decorators = [];
}
