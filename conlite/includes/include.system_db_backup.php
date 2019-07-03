<?php
/**
 * 
 * @package CL-Core
 * @subpackage DB-Backup
 * @version $Rev$
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright (c) 2012-2013, conlite.org
 * 
 * $Id$
 */
/* @var $sess Contenido_Session */
/* @var $perm Contenido_Perm */
/* @var $auth Contenido_Challenge_Crypt_Auth */
/* @var $notification Contenido_Notification */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

define('CL_BACKUP_COUNT_LINES', 20000);
define('CL_BACKUP_MAX_RELOADS', 600);
define('CL_BACKUP_VERSION', 'CL DB-Backup 1.3.4');
define('CL_BACKUP_PATH', $cfg['path']['conlite_backup']);
define('CL_BACKUP_LOGFILE', $cfg['path']['conlite_logs'] . "db-backup-log.txt");
define('CL_BACKUP_START_IMG', $cfg['path']['contenido_html'] . $cfg['path']['images'] . "db_backup_start.png");
define('CL_BACKUP_START_IMG_OFF', $cfg['path']['contenido_html'] . $cfg['path']['images'] . "db_backup_start_off.png");

$aMessage = array();
$bNoBackup = false;
$bFinalStep = false;
// check backup path
if (!is_dir(CL_BACKUP_PATH) || !is_writable(CL_BACKUP_PATH)) {
    $notification->displayNotification("error", i18n("Backupfolder missing or not writable!"));
    $bNoBackup = true;
}
$backup_action = (isset($_GET['cl_action']) ? $_GET['cl_action'] : '');

// check dbuser rights
/*
echo "<pre>";
$oDb = new DB_ConLite();
//echo $sSql = "SHOW GRANTS FOR '".$cfg['db']['connection']['user']."'@'".$cfg['db']['connection']['host']."';";
echo $sSql = "SHOW GRANTS FOR '" . $cfg['db']['connection']['user'] . "';";
$oDb->query($sSql);
while ($oDb->next_record()) {
    print_r($oDb->toArray());
}
*/

$sTable = "";
$bk_filename = str_replace(array("&cl_action=backupnow", "&"), array("", "&amp;"), $sess->self_url()); // web28 - 2011-07-02 - Security Fix - PHP_SELF
$info_h1 = i18n("Start your Backup!");
$info_h2 = '&nbsp;';
$oImgButton = new cHTMLImage();
if ($bNoBackup) {
    $oImgButton->setSrc(CL_BACKUP_START_IMG_OFF);
    $oImgButton->setAlt(mi18n("Backup not possible!"));
} else {
    $oImgButton->setSrc(CL_BACKUP_START_IMG);
    $oImgButton->setAlt(mi18n("Click to start Backup!"));
}
$sImage = $oImgButton->render();
$oStartButton = new cHTMLLink($sess->self_url() . "&cl_action=backupnow");
$oStartButton->_setContent($sImage);
$button_back = ($bNoBackup) ? $sImage : $oStartButton->toHtml();

//aktiviert die Ausgabepufferung
ob_start();

//Start Session
session_name('dbdump');
if (!isset($_SESSION)) {
    session_start();
}
//unset($_SESSION['dump']);
//#### BACKUP ANFANG #######
if (isset($_SESSION['dump'])) {
    $dump = $_SESSION['dump'];
    $info_h2 = '&nbsp;';
    $button_back = '&nbsp;';
} else {
    $oUser = new User();
    $oUser->loadUserByUserID($auth->auth['uid']);
    $bHasPermission = ($oUser->getUserProperty("system", "cl_backup_allowed") == 'true') ? true : false;
    if (!$perm->isSysadmin() && !$bHasPermission) {
        $notification->displayNotification("error", i18n("Permission denied!"));
        exit();
    }
}

cInclude("classes", "class.cl_db_backup.php");

