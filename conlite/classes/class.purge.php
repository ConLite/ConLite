<?php
/**
 * Description: 
 * Contenido Purge class to reset some datas and files.
 *
 * @package    Contenido Backend classes
 * @version    1.0.0
 * @author     Munkh-Ulzii Balidar
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.8.12
 *
 * $Id: 
 */

if(!defined('CON_FRAMEWORK')) {
	die('Illegal call');
}

/**
 * class Purge
 *
 */

class Purge {

	/**
	* @var DB_ConLite $oDb
	*/
	private $oDb;
	
	/**
	* @var $cfg
	*/
	private $cfg;
	
	/**
	* @var $cfgClient
	*/
	private $cfgClient;
	
	/**
	* @var string $sDefaultCacheDir
	*/
	private $sDefaultCacheDir = 'cache/';
	
	
	/**
	* @var string $sDefaultLogDir
	*/
	private $sDefaultLogDir = 'logs/';
	
	/**
	* @var string $sDefaultVersionDir
	*/
	private $sDefaultVersionDir = 'version/';
	
	/**
	* @var string $sDefaultCronjobDir
	*/
	private $sDefaultCronjobDir = 'cronjobs/';
	
	/**
	 * @var array $aLogFileTypes
	 */
	private $aLogFileTypes;
	
	/**
	 * @var array $aCronjobFileTypes
	 */
	private $aCronjobFileTypes;
	
	/**
	 * Constructor of class
	 * 
	 * @param object $db
	 * @param array $cfg
	 * @param array $cfgClient
	 */
	public function __construct(&$db, $cfg, $cfgClient) {
		$this->oDb = $db;
		$this->cfg = $cfg;
		$this->cfgClient = $cfgClient;
		
		$this->setLogFileTypes(array('txt'));
		$this->setCronjobFileTypes(array('job'));
	}
	
	/**
	 * Reset the table con_code for a client
	 * 
	 * @param int $iClientId
	 * @return boolean
	 */
	public function resetClientConCode($iClientId) {
		$sSql = "DELETE FROM " . $this->cfg['tab']['code'] . 
				   " WHERE idclient = " . $iClientId; 
		
		$this->oDb->query($sSql);
		
		return ($this->oDb->Error == '') ? true : false;
	}
	
	/**
	 * Reset the table con_cat_art for a client
	 * 
	 * @param int $iClientId
	 * @return boolean
	 */
	public function resetClientConCatArt ($iClientId) {
		$sSql = " UPDATE " . $this->cfg['tab']['cat_art'] . " cca, " . 
						    $this->cfg['tab']['cat'] . " cc, " . 
						    $this->cfg['tab']['art'] . " ca " . 
			   " SET cca.createcode=1 " . 			    
			   " WHERE cc.idcat = cca.idcat " . 
			   " AND ca.idart = cca.idart " . 
			   " AND cc.idclient = " . $iClientId .  
			   " AND ca.idclient =" . $iClientId; 
		$this->oDb->query($sSql);
		
		return ($this->oDb->Error == '') ? true : false;
	}
	
	/**
	 * Reset the table con_inuse
	 *
	 * @return boolean
	 */
	public function resetConInuse () {
		$sSql = "DELETE FROM " . $this->cfg['tab']['inuse'];
		$this->oDb->query($sSql);
		
		return ($this->oDb->Error == '') ? true : false;
	}
	
	/**
	 * Reset the table con_phplib_active_sessions
	 *
	 * @return boolean
	 */
	public function resetPHPLibActiveSession () {
		$sSql = "DELETE FROM " . $this->cfg['tab']['phplib_active_sessions'];
		$this->oDb->query($sSql);
		return ($this->oDb->Error == '') ? true : false;
	}
	
	/**
	 * Reset the table con_inuse
	 *
	 * @return boolean
	 */
	public function resetUnusedSession () {
		$sSql = "DELETE FROM " . $this->cfg['tab']['inuse'];
		$this->oDb->query($sSql);
		
		return ($this->oDb->Error == '') ? true : false;
	}
	
