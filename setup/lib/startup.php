<?php

/**
 * ConLite setup bootstrap file
 * 
 * @package ConLite
 * @subpackage Setup
 * @version 1.0.0
 * @author Ortwin Pinke <oldperl@ortwinpinke.de>
 * @author Murat Purc <murat@purc.de>
 * @copyright (c) 2020, ConLite.org
 * @license https://www.gnu.org/licenses/gpl-3.0.de.html GNU General Public License 3
 * @link https://conlite.org ConLite Portal
 * @since file available since contenido release <= 4.8.15
 * 
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}
define('CON_BE_PATH', '../conlite/');

session_start();

require_once 'lib/defines.php';
/*
 * SetEnv CL_VERSION
 */
if (!defined('CL_VERSION')) {

    define('CL_VERSION', C_SETUP_VERSION);

}

// uncomment this lines during development if needed
error_reporting(E_ALL ^ E_NOTICE);
ini_set("display_errors", true);
ini_set("log_errors", true);
ini_set("error_log", "../data/logs/setup_errorlog.txt");

header('Content-Type: text/html; charset=UTF-8');

// Check php version
if (version_compare(PHP_VERSION, C_SETUP_MIN_PHP_VERSION, '<')) {
    die("You need PHP >= " . C_SETUP_MIN_PHP_VERSION . " to install ConLite " . C_SETUP_VERSION . ". Sorry, even the setup doesn't work otherwise. Your version: " . PHP_VERSION . "\n");
} else if (version_compare(PHP_VERSION, C_SETUP_MAX_PHP_VERSION, '>=')) {
     die("You need PHP < " . C_SETUP_MAX_PHP_VERSION . " to install ConLite " . C_SETUP_VERSION . ". Sorry, even the setup doesn't work otherwise. Your version: " . PHP_VERSION . "\n");
}


/*
 * Do not edit this value!
 *
 * If you want to set a different enviroment value please define it in your .htaccess file
 * or in the server configuration.
 *
 * SetEnv CONLITE_ENVIRONMENT development
 */
if (!defined('CL_ENVIRONMENT')) {
    if (getenv('CONLITE_ENVIRONMENT')) {
        $sEnvironment = getenv('CONLITE_ENVIRONMENT');
    } else if (getenv('CL_ENVIRONMENT')) {
        $sEnvironment = getenv('CL_ENVIRONMENT');
    } else {
        // @TODO: provide a possibility to set the environment value via file
        $sEnvironment = 'production';
    }

    define('CL_ENVIRONMENT', $sEnvironment);
}

// include security class and check request variables
include_once(CON_BE_PATH . 'classes/class.security.php');
Contenido_Security::checkRequests();

/**
 * Setup file inclusion
 *
 * @param  string  $filename
 * @return void
 */
function checkAndInclude($filename) {
    if (file_exists($filename) && is_readable($filename)) {
        require_once($filename);
    } else {
        echo "<pre>";
        echo "Setup was unable to include neccessary files. The file $filename was not found. Solutions:\n\n";
        echo "- Make sure that all files are correctly uploaded to the server.\n";
        echo "- Make sure that include_path is set to '.' (of course, it can contain also other directories). Your include path is: " . ini_get("include_path") . "\n";
        echo "</pre>";
    }
}

// Some basic configuration
global $cfg;

$cfg['path']['frontend'] = CON_FRONTEND_PATH;
$cfg['path']['conlite'] = $cfg['path']['frontend'] . '/conlite/';
$cfg['path']['conlite_config'] = CON_FRONTEND_PATH . '/data/config/' . CL_ENVIRONMENT . '/';
$cfg['path']['conlite_logs'] = CON_FRONTEND_PATH . '/data/logs/';

if(!is_dir($cfg['path']['conlite_config'])) {
    die("Setup cannot find the config folder \"".$cfg['path']['conlite_config']."\"! Make shure folder exists and is readable.");
}

checkAndInclude($cfg['path']['conlite_config'] . 'config.misc.php');
checkAndInclude($cfg['path']['conlite_config'] . 'cfg_sql.inc.php');

// includes
/** @todo use conlite autoload to load needed classes */
checkAndInclude($cfg['path']['frontend'] . '/pear/HTML/Common2.php');
checkAndInclude($cfg['path']['conlite'] . 'classes/con2con/class.registry.php');
// load all genericdb classes
checkAndInclude($cfg['path']['conlite'] . 'classes/genericdb/class.item.base.abstract.php');
checkAndInclude($cfg['path']['conlite'] . 'classes/genericdb/class.item.cache.php');
checkAndInclude($cfg['path']['conlite'] . 'classes/class.genericdb.php');
checkAndInclude($cfg['path']['conlite'] . 'classes/cHTML5/class.chtml5.common.php');
checkAndInclude($cfg['path']['conlite'] . 'classes/cHTML5/class.chtml.php');
checkAndInclude($cfg['path']['conlite'] . 'classes/class.htmlelements.php');
checkAndInclude($cfg['path']['conlite'] . 'classes/con2con/class.filehandler.php');
checkAndInclude($cfg['path']['conlite'] . 'classes/contenido/class.language.php');
checkAndInclude($cfg['path']['conlite'] . 'classes/class.i18n.php');
checkAndInclude($cfg['path']['conlite'] . 'includes/functions.i18n.php');
checkAndInclude('lib/class.setupcontrols.php');
checkAndInclude('lib/functions.filesystem.php');
checkAndInclude('lib/functions.environment.php');
checkAndInclude('lib/functions.safe_mode.php');
checkAndInclude('lib/functions.mysql.php');
checkAndInclude('lib/functions.phpinfo.php');
checkAndInclude('lib/functions.system.php');
checkAndInclude('lib/functions.libraries.php');
checkAndInclude('lib/functions.sql.php');
checkAndInclude('lib/functions.setup.php');
checkAndInclude('lib/class.template.php');
checkAndInclude('lib/class.setupmask.php');