if ($backup_action == 'backupnow') {
    $oDB = new DB_ConLite();
    $oDB2 = new DB_ConLite();

    $info_h1 = i18n("Starting Backup.");
    $info_h2 = '&nbsp;';

    $restore = array();
    unset($_SESSION['restore']);
    $dump = array();
    unset($_SESSION['dump']);


    @set_time_limit(0);

    //BOF Disable "STRICT" mode!
    $vers = $oDB->getClientInfo();
    if (substr($vers, 0, 1) > 4) {
        $oDB->query("SET SESSION sql_mode=''");
        //@mysql_query("SET SESSION sql_mode=''", $oDB->Link_ID);
    }
    //EOF Disable "STRICT" mode!

    $mysql_version = '-- MySQL-Client-Version: ' . $oDB->getClientInfo() . "\n--\n";

    $schema = '-- ConLite ' . $cfg['version'] . "\n" .
            '--' . "\n" .
            '-- ' . CL_BACKUP_VERSION . ' (c) 2012-' . date('Y') . ' php-backoffice.de' . "\n" .
            '--' . "\n" .
            '-- Database: ' . $cfg['db']['connection']['database'] . "\n" .
            '-- Database Server: ' . $oDB->getServerInfo() . "\n" .
            '--' . "\n" . $mysql_version .
            '-- Backup Date: ' . date("r") . "\n\n";
    $backup_file = 'dbd_' . $cfg['db']['connection']['database'] . '-' . date('YmdHis');
    $dump['file'] = CL_BACKUP_PATH . $backup_file;

    if (!isset($dump['compress'])) {
        if (getEffectiveSetting("cl-backup", "use_gzip", false) == 'true') {
            $dump['compress'] = true;
            $dump['file'] .= '.sql.gz';
        } else {
            $dump['compress'] = false;
            $dump['file'] .= '.sql';
        }
    }

    if (!isset($dump['complete_inserts'])) {
        if (getEffectiveSetting("cl-backup", "complete_inserts", false) == 'true') {
            $dump['complete_inserts'] = 'yes';
        }
    }

    $oDB->query("SHOW TABLE STATUS FROM `" . $cfg['db']['connection']['database'] . "` LIKE '" . $cfg['sql']['sqlprefix'] . "_%'");
    $dump['num_tables'] = $oDB->num_rows();

    //Tabellennamen in Array einlesen
    $dump['tables'] = Array();
    $dump['num_rows'] = -1;
    //echo "<pre>";
    if ($dump['num_tables'] > 0) {
        $dump['num_rows'] = 0;
        for ($i = 0; $i < $dump['num_tables']; $i++) {
            $oDB->next_record();
            $row = $oDB->toArray(FETCH_ASSOC);
            $dump['tables'][$i] = $row['Name'];
            $oDB2->query("SELECT COUNT(*) FROM " . $row['Name']);
            $oDB2->next_record();
            //$result = mysql_query("SELECT COUNT(*) FROM ".$row['Name'], $oDB->Link_ID);
            $aTmp = $oDB2->toArray(FETCH_ASSOC);
            $dump['num_rows'] += $aTmp[0];
            //print_r($row);
        }
        $dump['nr'] = 0;

        $dump['table_offset'] = 0;
        $_SESSION['dump'] = $dump;
        //echo '<pre>';
        //print_r($dump);
        /*
         * Statuszeile kompatibel zu MySQLDumper (MSD)
         * AUFBAU der Statuszeile:
         *  -- Status:tabellenzahl:datensätze:Multipart:Datenbankname:script:scriptversion:Kommentar:MySQLVersion:Backupflags:SQLBefore:SQLAfter:Charset:EXTINFO
         *  Aufbau Backupflags (1 Zeichen pro Flag, 0 oder 1, 2=unbekannt)
         *  (complete inserts)(extended inserts)(ignore inserts)(delayed inserts)(downgrade)(lock tables)(optimize tables)
         */
        $statusline = array();
        $statusline['tables'] = $dump['num_tables'];
        $statusline['records'] = $dump['num_rows'];
        $statusline['part'] = 'MP_0';
        $statusline['dbname'] = $cfg['db']['connection']['database'];
        $statusline['script'] = 'php';
        $statusline['scriptversion'] = '1.24';
        $statusline['comment'] = 'Backup made by ' . CL_BACKUP_VERSION;
        $statusline['mysqlversion'] = $oDB->getServerInfo();
        $statusline['flags'] = '2222222';
        $statusline['sqlbefore'] = '';
        $statusline['sqlafter'] = '';
        $statusline['charset'] = $oDB->getClientEncoding();
        $statusline['extinfo'] = 'EXTINFO';

        $sStatLine = "-- Status:";
        $sStatLine .= implode(":", $statusline) . "\n\n";
        ;
        $schema = $sStatLine . $schema;
        //die($schema);
        $oClBackup = new clDbBackup();
        $oClBackup->_writeToDumpFile($schema);
        $selbstaufruf = '<script language="javascript" type="text/javascript">setTimeout("document.dump.submit()", 3000);</script></div>';
    } else {
        $aMessage['level'] = Contenido_Notification::LEVEL_ERROR;
        $aMessage['text'] = "Error: Cannot read database!";
//else ERROR
    }
}
//Seite neu laden wenn noch nicht alle Tabellen ausgelesen sind
if ($dump['num_tables'] > 0 && $backup_action != 'backupnow') {

    if (!is_object($oClBackup)) {
        $oClBackup = new clDbBackup();
    }

    $info_h1 = i18n("Backup in Progress!");

    @set_time_limit(0);

    if ($dump['nr'] < $dump['num_tables']) {
        $nr = $dump['nr'];
        $dump['aufruf'] ++;
        $table_ok = i18n("Tables saved: ") . ($nr + 1) . '<br><br>' . i18n("Last processed: ")
                . $dump['tables'][$nr] . '<br><br>' . i18n("Pageviews: ") . $dump['aufruf'];

        //Neue Tabelle
        if ($dump['table_offset'] == 0) {
            $dump['table_records'] = $oClBackup->_writeTableHeader($dump['tables'][$nr]);
            $dump['anzahl_zeilen'] = CL_BACKUP_COUNT_LINES;
            $dump['table_offset'] = 1;
            $dump['zeilen_offset'] = 0;
        } else {
            //Daten aus  Tabelle lesen
            //GetTableData($dump['tables'][$nr]);
            $oClBackup->_writeTableData($dump['tables'][$nr]);
        }

        $_SESSION['dump'] = $dump;

        $selbstaufruf = '<script type="text/javascript">setTimeout("document.dump.submit()", 10);</script>';
        //Verhindert Endlosschleife - Script wir nach MAX_RELOADS beendet
        if ($dump['aufruf'] > CL_BACKUP_MAX_RELOADS) {
            $selbstaufruf = '';
        }
    } else { //Fertig
        $info_h2 = '&nbsp;';
        $info_h1 = i18n("Backup done!");
        $table_ok = i18n("Tables saved: ") . $dump['nr'] . '<br><br>' . i18n("Pageviews: ") . $dump['aufruf'];
        $selbstaufruf = '';
        unset($_SESSION['dump']);
        $oBackButton = new cHTMLLink($sess->self_url());
        $oBackButton->_setContent(i18n("Go Back to Overview"));
        $button_back = $oBackButton->toHtml();
        $bFinalStep = true;
        if (!is_object($oClBackup)) {
            $oClBackup = new clDbBackup();
        }
        $oClBackup->writeLog();
    }
}
//#### BACKUP ENDE #######
// Let's build the file table
if (!isset($_SESSION['dump']) && !$bFinalStep) {
    if (!is_object($oClBackup)) {
        $oClBackup = new clDbBackup();
    }
    $aFiles = $oClBackup->getDumpFiles(CL_BACKUP_PATH);
    //print_r($aFiles);
    $oFileList = new cScrollList();
    $oFileList->objTable->setID("files_tab");
    $oFileList->setHeader(mi18n("#"), mi18n("Date"), mi18n("Filename"), mi18n("Filesize"), mi18n("Tables/Entries"), "", mi18n("Actions"));
    $oFileList->objHeaderItem->updateAttributes(array('style' => "width: 20px; padding: 0 4px;white-space:nowrap; border: 1px solid #B3B3B3; border-bottom: 0;"));
    $oFileList->setSortable(1, true);
    $oFileList->setSortable(2, true);
    $iLoop = 0;
    if (is_array($aFiles) && count($aFiles) > 0) {
        foreach ($aFiles as $sKey => $aFile) {
            // send file on request
            if ($cl_action == 'download_file' && $fileid == $sKey) {
                $oClBackup->sendFile(CL_BACKUP_PATH . $aFile['filename']);
                die();
            }

            if ($cl_action == 'del_file' && $fileid == $sKey) {
                if (unlink(CL_BACKUP_PATH . $aFile['filename'])) {
                    $notification->displayNotification('info', i18n("File successfully deleted."));
                    continue;
                }
            }

            $oDelImg = new cHTMLImage();
            $oDelImg->setSrc($cfg['path']['contenido_html'] . $cfg['path']['images'] . "but_delete.gif");
            $oDelImg->setClass("img_but");
            $oDelImg->setAlt(i18n("Delete File"));
            $oDelete = new cHTMLLink($sess->url($sess->self_url() . "&cl_action=del_file&fileid=$sKey"), "delete");
            $oDelete->setContent($oDelImg->render());

            $oDownloadImg = new cHTMLImage();
            $oDownloadImg->setSrc($cfg['path']['contenido_html'] . $cfg['path']['images'] . "but_downloadlist.gif");
            $oDownloadImg->setClass("img_but");
            $oDownloadImg->setAlt(i18n("Download File"));
            $oDownload = new cHTMLLink($sess->url($sess->self_url() . "&cl_action=download_file&fileid=$sKey"), "download");
            $oDownload->setContent($oDownloadImg->render());

            $aFileStats = readStatusLineFromFile(CL_BACKUP_PATH . $aFile['filename']);
            if (is_array($aFileStats)) {
                $sFileStats = $aFileStats['tables'] . " / " . $aFileStats['records'];
            } else {
                $sFileStats = '';
            }

            $oFileList->setData($iLoop, $iLoop + 1, date("Y-m-d H:i", $aFile['ctime']), $aFile['filename'], human_readable_size($aFile['size']), $sFileStats, " ", $oDelete->render() . $oDownload->render());
            $iLoop++;
        }
    }

    $sFileList = $oFileList->render(true);
}

