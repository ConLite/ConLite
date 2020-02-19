<?php

if(!defined('CON_FRAMEWORK')) {
	die('Illegal call');
}

class pimPluginArchiveExtractor {

    /**
     * The extractor initializer
     *
     * @var int
     */
    protected $_extractor = 0;

    /**
     * The temp dir
     *
     * @var string
     */
    protected $tempDir = '';

    /**
     * The archive file
     *
     * @var string
     */
    protected $_source = '';

    /**
     * The destination path
     *
     * @var string
     */
    protected $_destination = '';

    /**
     * The absolute path
     *
     * @var string
     */
    protected $_absPath = '';

    /**
     * 
     * @param string $source
     * @param string $filename
     * @throws pimException
     */
    public function __construct($source, $filename) {
        $cfg = cRegistry::getConfig();

        // initialzing ziparchive
        $this->_extractor = new ZipArchive();

        // path to temp directory
        $this->tempDir = $source;

        // temp directory with zip archive
        $this->_source = (string) $source . (string) $filename;

        if (file_exists($source)) {
            // generate absolute path to the plugin manager directory
            $this->_absPath = $cfg['path']['contenido'] . $cfg['path']['plugins'] . 'pluginmanager' . DIRECTORY_SEPARATOR;

            // open the zip archive
            $this->_extractor->open($this->_source);
        } else {
            throw new pimException('Source file does not exists');
        }
    }

    public function closeArchive() {
        $this->_extractor->close();
    }

    /**
     * 
     * @param string $destination
     * @throws pimException
     */
    public function setDestinationPath($destination) {
        if (!is_dir($destination)) {
            $makeDirectory = mkdir($destination, 0777);
            if ($makeDirectory != true) {
                throw new pimException('Can not set destination path: directoy is not writable');
            }
            $this->_destination = (string) $destination;
        } else {
            throw new pimException('Destination already exists');
        }
    }

    /**
     * 
     * @throws pimException
     */
    public function extractArchive() {
        if ($this->_destination != '') {
            $this->_extractor->extractTo($this->_destination);
        } else {
            throw new pimException('Extraction failed: no destination path setted');
        }
    }

    /**
     * 
     * @param string $filename
     * @param boolean $content
     * @return type
     */
    public function extractArchiveFileToVariable($filename, $content = true) {
        $filename = (string) $filename;
        $this->_extractor->extractTo($this->tempDir, $filename);

        if ($content) {
            return file_get_contents($this->tempDir . $filename);
        } else {
            return $this->tempDir . $filename;
        }
    }

    /**
     * 
     */
    public function destroyTempFiles() {

        // remove plugin.xml if exists
        if (file_exists($this->tempDir . 'cl_plugin.xml')) {
            unlink($this->tempDir . 'cl_plugin.xml');
        }

        // remove plugin_install.sql if exists
        if (file_exists($this->tempDir . 'plugin_install.sql')) {
            unlink($this->tempDir . 'plugin_install.sql');
        }

        // remove temporary plugin dir if exists
        if (file_exists($this->_source)) {
            unlink($this->_source);
        }
    }
}
