<?php
/**
 * Project:
 * Contenido Content Management System
 *
 * Description:
 * Main Contenido setup bootstrap file.
 *
 * Requirements:
 * @con_php_req 5.0
 *
 *
 * @package    Contenido setup bootstrap
 * @version    0.0.1
 * @author     Murat Purc <murat@purc.de>
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.8.15
 *
 *   $Id: startup.php 379 2015-11-09 20:32:17Z oldperl $
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}
define('CON_BE_PATH', '../conlite/');

// uncomment this lines during development if needed
error_reporting(E_ALL ^ E_NOTICE);
ini_set("display_errors", true);
ini_set("log_errors", true);
ini_set("error_log", "../data/logs/setup_errorlog.txt");

header('Content-Type: text/html; charset=UTF-8');

// Check version in the 'first' line, as class.security.php uses
if (version_compare(PHP_VERSION, '5.6.0', '<')) {
    die("You need PHP >= 5.6.0 for ConLite. Sorry, even the setup doesn't work otherwise. Your version: " . PHP_VERSION . "\n");
}

// Check version
//PHP >= 5.6.0 and < 7.3.0
if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
    die("You need PHP >= 5.6.0  < 7.3.0 for ConLite. Sorry, even the setup doesn't work otherwise. Your version: " . PHP_VERSION . "\n");
}


/*
 * Do not edit this value!
 *
 * If you want to set a different enviroment value please define it in your .htaccess file
 * or in the server configuration.
 *
 * SetEnv CONTENIDO_ENVIRONMENT development
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
            echo "- Make sure that include_path is set to '.' (of course, it can contain also other directories). Your include path is: ".ini_get("include_path")."\n"; 
            echo "</pre>";
    }
}

// Some basic configuration
global $cfg;

$cfg['path']['frontend'] = CON_FRONTEND_PATH;
$cfg['path']['conlite'] = $cfg['path']['frontend'] . '/conlite/';
$cfg['path']['conlite_config'] = CON_FRONTEND_PATH . '/data/config/' . CL_ENVIRONMENT . '/';

// (bool) Flag to use native i18n.
//        Note: Enabling this could create unwanted side effects, because of
//        native gettext() behavior.
$cfg['native_i18n'] = false;

session_start();

// includes
checkAndInclude('lib/defines.php');
checkAndInclude($cfg['path']['frontend'].'/pear/HTML/Common2.php');
checkAndInclude($cfg['path']['conlite'] . 'classes/cHTML5/class.chtml.php');
checkAndInclude($cfg['path']['conlite'] . 'classes/class.htmlelements.php');
checkAndInclude($cfg['path']['conlite'] . 'classes/con2con/class.filehandler.php');
checkAndInclude($cfg['path']['conlite'] . 'includes/functions.php54.php');
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
?>
