<?php
/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 *  Cron Job to move old statistics into the stat_archive table
 * 
 * @package    Backend
 * @subpackage Cronjobs
 * @version    $Rev$
 * @author     Timo A. Hummel
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 *
 *   $Id$:
 */

if (!defined("CON_FRAMEWORK")) {
    define("CON_FRAMEWORK", true);
}

// Contenido startup process
include_once ('../includes/startup.php');

$classPath = cRegistry::getConfigValue('path', 'conlite') . cRegistry::getConfigValue('path', 'classes');
$includesPath = cRegistry::getConfigValue('path', 'conlite') . cRegistry::getConfigValue('path', 'includes');

include_once ($classPath . 'class.user.php');
include_once ($classPath . 'class.xml.php');
include_once ($classPath . 'class.navigation.php');
include_once ($classPath . 'template/class.template.php');
include_once ($classPath . 'class.backend.php');
include_once ($classPath . 'class.table.php');
include_once ($classPath . 'class.notification.php');
include_once ($classPath . 'class.area.php');
include_once ($classPath . 'class.layout.php');
include_once ($classPath . 'class.client.php');
include_once ($classPath . 'class.cat.php');
include_once ($classPath . 'class.treeitem.php');
include_once ($includesPath . 'cfg_language_de.inc.php');
include_once ($includesPath . 'functions.stat.php');

global $cfg;

if(!isRunningFromWeb() || function_exists("runJob") || $area == "cronjobs") {
    $db = new DB_ConLite;

	$tables = cRegistry::getConfigValue('tab');

    foreach ($tables as $key => $value)
    {
    	$sql = "OPTIMIZE TABLE ".$value;
    	$db->query($sql);

    }

	if (cRegistry::getConfigValue('statistics_heap_table')) {
		$sHeapTable = cRegistry::getConfigValue('tab', 'stat_heap_table');

		buildHeapTable ($sHeapTable, $db);
	}
}