<?php
/**
 * Project:
 * Contenido Content Management System
 *
 * Description:
 * Central Contenido file to initialize the application. Performs following steps:
 * - Does basic security check
 * - Includes configurations
 * - Runs validation of request variables
 * - Loads available login languages
 * - Initializes CEC
 * - Includes userdefined configuration
 * - Sets/Checks DB connection
 * - Initializes UrlBuilder
 *
 * @TODO: Collect all startup (bootstrap) related jobs into this file...
 *
 *
 * @package    Contenido Backend includes
 * @version    $Rev$
 * @author     four for Business AG
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.6
 *
 *   $Id$:
 */


if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

/*
 * Do not edit this value!
 *
 * If you want to set a different enviroment value please define it in your .htaccess file
 * or in the server configuration.
 *
 * SetEnv CL_ENVIRONMENT development
 * 
 * You may also use a php-file in conlite folder named enviroment.php with content
 * <?php
 * if (!defined('CON_FRAMEWORK')) {die('Illegal call');}
 * $sEnvironment = 'development';
 * ß>
 */
if (!defined('CL_ENVIRONMENT')) {
    if (getenv('CONLITE_ENVIRONMENT')) {
        $sEnvironment = getenv('CONLITE_ENVIRONMENT');
    } elseif (getenv('CL_ENVIRONMENT')) {
        $sEnvironment = getenv('CL_ENVIRONMENT');
    } else {
        if(file_exists(dirname(dirname(__FILE__))."/environment.php")) {
            include_once dirname(dirname(__FILE__))."/environment.php";
        }
        
        if(!isset($sEnvironment) || empty($sEnvironment)) {
            $sEnvironment = 'production';
        }
    }
    define('CL_ENVIRONMENT', $sEnvironment);
    unset($sEnvironment);
}

/*
 * SetEnv CL_VERSION
 */
if (!defined('CL_VERSION')) {

define('CL_VERSION', '3.0.0 RC');

}

// 1. security check: Include security class and invoke basic request checks
include_once(str_replace('\\', '/', realpath(dirname(__FILE__) . '/..')) . '/classes/class.security.php');
try {
    Contenido_Security::checkRequests();
} catch (Exception $e) {
    die($e->getMessage());
}


// "Workaround" for register_globals=off settings.
require_once(dirname(__FILE__) . '/globals_off.inc.php');
$sPathCfgDir = dirname(dirname(dirname(__FILE__))).'/data/config/'.CL_ENVIRONMENT.'/';
$sClConfigFile = $sPathCfgDir.'config.php';
// Check if configuration file exists, this is a basic indicator to find out, if Contenido is installed
if (!file_exists(dirname(__FILE__) . '/config.php') && !file_exists($sClConfigFile)) {
    $msg  = "<h1>Fatal Error</h1><br>";
    $msg .= "Could not open the configuration file <b>config.php</b>.<br><br>";
    $msg .= "Please make sure that you saved the file in the setup program. If you had to place the file manually on your webserver, make sure that it is placed in your data/config/ENVIROMENT directory.";
    die($msg);
}


// Include some basic configuration files
if(file_exists($sClConfigFile)) {
    include_once($sClConfigFile);
} else {
    include_once(dirname(__FILE__) . '/config.php');
}
include_once($sPathCfgDir.'config.path.php');
include_once($sPathCfgDir. 'config.misc.php');
include_once($sPathCfgDir . 'config.colors.php');
include_once($sPathCfgDir . 'config.path.php');
include_once($sPathCfgDir . 'config.templates.php');
include_once($sPathCfgDir . 'cfg_sql.inc.php');

$cfg['path']['config'] = $sPathCfgDir;

// Include userdefined configuration (if available), where you are able to
// extend/overwrite core settings from included configuration files above
if(file_exists($sPathCfgDir.'config.local.php')) {
    include_once($sPathCfgDir.'config.local.php');
} else if(file_exists($cfg['path']['contenido'].$cfg['path']['includes'] . '/config.local.php')) {
    include_once($cfg['path']['contenido'] . $cfg['path']['includes'] . '/config.local.php');
}

