<?php
/**
 * File:
 * class.template.php
 *
 * Description:
 *  Base class for all cHTML-Classes
 * 
 * @package Core
 * @subpackage Template
 * @version $Rev: 195 $
 * @deprecated since CL 2.0, classes moved to own folder, so this file is just to include new class files for compability.
 *              File will be removed in one of the next CL-versions 
 * @since 2.0
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright c) 2012, conlite.org
 * @license http://www.gnu.de/documents/gpl.en.html GPL v3 (english version)
 * @license http://www.gnu.de/documents/gpl.de.html GPL v3 (deutsche Version)
 * @link http://www.conlite.org ConLite.org
 * 
 * $Id: class.template.php 195 2012-12-19 06:09:48Z oldperl $
 */

// security check
defined('CON_FRAMEWORK') or die('Illegal call');
$cErrorType = (version_compare(PHP_VERSION, '5.3.0') >= 0)?E_USER_DEPRECATED:E_USER_NOTICE;
trigger_error("Include of ".__FILE__." is deprecated since CL 2.0. Please use autoloader or include file in folder classes/template.", $cErrorType);
cInclude("classes", "template/class.template.php");
?>
