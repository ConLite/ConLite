<?php
/**
 * File:
 * config.autoloader.php
 *
 * @package Plugins
 * @subpackage Newsletter
 * @version $Rev: 128 $
 * @since 2.0
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright 2015 CL-Team
 * @link http://www.conlite.org
 *
 * $Id: config.autoloader.php 128 2019-07-03 11:58:28Z oldperl $
 */

if(!defined('CON_FRAMEWORK')) {
	die('Illegal call');
}

$sAutoloadClassPath = 'conlite/plugins/cl-content-allocation/classes/';
return array(
    'pApiContentAllocation' => $sAutoloadClassPath.'class.content_allocation.php',
    'pApiContentAllocationArticle' => $sAutoloadClassPath.'class.content_allocation_article.php',
    'pApiContentAllocationComplexList' => $sAutoloadClassPath.'class.content_allocation_complexlist.php',
    'pApiContentAllocationSelectBox' => $sAutoloadClassPath.'class.content_allocation_selectbox.php',
    'pApiTree' => $sAutoloadClassPath.'class.content_allocation_tree.php',
    'pApiContentAllocationTreeView' => $sAutoloadClassPath.'class.content_allocation_treeview.php'
);
?>