// check $belang and set default
if(!isset($belang) || empty($belang)) {
    $belang = "de_DE";
}

$I18N_EMULATE_GETTEXT = false;

// Various base API functions
require_once($cfg['path']['conlite'] . $cfg['path']['includes'] . '/api/functions.api.general.php');


// Initialization of autoloader
include_once($cfg['path']['conlite'] . $cfg['path']['classes'] . 'class.autoload.php');
cAutoload::initialize($cfg);
// init composer autoload
include_once(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'vendor/autoload.php');


// 2. security check: Check HTTP parameters, if requested
if ($cfg['http_params_check']['enabled'] === true) {
    $oHttpInputValidator =
        new HttpInputValidator($cfg['path']['config'] . CL_ENVIRONMENT . DIRECTORY_SEPARATOR . 'config.http_check.php');
}

/* Generate arrays for available login languages
 * ---------------------------------------------
 * Author: Martin Horwath
 */

global $cfg;

$handle = opendir($cfg['path']['contenido'] . $cfg['path']['locale']);

while ($locale = readdir($handle)) {
   if (is_dir($cfg['path']['contenido'] . $cfg['path']['locale'] . $locale) && $locale != '..' && $locale != '.') {
      if (file_exists($cfg['path']['contenido'] . $cfg['path']['locale'] . $locale . '/LC_MESSAGES/conlite.po') &&
         file_exists($cfg['path']['contenido'] . $cfg['path']['locale'] . $locale . '/LC_MESSAGES/conlite.mo') &&
         file_exists($cfg['path']['contenido'] . $cfg['path']['xml'] . 'lang_'.$locale.'.xml') ) {

         $cfg['login_languages'][] = $locale;
         $cfg['lang'][$locale] = 'lang_'.$locale.'.xml';
      }
   }
}


// Some general includes
cInclude('includes', 'functions.general.php');
cInclude('conlib', 'prepend.php');
cInclude('includes', 'functions.i18n.php');
cInclude('includes', 'functions.con.php');


// Initialization of CEC
$_cecRegistry = cApiCECRegistry::getInstance();
cInclude('config', 'config.chains.php');

// fallback to old db-connection settings
if(!isset($cfg['db']) || !is_array($cfg['db'])) {
    $cfg['db'] = array(
    'connection' => array(
        'host'     => $contenido_host,
        'database' => $contenido_database,
        'user'     => $contenido_user,
        'password' => $contenido_password,
    ),
    'nolock'          => false, // (bool) Flag to not lock tables
    'sequenceTable'   => '',       // (string) will be set later in startup!
    'haltBehavior'    => 'report', // (string) Feasible values are 'yes', 'no' or 'report'
    'haltMsgPrefix'   => (isset($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] . ' ' : '',
    'enableProfiling' => false,    // (bool) Flag to enable profiling
);
}
// Set default database connection parameter
$cfg['db']['sequenceTable'] = $cfg['tab']['sequence'];
DB_ConLite::setDefaultConfiguration($cfg['db']);

// @TODO: This should be done by instantiating a DB_ConLite class, creation of DB_ConLite object
checkMySQLConnectivity();


// Initialize UrlBuilder, configuration is set in /contenido/includes/config.misc.php
Contenido_UrlBuilderConfig::setConfig($cfg['url_builder']);

// set global encoding array
if (!isset($encoding) || !is_array($encoding) || count($encoding) == 0) {
    // get encodings of all languages
    $db = new DB_ConLite();
    $encoding = array();
    $sql = "SELECT idlang, encoding FROM " . $cfg["tab"]["lang"];
    $db->query($sql);
    while ($db->next_record()) {
        $encoding[$db->f('idlang')] = $db->f('encoding');
    }
}

if($cfg['debug']['sendnocacheheader']) {
    header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
    header("Pragma: no-cache"); // HTTP 1.0.
    header("Expires: 0"); // Proxies.
}
?>
