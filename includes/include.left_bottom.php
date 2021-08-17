<?php
/**
 *
 * @package Plugins
 * @subpackage ContentAllocation
 * @version $Rev: 128 $
 * @since 2.0
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright 2017 CL-Team
 * @link http://www.conlite.org
 *
 * $Id: include.left_bottom.php 128 2019-07-03 11:58:28Z oldperl $
 */

if(!defined('CON_FRAMEWORK')) {
	die('Illegal call');
}

$oPage = new cPage();
$oPage->setHtml5();

$oPage->render();