<?php

/**
 * Project:
 * Contenido Content Management System
 *
 * Description:
 * Creates/Updates the database tables and fills them with entries (depending on 
 * selected options during setup process)
 *
 * @package    Contenido setup
 * @version    0.2.2
 * @author     unknown
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 *
 *   $Id$:
 */
if (!defined('CON_FRAMEWORK')) {
    define('CON_FRAMEWORK', true);
}

define('CON_SETUP_PATH', str_replace('\\', '/', realpath(__DIR__)));

define('CON_FRONTEND_PATH', str_replace('\\', '/', realpath(__DIR__ . '/../')));

include_once('lib/startup.php');


checkAndInclude(CON_BE_PATH . 'includes/functions.database.php');
checkAndInclude(CON_BE_PATH . 'classes/class.version.php');
checkAndInclude(CON_BE_PATH . 'classes/class.versionImport.php');


if (hasMySQLiExtension() && !hasMySQLExtension()) {
    // use MySQLi extension by default if available
    $cfg['database_extension'] = 'mysqli';
} elseif (hasMySQLExtension()) {
    // use MySQL extension if available
    $cfg['database_extension'] = 'mysql';
} else {
    die("Can't detect MySQLi or MySQL extension");
}

checkAndInclude('../conlib/prepend.php');

$db = getSetupMySQLDBConnection(false);

if (checkMySQLDatabaseCreation($db, $_SESSION['dbname'])) {
    $db = getSetupMySQLDBConnection();
}

$currentstep = (empty($_GET['step'])) ? 1 : filter_input(INPUT_GET, "step", FILTER_SANITIZE_NUMBER_INT);

// Count DB Chunks
$file = fopen('data/tables.txt', 'r');
$step = 1;
$count = 1;
$fullcount = 1;

while (($data = fgetcsv($file, 4000, ';')) !== false) {
    if ($count == 50) {
        $count = 1;
        $step++;
    }

    if ($currentstep == $step) {
        if ($data[7] == '1') {
            $drop = true;
        } else {
            $drop = false;
        }
        dbUpgradeTable($db, $_SESSION['dbprefix'] . '_' . $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], '', $drop);

        if ($db->getErrorNumber() != 0) {
            $_SESSION['install_failedupgradetable'] = true;
        }
    }

    $count++;
    $fullcount++;
}

// Count DB Chunks (plugins)
if (cFileHandler::exists('data/tables_pi.txt')) {
    $file = fopen('data/tables_pi.txt', 'r');
    if ($file) {
        $step = 1;
        while (($data = fgetcsv($file, 4000, ';')) !== false) {
            if ($count == 50) {
                $count = 1;
                $step++;
            }

            if ($currentstep == $step) {
                if ($data[7] == '1') {
                    $drop = true;
                } else {
                    $drop = false;
                }
                dbUpgradeTable($db, $_SESSION['dbprefix'] . '_' . $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], '', $drop);

                if ($db->errno != 0) {
                    $_SESSION['install_failedupgradetable'] = true;
                }
            }

            $count++;
            $fullcount++;
        }
    }
}

$pluginChunks = [];

$baseChunks = txtFileToArray('data/base.txt');

$clientChunks = txtFileToArray('data/client.txt');

$clientNoContentChunks = txtFileToArray('data/client_no_content.txt');

$moduleChunks = txtFileToArray('data/standard.txt');

$contentChunks = txtFileToArray('data/examples.txt');

$sysadminChunk = txtFileToArray('data/sysadmin.txt');

if ($_SESSION['setuptype'] == 'setup') {
    $fullChunks = match ($_SESSION['clientmode']) {
        'CLIENT' => array_merge($baseChunks, $sysadminChunk, $clientNoContentChunks),
        'CLIENTMODULES' => array_merge($baseChunks, $sysadminChunk, $clientNoContentChunks, $moduleChunks),
        'CLIENTEXAMPLES' => array_merge($baseChunks, $sysadminChunk, $clientChunks, $moduleChunks, $contentChunks),
        default => array_merge($baseChunks, $sysadminChunk),
    };
} else {
    $fullChunks = $baseChunks;
}

$fullChunks = array_merge($fullChunks, $pluginChunks);


[$root_path, $root_http_path] = getSystemDirectories();

