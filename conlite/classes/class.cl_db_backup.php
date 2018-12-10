<?php

/**
 * 
 * @package ConLite
 * @subpackage DB-Backup
 * @version $Rev: 374 $
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright (c) 2012, conlite.org
 * 
 * $Id: class.cl_db_backup.php 374 2015-11-09 15:59:28Z oldperl $
 */
/* @var $sess Contenido_Session */
/* @var $perm Contenido_Perm */
/* @var $auth Contenido_Challenge_Crypt_Auth */
/* @var $notification Contenido_Notification */

// security check
defined('CON_FRAMEWORK') or die('Illegal call');

class clDbBackup {

    protected $_oDb;
    protected $_sDumpFile = null;
    protected $_bCompress = false;
    protected $_sTmpData = null;
    protected $_aUsedExt = array('sql', 'gz');
    protected $_aTableContent = array('_code', '_inuse', '_phplib_active_sessions');
    protected $_sLogFile;

    /**
     * Constructor
     */
    public function __construct() {
        $this->_oDb = new DB_ConLite();
        $this->_sLogFile = CL_BACKUP_LOGFILE;
        if (isset($_SESSION['dump']['file']) && !empty($_SESSION['dump']['file'])) {
            $this->_sDumpFile = $_SESSION['dump']['file'];
        }
        if (isset($_SESSION['dump']['compress']) && is_bool($_SESSION['dump']['compress'])) {
            $this->_bCompress = ($_SESSION['dump']['compress']) ? true : false;
        }
    }

    /**
     * Builds and returns an array of dump files for a given dir
     * 
     * @param string $sPath server path to dump files
     * @param string $sOrder sort order by
     * @param string $sDirection sort direction
     * @return array|boolean returns array of files or false
     */
    public function getDumpFiles($sPath, $sOrder = 'date', $sDirection = 'DESC') {
        $oDirHandle = dir($sPath);
        $aFiles = array();
        if (is_object($oDirHandle)) {
            $iLoop = 0;
            while (false !== ($sFile = $oDirHandle->read())) {
                if ($sFile == '..' || $sFile == '.' || $sFile == '.svn')
                    continue;
                $info = new SplFileInfo($sPath . $sFile); // pathinfo($file->getFilename(), PATHINFO_EXTENSION)
                if ($info->isFile()) {
                    $sExtension = '';
                    if (version_compare(phpversion(), "5.3.6", ">=")) {
                        $sExtension = $info->getExtension();
                    } else {
                        $sExtension = pathinfo($info->getFilename(), PATHINFO_EXTENSION);
                    }

                    if (!in_array($sExtension, $this->_aUsedExt))
                        continue;
                    $aFiles[$iLoop]['ext'] = $sExtension;
                    $aFiles[$iLoop]['size'] = $info->getSize();
                    $aFiles[$iLoop]['readable'] = $info->isReadable();
                    $aFiles[$iLoop]['atime'] = $info->getATime();
                    $aFiles[$iLoop]['ctime'] = $info->getCTime();
                    $aFiles[$iLoop]['mtime'] = $info->getMTime();
                }
                $aFiles[$iLoop]['filename'] = $sFile;
                $iLoop++;
            }
            $oDirHandle->close();
        }

        if (count($aFiles) > 0) {
            // Hole eine Liste von Spalten
            foreach ($aFiles as $key => $row) {
                $time[$key] = $row['ctime'];
            }
            array_multisort($time, (($sDirection == 'DESC') ? SORT_DESC : SORT_ASC), $aFiles);
        }
        return (count($aFiles) > 0) ? $aFiles : false;
    }

