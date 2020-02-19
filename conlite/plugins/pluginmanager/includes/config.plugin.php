<?php
/**
 *   $Id: config.plugin.php 23 2016-03-30 17:12:16Z oldperl $
 */

if(!defined('CON_FRAMEWORK')) {
	die('Illegal call');
}

class pimHandler extends pluginHandlerAbstract {
    
}

plugin_include(pimHandler::getName(), "includes/functions/simplexml_dump.php");
plugin_include(pimHandler::getName(), "includes/functions/simplexml_tree.php");
?>