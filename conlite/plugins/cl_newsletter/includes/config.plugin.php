<?php
/**
 * File:
 * plugin.conf.php.php
 *
 * @package Plugins
 * @subpackage Newsletter
 * @version $Rev$
 * @since 2.0
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright 2012 CL-Team
 * @link http://www.conlite.org
 *
 * $Id$
 */

// security check
defined('CON_FRAMEWORK') or die('Illegal call');

class nlHandler extends pluginHandlerAbstract {
    
}

/*
plugin_include(nlHandler::getName(), "includes/functions/demo1.php");
plugin_include(nlHandler::getName(), "includes/functions/demo2.php");
 */


$cfg["tab"]["news"] 			= $cfg['sql']['sqlprefix']."_pi_news";
$cfg["tab"]["news_rcp"] 		= $cfg['sql']['sqlprefix']."_pi_news_rcp";
$cfg["tab"]["news_groups"]		= $cfg['sql']['sqlprefix']."_pi_news_groups";
$cfg["tab"]["news_groupmembers"]	= $cfg['sql']['sqlprefix']."_pi_news_groupmembers";
$cfg["tab"]["news_jobs"]                                 = $cfg['sql']['sqlprefix']."_pi_news_jobs";
$cfg["tab"]["news_log"]                                   = $cfg['sql']['sqlprefix']."_pi_news_log";