	/**
	 * Clear the cache directory for a client
	 * 
	 * @param int $sClientName
	 * @return boolean
	 */
	public function clearClientCache ($iClientId, $sCacheDir = 'cache/') {
		$sClientDir = $this->getClientDir($iClientId);
		
		$sCacheDir = (trim($sCacheDir) == '' || trim($sCacheDir) == '/') ? $this->sDefaultCacheDir : $sCacheDir;
		
		if (is_dir($sClientDir . $sCacheDir)) {
			$sCachePath = $sClientDir . $sCacheDir;
			return ($this->clearDir($sCachePath, $sCachePath) ? true : false);	
		} 
		
		return false;
	} 
	
	/**
	 * Clear the cache directory for a client
	 * 
	 * @param int $sClientName
	 * @return boolean
	 */
	public function clearClientHistory ($iClientId, $bKeep, $iFileNumber, $sVersionDir = 'version/') {
		$sClientDir = $this->getClientDir($iClientId);
		
		$sCacheDir = (trim($sVersionDir) == '' || trim($sVersionDir) == '/') ? $this->sDefaultVersionDir : $sVersionDir;
		
		if (is_dir($sClientDir . $sVersionDir)) {
			$sVersionPath = $sClientDir . $sVersionDir;
			$aTmpFile = array();
			$this->clearDir($sVersionPath, $sVersionPath, $bKeep, $aTmpFile);
		    if (count($aTmpFile) > 0 )
		    foreach ($aTmpFile as $sKey => $aFiles) {
		    	// sort the history files with filename
		    	array_multisort($aTmpFile[$sKey]);

		    	$iCount = count($aTmpFile[$sKey]);
		    	// find the total number to delete
		    	$iCountDelete = ($iCount <= $iFileNumber) ? 0 : ($iCount - $iFileNumber); 
		    	// delete the files
		    	for ($i = 0; $i < $iCountDelete; $i++) {
		    		if (file_exists($aTmpFile[$sKey][$i]) && is_writable($aTmpFile[$sKey][$i])) {
		    			unlink($aTmpFile[$sKey][$i]);
		    		} 
		    	}
		    }
		    
		    return true;
		} 
		
		return false;
	} 
	
	/**
	 * Clear client log file
	 * 
	 * @param int $iClientId
	 * @param string $sLogDir
	 * @return boolean
	 */
	public function clearClientLog ($iClientId, $sLogDir = 'logs/') {
		$sClientDir = $this->getClientDir($iClientId);
		
		$sLogDir = (trim($sLogDir) == '' || trim($sLogDir) == '/') ? $this->sDefaultLogDir : $sLogDir;

		if (is_dir($sClientDir . $sLogDir)) { 
			return $this->emptyFile($sClientDir . $sLogDir, $this->aLogFileTypes);
		} 
		
		return false;
	}
	
	/**
	 * Clear contenido log files
	 * 
	 * @param string $sLogDir
	 * @return boolean
	 */
	public function clearConLog ($sLogDir = 'logs/') {
		$sLogDir = (trim($sLogDir) == '' || trim($sLogDir) == '/') ? $this->sDefaultLogDir : $sLogDir;
		
		if (is_dir($sLogDir)) {
			return $this->emptyFile($sLogDir, $this->aLogFileTypes);
		} 
		
		return false;
	}
	
	/**
	 * Clear contenido log files
	 * 
	 * @param string $sLogDir
	 * @return boolean
	 */
	public function clearConCronjob ($sCronjobDir = 'cronjobs/') {
		$sCronjobDir = (trim($sCronjobDir) == '' || trim($sCronjobDir) == '/') ? $this->sDefaultCronjobDir : $sCronjobDir;
		
		if (is_dir($sCronjobDir)) { 
			return $this->emptyFile($sCronjobDir, $this->aCronjobFileTypes);
		} 
		
		return false;
	}
	
	/**
	 * Clear the cache directory for a client
	 * 
	 * @param int $sClientName
	 * @return boolean
	 */
	public function clearConCache ($sCacheDir = 'cache/') {
		
		$sCacheDir = (trim($sCacheDir) == '' || trim($sCacheDir) == '/') ? $this->sDefaultCacheDir : $sCacheDir;
		
		if (is_dir($sCacheDir)) {
			return ($this->clearDir($sCacheDir, $sCacheDir) ? true : false);	
		} 
		
		return false;
	} 
	