    /**
     * Write data to dump file
     * 
     * @todo make this func protected and give a public wrapper
     * @param string $sData
     * @return boolean
     */
    public function _writeToDumpFile($sData = null) {
        if (!is_null($sData))
            $this->_sTmpData = $sData;
        if (!is_null($this->_sDumpFile)) {
            if (!is_null($this->_sTmpData) && !empty($this->_sTmpData)) {
                if ($this->_bCompress) {
                    $fp = gzopen($this->_sDumpFile, 'ab');
                    gzwrite($fp, $this->_sTmpData, strlen($this->_sTmpData));
                    gzclose($fp);
                } else {
                    $fp = fopen($this->_sDumpFile, 'ab');
                    fwrite($fp, $this->_sTmpData);
                    fclose($fp);
                }
                $this->_sTmpData = null;
                return true;
            }
        }
        $this->_sTmpData = null;
        return false;
    }

    /**
     * Write table header to dump and return dataset count
     * 
     * @param string $sTable
     * @return array
     */
    public function _writeTableHeader($sTable) {
        $data = "DROP TABLE IF EXISTS `$sTable`;\n";
        $this->_oDb->query('SHOW CREATE TABLE `' . $sTable . '`');
        $this->_oDb->next_record();
        $row = $this->_oDb->toArray();
        $data .= $row[1] . ';' . "\n\n";
        if (!$this->_noTableData($sTable)) {
            $data .= "/*!40000 ALTER TABLE `$sTable` DISABLE KEYS */;\n";
        }

        $this->_writeToDumpFile($data);

        $sql = "SELECT count(*) as `count_records` FROM `" . $sTable . "`";
        $this->_oDb->query($sql);
        $this->_oDb->next_record();
        $res_array = $this->_oDb->toArray();

        return $res_array['count_records'];
    }

    /**
     * Write table data to dump file
     * 
     * @global array $dump
     * @param string $sTable
     * @return void
     */
    public function _writeTableData($sTable) {
        global $dump;
        if ($this->_noTableData($sTable)) {
            $dump['nr'] ++;
            $dump['table_offset'] = 0;
            return;
        }
        $table_list = array();
        $this->_oDb->query("SHOW COLUMNS FROM " . $sTable);
        while($this->_oDb->next_record()) {
            $aResult = $this->_oDb->toArray();
            $table_list[] = $aResult['Field'];
        }

        $this->_oDb->query('select `' . implode('`,`', $table_list) . '` from ' . $sTable . ' limit ' . $dump['zeilen_offset'] . ',' . ($dump['anzahl_zeilen']));
        $ergebnisse = $this->_oDb->num_rows();

        $data = '';

        if ($ergebnisse !== false) {
            if (($ergebnisse + $dump['zeilen_offset']) < $dump['table_records']) {
                //noch nicht fertig - neuen Startwert festlegen
                $dump['zeilen_offset']+= $dump['anzahl_zeilen'];
            } else {
                //Fertig - naechste Tabelle
                $dump['nr'] ++;
                $dump['table_offset'] = 0;
            }

            //BOF Complete Inserts ja/nein
            if ($_SESSION['dump']['complete_inserts'] == 'yes') {

                while ($this->_oDb->next_record()) {
                    $rows = $this->_oDb->toArray();
                    $insert = 'INSERT INTO `' . $sTable . '` (`' . implode('`, `', $table_list) . '`) VALUES (';
                    foreach ($table_list as $column) {
                        //EOF NEW TABLE  STRUCTURE  - LIKE MYSQLDUMPER -functions_dump.php line 186
                        if (!isset($rows[$column])) {
                            $insert.='NULL,';
                        } else if ($rows[$column] != '') {
                            $insert.='\'' . $this->_oDb->escape($rows[$column]) . '\',';
                        } else {
                            $insert.='\'\',';
                        }
                        //BOF NEW TABLE  STRUCTURE  - LIKE MYSQLDUMPER
                    }
                    $data .=substr($insert, 0, -1) . ');' . "\n";
                }
            } else {

                $lines = array();
                while ($this->_oDb->next_record()) {
                    $rows = $this->_oDb->toArray();
                    $values = array();
                    foreach ($table_list as $column) {
                        //EOF NEW TABLE  STRUCTURE  - LIKE MYSQLDUMPER
                        if (!isset($rows[$column])) {
                            $values[] = 'NULL';
                        } else if ($rows[$column] != '') {
                            $values[] = '\'' . $this->_oDb->escape($rows[$column]) . '\'';
                        } else {
                            $values[] = '\'\'';
                        }
                        //BOF NEW TABLE  STRUCTURE  - LIKE MYSQLDUMPER
                    }
                    $lines[] = implode(', ', $values);
                }
                $tmp = trim(implode("),\n (", $lines));
                if ($tmp != '') {
                    $data = 'INSERT INTO `' . $sTable . '` (`' . implode('`, `', $table_list) . '`) VALUES' . "\n" . ' (' . $tmp . ");\n";
                }
            }
            //EOF Complete Inserts ja/nein
            if ($dump['table_offset'] == 0)
                $data.= "/*!40000 ALTER TABLE `$sTable` ENABLE KEYS */;\n\n";

            $this->_writeToDumpFile($data);
        }
    }

