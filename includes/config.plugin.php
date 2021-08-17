<?php
/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * Config file for Content Allocation plugin
 * 
 * Requirements: 
 * @con_php_req 5.0
 * 
 *
 * @package    Contenido Backend plugins
 * @version    1.0.1
 * @author     unknown
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.6
 * 
 * {@internal 
 *   created unknown
 *   modified 2008-07-02, Frederic Schneider, add security fix
 *
 *   $Id: config.plugin.php 128 2019-07-03 11:58:28Z oldperl $:
 * }}
 * 
 */

if(!defined('CON_FRAMEWORK')) {
	die('Illegal call');
}

// plugin_variables
$cfg['tab']['pica_alloc'] = $cfg['sql']['sqlprefix'].'_pica_alloc';
$cfg['tab']['pica_alloc_con'] = $cfg['sql']['sqlprefix'].'_pica_alloc_con';
$cfg['tab']['pica_lang'] = $cfg['sql']['sqlprefix'].'_pica_lang';

$cfg['pica']['logpath'] = $cfg['path']['contenido'] . $cfg['path']['plugins'] . 'repository/log/data/';
$cfg['pica']['loglevel'] = 'warn';
$cfg['pica']['treetemplate'] = $cfg['path']['contenido'] . $cfg['path']['plugins'] . 'cl-content-allocation/templates/template.tree_structure.html';
$cfg['pica']['treetemplate_article'] = $cfg['path']['contenido'] . $cfg['path']['plugins'] . 'cl-content-allocation/templates/template.tree_article.html';
$cfg['pica']['treetemplate_complexlist'] = $cfg['path']['contenido'] . $cfg['path']['plugins'] . 'cl-content-allocation/templates/template.tree_complexlist.html';

$cfg['pica']['style_complexlist'] = $cfg['path']['contenido_fullhtml'] . $cfg['path']['plugins'] . 'cl-content-allocation/style/complexlist.css';
$cfg['pica']['script_complexlist'] = $cfg['path']['contenido_fullhtml'] . $cfg['path']['plugins'] . 'cl-content-allocation/scripts/complexlist.js';

// administration > users > area translations
global $lngAct, $_cecRegistry;
$lngAct['con_contentallocation']['storeallocation'] = i18n("Store content allocations", "cl-content-allocation");

plugin_include('cl-content-allocation', 'includes/functions.chains.php');

$_cecRegistry->addChainFunction("Contenido.Article.RegisterCustomTab", "pica_RegisterCustomTab");
$_cecRegistry->addChainFunction("Contenido.Article.GetCustomTabProperties", "pica_GetCustomTabProperties");
?>