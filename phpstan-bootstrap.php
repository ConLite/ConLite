<?php
if (!defined("CON_FRAMEWORK")) {
    define("CON_FRAMEWORK", true);
}

if (!defined('CL_VERSION')) {

    define('CL_VERSION', '3.1.0');

}

// include needed classes for conlib
include_once 'conlib/ct_sql.inc';
include_once 'conlib/session.inc';
include_once 'conlib/auth.inc';
include_once 'conlib/perm.inc';
include_once 'conlib/page.inc';

// init composer autoload
include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php');

$sPathCfgDir = dirname(__FILE__) . '/data/config/production/';

include_once($sPathCfgDir . 'config.php');
include_once($sPathCfgDir . 'config.path.php');
include_once($sPathCfgDir . 'config.misc.php');
include_once($sPathCfgDir . 'config.colors.php');
include_once($sPathCfgDir . 'config.path.php');
include_once($sPathCfgDir . 'config.templates.php');

$cfg['path']['config'] = $sPathCfgDir;

// Various base API functions
require_once($cfg['path']['conlite'] . $cfg['path']['includes'] . '/api/functions.api.general.php');

// Initialization of autoloader
include_once($cfg['path']['conlite'] . $cfg['path']['classes'] . 'class.autoload.php');
cAutoload::initialize($cfg);
