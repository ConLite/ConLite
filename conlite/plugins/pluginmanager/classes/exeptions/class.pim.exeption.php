<?php

use ConLite\Log\LogWriter;
use ConLite\Log\Log;

if(!defined('CON_FRAMEWORK')) {
	die('Illegal call');
}

class pimExeption extends Exception {
    
    protected $_log_exception = true;
    protected $_logger = NULL;
    
    public function __construct($message, $code = 0, Exception $previous = NULL) {
        parent::__construct($message, $code, $previous);

        // create a logger class and save it for all logging purposes
        $writer = LogWriter::factory("File", array(
            'destination' => cRegistry::getConfigValue('path', 'data')
            . 'logs/exception.log'
        ));
        $this->_logger = new Log($writer);

        // determine if exception should be logged
        if (false === $this->_log_exception
        && !is_null(cRegistry::getConfigValue('debug', 'log_exeptions'))) {
            $this->_log_exception = cRegistry::getConfigValue('debug', 'log_exeptions');
        }

        // log the exception if it should be logged
        if (true === $this->_log_exception) {
            $this->log();
        }
    }
    
    public function log() {
        // construct the log message with all infos and write it via the logger
        $logMessage = get_class($this) . ' thrown at line ' . $this->getLine() . ' of file ' . $this->getFile() . ".\r\n";
        $logMessage .= 'Exception message: ' . $this->getMessage() . "\r\n";
        $logMessage .= "Call stack:\r\n";
        $logMessage .= $this->getTraceAsString();
        $logMessage .= "\r\n";
        $this->_logger->log($logMessage);
    }
}

class pimXmlStructureException extends pimExeption {
    
}