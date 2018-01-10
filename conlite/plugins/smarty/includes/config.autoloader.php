<?php
/**
 * This file contains the autoloader stuff for this plugin
 *
 * @package Plugin
 * @subpackage SmartyWrapper
 * @version $Rev$
 * @since 2.0.2
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright (c) 2018, conlite.org
 * @license http://www.gnu.de/documents/gpl.en.html GPL v3 (english version)
 * @license http://www.gnu.de/documents/gpl.de.html GPL v3 (deutsche Version)
 * @link http://www.conlite.org ConLite.org
 * 
 * $Id$
 */
defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

$sAutoloadClassPath = strstr(dirname(dirname(__FILE__)), "conlite/plugins")."/classes/";
return array(
    'cSmartyBackend' => $sAutoloadClassPath.'class.smarty.backend.php',
    'cSmartyFrontend' => $sAutoloadClassPath.'class.smarty.frontend.php',
    'cSmartyWrapper' => $sAutoloadClassPath.'class.smarty.wrapper.php'
);
?>