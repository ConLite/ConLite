<?php
/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * Extractor for plugin archive files
 * 
 * Requirements: 
 * @con_php_req 5.0
 * 
 *
 * @package    Contenido Backend plugins
 * @version    1.0.0
 * @author     Dominik Ziegler
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release 4.8.7
 * 
 * {@internal 
 *   created 2008-06-06
 *   modified 2008-07-03, Frederic Schneider, add security fix
 *
 *   $Id: Contenido_ArchiveExtractor.class.php 7 2015-06-23 11:01:26Z oldperl $:
 * }}
 * 
 */

if(!defined('CON_FRAMEWORK')) {
	die('Illegal call');
}

plugin_include('pluginmanager', 'classes/Exceptions/Contenido_ArchiveExtractor_Exception.php');
//cInclude('pear', 'PHP/Archive.php');
cInclude('pear', 'File/Archive.php');

class Contenido_ArchiveExtractor {

	/**
	* The archive file
	* @var string
	*/
	protected $sSource = "";

	/**
	* The destination path
	* @var string
	*/
	protected $sDestination = "";

	/**
	* List of all files in the archive
	* @var array
	*/
	protected $aFileList = array();

	/**
	* The absolute path
	* @var string
	*/
	protected $sAbsPath = "";

	/**
	* Constructor of ArchiveExtractor, load the file list
	* @access public
	* @param  string $sSource
	* @return void
	*/
	public function __construct($sSource) {
		global $cfg;

		$this->sSource = (string) $sSource;

		if (file_exists($sSource)) {

			$sTrailingSlash = substr($this->sSource, -1);

			if( $sTrailingSlash != DIRECTORY_SEPARATOR) {
				$this->sSource = $this->sSource . DIRECTORY_SEPARATOR;
			}

			// generate absolute path to the plugin manager directory
			$this->sAbsPath = $cfg['path']['contenido'] . $cfg['path']['plugins'] . "pluginmanager" . DIRECTORY_SEPARATOR;

			$this->getFileList();

		} else {
			throw new Contenido_ArchiveExtractor_Exception("Source file does not exists");
		}

	}

	public function setErrorHandling($mode, $options) {
		PEAR::setErrorHandling($mode, $options); // use temporary the error handler of PEAR
	}

	/**
	* Sets the path where the extractor extracts the archive files
	* @access public
	* @param  string $sDestination
	* @return void
	*/
	public function setDestinationPath($sDestination) {

		if (!is_dir($sDestination)) {
			$bMakeDirectory = mkdir($sDestination, 0777);

			if ($bMakeDirectory != true) {
				throw new Contenido_ArchiveExtractor_Exception("Can not set destination path: directoy is not writable");
			} 

			$this->sDestination = (string) $sDestination;

		} else {
			throw new Contenido_ArchiveExtractor_Exception("Destination already exists");
		}

	}

	/**
	* Extracts the whole archive
	* @access public
	* @return void
	*/
	public function extractArchive() {

		if ($this->sDestination != "") {
			File_Archive::extract($this->sSource, $this->sDestination);
		} else {
			throw new Contenido_ArchiveExtractor_Exception("Extraction failed: no destination path setted");
		}

	}

	/**
	* Loads the file list of the archive
	* @access public
	* @return void
	*/
	public function getFileList(){
		$objArchiveReader = File_Archive::read($this->sSource);
		$this->aFileList = $objArchiveReader->getFileList();
	}

	/**
	* Extract only one specific file from archive
	* @access public
	* @param  string $sFilename
	* @return void
	*/
	public function extractArchiveFile($sFilename) {

		$sFilename = (string) $sFilename;
		$sExtractFile = $this->sSource . $sFilename;

		if ($this->sDestination != "") {
			File_Archive::extract($sExtractFile, $this->sDestination);
		} else {
			throw new Contenido_ArchiveExtractor_Exception("Extraction failed: no destination path setted");
		}

	}

	/**
	* Returns the archives file list
	* @access public
	* @return array
	*/
	public function getArchiveFileList() {
		return $this->aFileList;
	}

	/**
	* Checks if a specific file exists in archive
	* @access public
	* @param  string $sFilename
	* @return boolean
	*/
	public function existsInArchive($sFilename) {

		$aFileList = $this->getArchiveFileList();

		if (in_array($sFilename, $aFileList)) {
			$bFileCheck = true;
		} else {
			$bFileCheck = false;
		}

		return $bFileCheck;

	}

	/**
	* Extracts a specific file from archive and return its content to use it in a variable
	* @access public
	* @param  string $sFilename
	* @return string
	*/
	public function extractArchiveFileToVariable($sFilename) {

		$sFilename = (string) $sFilename;
		$sExtractFile = $this->sSource . $sFilename;

		File_Archive::extract($sExtractFile, File_Archive::toVariable($sReturn));
		return $sReturn;
    
	}

}
?>