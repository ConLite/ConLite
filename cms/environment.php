<?php

// Load environment config file
$configEnv = str_replace('\\', '/', realpath(dirname(__FILE__) . '/')) . '/data/config/config.environment.php';
if (file_exists($configEnv)) {
    include_once($configEnv);
}

if (!defined('CL_ENVIRONMENT')) {
    if (getenv('CONLITE_ENVIRONMENT')) {
        define('CL_ENVIRONMENT', getenv('CONLITE_ENVIRONMENT'));
    } if (getenv('CONTENIDO_ENVIRONMENT')) {
        define('CL_ENVIRONMENT', getenv('CONTENIDO_ENVIRONMENT'));
    }  else {
        define('CL_ENVIRONMENT', 'production');
    }
}

//echo CL_ENVIRONMENT;