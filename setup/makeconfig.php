<?php
 /**
 * Project:
 * Contenido Content Management System
 *
 * Description:
 * Generates the configuration file and saves it into contenido folder or
 * outputs the for download (depending on selected option during setup)
 *
 * Requirements:
 * @con_php_req 5
 *
 * @package    Contenido setup
 * @version    0.2.1
 * @author     unknown
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * 
 *
 * {@internal
 *   created  unknown
 *   modified 2008-07-07, bilal arslan, added security fix
 *   modified 2011-02-28, Murat Purc, normalized setup startup process and some cleanup/formatting
 *
 *   $Id$:
 * }}
 *
 */

if (!defined('CON_FRAMEWORK')) {
    define('CON_FRAMEWORK', true);
}

define('CON_SETUP_PATH', str_replace('\\', '/', realpath(__DIR__)));

define('CON_FRONTEND_PATH', str_replace('\\', '/', realpath(__DIR__ . '/../')));

include_once('lib/startup.php');

[$root_path, $root_http_path] = getSystemDirectories();

$tpl = new Template();
$tpl->set('s', 'CONTENIDO_ROOT', $root_path);
$tpl->set('s', 'CONTENIDO_WEB', $root_http_path);
$tpl->set('s', 'MYSQL_HOST', $_SESSION['dbhost']);
$tpl->set('s', 'MYSQL_DB', $_SESSION['dbname']);
$tpl->set('s', 'MYSQL_USER', $_SESSION['dbuser']);
$tpl->set('s', 'MYSQL_PASS', $_SESSION['dbpass']);
$tpl->set('s', 'MYSQL_PREFIX', $_SESSION['dbprefix']);

if (hasMySQLiExtension() && !hasMySQLExtension()) {
    $tpl->set('s', 'DB_EXTENSION', 'mysqli');
} else {
    $tpl->set('s', 'DB_EXTENSION', 'mysql');
}

if ($_SESSION['start_compatible'] == true) {
    $tpl->set('s', 'START_COMPATIBLE', 'true');
} else {
    $tpl->set('s', 'START_COMPATIBLE', 'false');
}

$tpl->set('s', 'NOLOCK', $_SESSION['nolock']);

// Set CON_UTF8 constant only for new installations
if ($_SESSION['setuptype'] == 'setup') {
	$tpl->set('s', 'CON_UTF8', 'define("CON_UTF8", true);');
} else {
	$tpl->set('s', 'CON_UTF8', '');
}

$tpl->set('s','MYSQL_CHARSET', '');

if ($_SESSION['configmode'] == 'save') {
    $sCfgFileOld = $root_path . '/conlite/includes/config.php';
    $sCfgFileNew = $root_path . '/data/config/'.CL_ENVIRONMENT.'/config.php';
    if(file_exists($sCfgFileOld)) {
        unlink($sCfgFileOld);
    }
    if(file_exists($sCfgFileNew)) {
        unlink($sCfgFileNew);
    }

    $handle = fopen($sCfgFileNew, 'wb');
    fwrite($handle, $tpl->generate('templates/config.php.tpl', true, false));
    fclose($handle);

    if (!file_exists($sCfgFileNew)) {
        $_SESSION['configsavefailed'] = true;
    } else {
        unset($_SESSION['configsavefailed']);
    }
} else {
    header('Content-Type: application/octet-stream');
    header('Etag: ' . md5(random_int(0, mt_getrandmax())));
    header('Content-Disposition: attachment;filename=config.php');
    $tpl->generate('templates/config.php.tpl', false, false);
}

?>