    /**
     * send a file to browser (download) and exit
     * 
     * @param string $sFileName
     */
    public function sendFile($sFileName) {
        if (is_file($sFileName)) {
            $path_parts = pathinfo($sFileName);
            $file_name = $path_parts['basename'];
            $file_ext = $path_parts['extension'];
            $file_size = filesize($sFileName);
            $rTmpFile = @fopen($sFileName, "rb");
            if ($rTmpFile) {
                set_time_limit(0);
                ob_end_clean();
                // set the headers, prevent caching
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: private", false); // required for certain browsers 
                header("Cache-Control: public, must-revalidate, post-check=0, pre-check=0");
                header("Content-Disposition: attachment; filename=\"$file_name\"");

                // set the mime type based on extension, add yours if needed.
                $ctype_default = "application/octet-stream";
                $content_types = array(
                    "sql" => "application/x-sql",
                    "gz" => "application/x-gzip"
                );
                $ctype = isset($content_types[$file_ext]) ? $content_types[$file_ext] : $ctype_default;
                header("Content-Type: $ctype");
                header("Content-Transfer-Encoding: binary");
                header("Content-Length: $file_size");

                while (!feof($rTmpFile)) {
                    print(@fread($rTmpFile, 1024 * 8));
                    ob_flush();
                    flush();
                    if (connection_status() != 0) {
                        @fclose($rTmpFile);
                        exit;
                    }
                }
                // file save was a success
                @fclose($rTmpFile);
                $this->_writeLogEntry("Downloaded File: " . basename($sFileName));
                exit;
            } else {
                // file couldn't be opened
                header("HTTP/1.0 500 Internal Server Error");
                exit;
            }
        } else {
            // file does not exist
            header("HTTP/1.0 404 Not Found");
            exit;
        }
    }

    /**
     * Write a log entry
     */
    public function writeLog() {
        $this->_writeLogEntry("Backup generated: " . $this->_sDumpFile);
    }

    /**
     * Check against table exclude array for writing table data
     * 
     * @param string $sTable
     * @return boolean
     */
    protected function _noTableData($sTable) {
        foreach ($this->_aTableContent as $sExTable) {
            if (strstr($sTable, $sExTable))
                return true;
        }
        return false;
    }

    /**
     * Write to log file
     * 
     * @global Contenido_Challenge_Crypt_Auth $auth
     * @param string $sMessage
     */
    protected function _writeLogEntry($sMessage) {
        /* @var $auth Contenido_Challenge_Crypt_Auth */
        global $auth;
        if (!empty($auth->auth['uname'])) {
            $sMessage = "User " . $auth->auth['uname'] . " - " . $sMessage;
        }
        $f = @fopen($this->_sLogFile, 'a+');
        if (is_resource($f)) {
            @fputs($f, date("m.d.Y g:ia") . "  " . $_SERVER['REMOTE_ADDR'] . "  " . $sMessage . "\n");
            @fclose($f);
        }
    }

}

?>