	/**
	 * Delete all files and sub directories in a directory
	 * 
	 * @param string $sDirPath
	 * @param string $sTmpDirPath - root directory not deleted
	 * @param boolean $bKeep
	 * @param array $aTmpFileList - files are temporarily saved
	 * @return boolean 
	 */
	public function clearDir ($sDirPath, $sTmpDirPath, $bKeep = false, &$aTmpFileList = array()) {
		if (is_dir($sDirPath) && ($handle = opendir($sDirPath))) {
			$sTmp = str_replace(array('/', '..'), '', $sDirPath);
		    while (false !== ($file = readdir($handle))) {
		        if ($file != "." && $file != "..") {
					$sFilePath = $sDirPath . '/' . $file;	
					$sFilePath = str_replace('//', '/', $sFilePath);
		            if (is_dir($sFilePath)) {
		            	$this->clearDir($sFilePath, $sTmpDirPath, $bKeep, $aTmpFileList);
		            } else {
		            	if ($bKeep === false ) {
		            		unlink($sFilePath);	
		            	} else {
		            		$aTmpFileList[$sTmp][] = $sFilePath;
		            	}
		            	
		            } 
		        }
		    }
		    if (str_replace(array('/', '..'), '', $sDirPath) !=  str_replace(array('/', '..'), '', $sTmpDirPath) && $bKeep === false) 
		    	rmdir($sDirPath);
	        
	        closedir($handle);
	        
	        return true;
		} else {
			return false;
		}	
	}
	
	/**
	 * Empty a file content
	 * 
	 * @param string $sFilePath
	 * @return boolean
	 */
	public function emptyFile($sDirPath, $aTypes) {
		$iCount = 0;
		$iCountCleared = 0;
		if (is_dir($sDirPath) && ($handle = opendir($sDirPath))) {
		    while (false !== ($file = readdir($handle))) {
		    	$sFileExt = trim(end(explode('.', $file)));

		    	if ($file != "." && $file != ".." &&  in_array($sFileExt, $aTypes)) {
					$sFilePath = $sDirPath . '/' . $file;	
					    	
		        	if (file_exists($sFilePath) && is_writable($sFilePath)) {
		        		$iCount++;
		        		
		        		//chmod($sFilePath, 0777);
						if (fclose(fopen($sFilePath, 'w+'))) 
							$iCountCleared++;	  	
					} 
		        }
		    }
		    
		    // true if all files are cleaned 
		    return ($iCount == $iCountCleared) ? true : false;
		}   
	 	
		return false;
	}
	
	/**
	 * Get frontend directory name for a client
	 * 
	 * @param int $iClientId
	 * @return string $sClientDir
	 */
	public function getClientDir($iClientId) {
		$sClientDir = str_replace($this->cfg['path']['frontend'], '..', $this->cfgClient[$iClientId]['path']['frontend']);
		
		return $sClientDir;
	}
	
	/**
	 * Set log file types 
	 * 
	 * @param array $aTypes
	 */
	public function setLogFileTypes( $aTypes) {
		if (count($aTypes) > 0) {
			foreach($aTypes as $sType) {
				$this->aLogFileTypes[] = $sType;
			}
		}
	}
	
	/**
	 * Set cronjob file types 
	 * 
	 * @param array $aTypes
	 */
	public function setCronjobFileTypes($aTypes) {
		if (count($aTypes) > 0) {
			foreach($aTypes as $sType) {
				$this->aCronjobFileTypes[] = $sType;
			}
		}
	}

    /**
     * updates con_sequence for all tables in db
     * which are know in CL
     * 
     * @author Ortwin Pinke
     * @since 4.8.17 CL
     * 
     * @return boolean 
     */
    public function updateConSequence() {

        $aDbTables = array();
        $sSql = 'SHOW TABLES';
        $this->oDb->query($sSql);

        while($this->oDb->next_record()) {
            $aTmp = $this->oDb->toArray();
            $aDbTables[] = $aTmp[0];
        }    

        $iLoop = 0;
        //only use tables which are listet in tab-cfg
        foreach ($this->cfg['tab'] as $sTable) {
            if(in_array($sTable, $aDbTables)) {
                dbUpdateSequence($this->cfg['tab']['sequence'], $sTable, $this->oDb);
                if($this->oDb->Errno > 0) {
                    return false;
                }
                
                $iLoop++;
            }
        }
        return true;
        // not working with tables not listed in tab-cfg
        // return ($iLoop == count($aDbTables))?true:false;
    }
}
?>