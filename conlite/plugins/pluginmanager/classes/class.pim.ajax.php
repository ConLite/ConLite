<?php
/**
 *   $Id: class.pim.ajax.php 29 2016-10-18 11:27:53Z oldperl $:
 */

if(!defined('CON_FRAMEWORK')) {
	die('Illegal call');
}

abstract class pimAjax{
    
    /**
     * constructor
     */
    public function __construct() {
        ;
    }
    
    /**
     * Handles the ajax request named bei param and returns an answer string
     * child class has to overwrite this method
     * 
     * @param string $Request requested ajax action
     * @return string String send back to requesting page
     */
    abstract public function handle($Request);
}
?>