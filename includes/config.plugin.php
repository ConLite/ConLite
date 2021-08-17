<?php

/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 *  Workflow allocation class
 * 
 * Requirements: 
 * @con_php_req 5.0
 * 
 *
 * @package    Contenido Backend classes
 * @version    1.5
 * @author     Timo Hummel
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * 
 * @todo move functions to own file and add autoloader (Ortwin)  
 *  
 *   $Id: config.plugin.php 128 2019-07-03 11:58:28Z oldperl $
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

plugin_include('cl-workflow', 'includes/functions.workflow.php');

$sPluginName = 'cl-workflow';

global $lngAct, $modidartlang;

$lngAct["workflow"]["workflow_delete"] = i18n("Delete workflow", $sPluginName);
$lngAct["con_workflow"]["workflow_task_user_select"] = i18n("Select workflow task", $sPluginName);
$lngAct["workflow_common"]["workflow_show"] = i18n("Show workflow", $sPluginName);
$lngAct["workflow_common"]["workflow_create"] = i18n("Create workflow", $sPluginName);
$lngAct["workflow_common"]["workflow_save"] = i18n("Edit workflow", $sPluginName);
$lngAct["con"]["workflow_do_action"] = i18n("Process workflow step", $sPluginName);
$lngAct["str"]["workflow_inherit_down"] = i18n("Inherit workflow down", $sPluginName);
$lngAct["str"]["workflow_inherit_down"] = i18n("Inherit workflow down", $sPluginName);
$lngAct["workflow_steps"]["workflow_step_edit"] = i18n("Edit workflow step", $sPluginName);
$lngAct["workflow_steps"]["workflow_step_up"] = i18n("Move workflowstep up", $sPluginName);
$lngAct["workflow_steps"]["workflow_step_down"] = i18n("Move workflowstep down", $sPluginName);
$lngAct["workflow_steps"]["workflow_save_step"] = i18n("Save Workflowstep", $sPluginName);
$lngAct["workflow_steps"]["workflow_create_step"] = i18n("Create workflowstep", $sPluginName);
$lngAct["workflow_steps"]["workflow_step_delete"] = i18n("Delete workflowstep", $sPluginName);
$lngAct["workflow_steps"]["workflow_user_up"] = i18n("Move workflowstepuser up", $sPluginName);
$lngAct["workflow_steps"]["workflow_user_down"] = i18n("Move workflowstepuser down", $sPluginName);
$lngAct["workflow_steps"]["workflow_create_user"] = i18n("Create Workflowstepuser", $sPluginName);
$lngAct["workflow_steps"]["workflow_user_delete"] = i18n("Delete Workflowstepuser", $sPluginName);
$lngAct["str"]["workflow_cat_assign"] = i18n("Associate workflow with category", $sPluginName);

$_cecRegistry->addChainFunction("Contenido.ArticleCategoryList.ListItems", "piworkflowCreateTasksFolder");
$_cecRegistry->addChainFunction("Contenido.ArticleList.Columns", "piworkflowProcessArticleColumns");
$_cecRegistry->addChainFunction("Contenido.ArticleList.Actions", "piworkflowProcessActions");
$_cecRegistry->addChainFunction("Contenido.ArticleList.RenderColumn", "piworkflowRenderColumn");
$_cecRegistry->addChainFunction("Contenido.ArticleList.RenderAction", "piworkflowRenderAction");
$_cecRegistry->addChainFunction("Contenido.CategoryList.Columns", "piworkflowCategoryColumns");
$_cecRegistry->addChainFunction("Contenido.CategoryList.RenderColumn", "piworkflowCategoryRenderColumn");
$_cecRegistry->addChainFunction("Contenido.Frontend.AllowEdit", "piworkflowAllowArticleEdit");


$cfg["tab"]["workflow"] = $cfg['sql']['sqlprefix'] . "_piwf_workflow";
$cfg["tab"]["workflow_allocation"] = $cfg['sql']['sqlprefix'] . "_piwf_allocation";
$cfg["tab"]["workflow_art_allocation"] = $cfg['sql']['sqlprefix'] . "_piwf_art_allocation";
$cfg["tab"]["workflow_items"] = $cfg['sql']['sqlprefix'] . "_piwf_items";
$cfg["tab"]["workflow_user_sequences"] = $cfg['sql']['sqlprefix'] . "_piwf_user_sequences";
$cfg["tab"]["workflow_actions"] = $cfg['sql']['sqlprefix'] . "_piwf_actions";