$totalsteps = ceil($fullcount / 50) + count($fullChunks) + 1;
foreach ($fullChunks as $fullChunk) {
    $step++;
    if ($step == $currentstep) {
        $failedChunks = [];

        $replacements = ['<!--{conlite_root}-->' => addslashes($root_path), '<!--{conlite_web}-->' => addslashes($root_http_path)];

        injectSQL($db, $_SESSION['dbprefix'], 'data/' . $fullChunk, $failedChunks, $replacements);

        if ((is_countable($failedChunks) ? count($failedChunks) : 0) > 0) {
            $fp = fopen('../data/logs/setuplog.txt', 'w');
            foreach ($failedChunks as $failedChunk) {
                fwrite($fp, sprintf("Setup was unable to execute SQL. MySQL-Error: %s, MySQL-Message: %s, SQL-Statements:\n%s", $failedChunk['errno'], $failedChunk['error'], $failedChunk['sql']));
            }
            fclose($fp);

            $_SESSION['install_failedchunks'] = true;
        }
    }
}

$percent = intval((100 / $totalsteps) * ($currentstep));
$width = ((700 / 100) * $percent) + 10;

echo '<script type="text/javascript">parent.updateProgressbar(' . $percent . ');</script>';
//echo '<script type="text/javascript">parent.document.getElementById("progressbar").style.width = '.$width.';</script>';
if ($currentstep < $totalsteps) {
    printf('<script type="text/javascript">window.setTimeout("nextStep()", 10); function nextStep () { window.location.href=\'dbupdate.php?step=%s\'; }</script>', $currentstep + 1);
} else {
    $sql = 'SHOW TABLES';
    $db->query($sql);

    // For import mod_history rows to versioning
    if ($_SESSION['setuptype'] == 'migration' || $_SESSION['setuptype'] == 'upgrade') {
        $cfgClient = [];
        rereadClients_Setup();

        $oVersion = new VersionImport($cfg, $cfgClient, $db, $client, $area, $frame);
        $oVersion->CreateHistoryVersion();
    }

    $tables = [];

    while ($db->next_record()) {
        $tables[] = $db->f(0);
    }

    foreach ($tables as $table) {
        dbUpdateSequence($_SESSION['dbprefix'] . '_sequence', $table, $db);
    }

    updateContenidoVersion($db, $_SESSION['dbprefix'] . '_system_prop', C_SETUP_VERSION);
    updateSystemProperties($db, $_SESSION['dbprefix'] . '_system_prop');

    if (isset($_SESSION['sysadminpass']) && $_SESSION['sysadminpass'] != '') {
        updateSysadminPassword($db, $_SESSION['dbprefix'] . '_phplib_auth_user_md5', 'sysadmin');
    }

    $sql = 'DELETE FROM %s';
    $db->query(sprintf($sql, $_SESSION['dbprefix'] . '_code'));

    // As con_code has been emptied, force code creation (on update)
    $sql = "UPDATE %s SET createcode = '1'";
    $db->query(sprintf($sql, $_SESSION['dbprefix'] . '_cat_art'));

    if ($_SESSION['setuptype'] == 'migration') {
        $aClients = listClients($db, $_SESSION['dbprefix'] . '_clients');

        foreach ($aClients as $iIdClient => $aInfo) {
            updateClientPath($db, $_SESSION['dbprefix'] . '_clients', $iIdClient, $_SESSION['frontendpath'][$iIdClient], $_SESSION['htmlpath'][$iIdClient]);
        }
    }

    $_SESSION['start_compatible'] = false;

    if ($_SESSION['setuptype'] == 'upgrade') {
        $sql = "SELECT is_start FROM %s WHERE is_start = 1";
        $db->query(sprintf($sql, $_SESSION['dbprefix'] . '_cat_art'));

        if ($db->next_record()) {
            $_SESSION['start_compatible'] = true;
        }
    }

    // Update Keys
    $aNothing = [];

    injectSQL($db, $_SESSION['dbprefix'], 'data/indexes.sql', $aNothing);

    // logging query stuff
    $aSqlArray = $db->getProfileData();
    if (is_array($aSqlArray) && count($aSqlArray) > 0) {
        $fp = fopen('../data/logs/setup_queries.txt', 'w');
        foreach ($aSqlArray as $failedChunk) {
            fwrite($fp, print_r($aSqlArray, TRUE));
        }
        fclose($fp);
    }

    printf('<script type="text/javascript">parent.document.getElementById("installing").style.visibility="hidden";parent.document.getElementById("installingdone").style.visibility="visible";</script>');
    printf('<script type="text/javascript">parent.document.getElementById("next").style.visibility="visible"; window.setTimeout("nextStep()", 10); function nextStep () { window.location.href=\'makeconfig.php\'; }</script>');
}

function txtFileToArray($sFile) {
    $aFileArray = [];
    if (file_exists($sFile) && is_readable($sFile)) {
        $aFileArray = explode("\n", file_get_contents($sFile));
    }
    return $aFileArray;
}

?>