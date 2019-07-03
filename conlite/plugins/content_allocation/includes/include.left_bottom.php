<?php
/**
 *
 * @package Plugins
 * @subpackage ContentAllocation
 * @version $Rev$
 * @since 2.0
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright 2017 CL-Team
 * @link http://www.conlite.org
 *
 * $Id$
 */

if(!defined('CON_FRAMEWORK')) {
	die('Illegal call');
}

$oPage = new cPage();
$oPage->setHtml5();

$oPage->render();