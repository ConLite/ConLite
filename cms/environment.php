<?php

// Load environment config file
$configEnv = str_replace('\\', '/', realpath(dirname(__FILE__) . '/')) . '/data/config/config.environment.php';
if (file_exists($configEnv)) {
    include_once($configEnv);
}

if (!defined('CL_ENVIRONMENT')) {
    if (getenv('CONLITE_ENVIRONMENT')) {
        $sEnvironment = getenv('CONLITE_ENVIRONMENT');
    } elseif (getenv('CL_ENVIRONMENT')) {
        $sEnvironment = getenv('CL_ENVIRONMENT');
    } else {
        $sEnvironment =  'production';
    }
    define('CL_ENVIRONMENT', $sEnvironment);
    unset($sEnvironment);
}