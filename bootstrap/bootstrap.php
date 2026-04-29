<?php

use Core\Config;
use Core\LunaORM\DB;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../core/helpers/helpers.php';

// Initialize config
$global_config = new Config();
$global_config->init();

if (config('database')) {
    DB::init();
}