function readStatusLineFromFile($sFile) {
    $FileName = basename($sFile);
    if (strtolower(substr($FileName, -2)) == 'gz') {
        $fp = gzopen($sFile, "r");
        if ($fp === false)
            die('Can\'t open file ' . $FileName);
        $sline = gzgets($fp, 40960);
        gzclose($fp);
    } else {
        $fp = fopen($sFile, "r");
        if ($fp === false)
            die('Can\'t open file ' . $FileName);
        $sline = fgets($fp, 5000);
        fclose($fp);
    }
    $aStatusLine = clStatusLine2Array($sline);
    return $aStatusLine;
}

function clStatusLine2Array($sLine) {

    if (substr($sLine, 0, 9) != "-- Status")
        return false;

    $aStatus = explode(":", $sLine);

    $aRet = array();
    $aRet['tables'] = $aStatus[1];
    $aRet['records'] = $aStatus[2];
    $aRet['dbname'] = $aStatus[4];
    if ((isset($aStatus[12])) && trim($aStatus[12]) != 'EXTINFO') {
        $aRet['charset'] = $aStatus[12];
    } else {
        $aRet['charset'] = '?';
    }
    return $aRet;
}
?>
<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title><?php echo CL_BACKUP_VERSION; ?></title>
        <style type="text/css">
            body {position: relative; padding-bottom: 3em;}
            h2, p {margin: 0;}
            #wrapper {margin: 0 auto;}
            #content {text-align: center;}
            #dumpfile_table {margin: 30px 0;}
            table#files_tab {margin: 0 auto; border-collapse: collapse;}
            #footer {position: fixed; bottom: 0.6em; right: 0.6em; color: #666; font-weight: normal; font-size: 0.8em;}
            .img_but {margin-right: 4px;}
        </style>
    </head>
    <body>
        <?php
        echo '<form name="dump" action="' . $bk_filename . '" method="POST"></form>';
        ?>
        <div id="wrapper">
            <div id="header">
                <?php
                if (!empty($aMessage)) {
                    $oNoti = new Contenido_Notification();
                    $oNoti->displayNotification($aMessage['level'], $aMessage['text']);
                }
                ?>
            </div>
            <div id="content">
                <h1><?php echo $info_h1; ?></h1>
                <h2><?php echo $info_h2; ?></h2>
                <p><?php echo $info_p; ?></p>
                <p><b><?php echo $table_ok; ?></b></p>
                <p><?php echo $button_back; ?></p>
            </div>
            <div id="dumpfile_table">
                <?php echo $sFileList ?>
            </div>
            <div id="footer">Database Backup for ConLite [<?php echo CL_BACKUP_VERSION; ?>]</div>
        </div>      
        <!-- body_eof //-->
        <?php
        if ($selbstaufruf != '')
            echo $selbstaufruf;
        ?>
    </body>
</html>
<?php
//Pufferinhalte an den Client ausgeben
ob_end_flush();
?>