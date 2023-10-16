<?php
/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * 
 * Requirements: 
 * @con_php_req 5
 * 
 *
 * @package    ContenidoBackendArea
 * @version    0.2
 * @author     unknown
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * 
 * 
 * 
 * {@internal 
 *   created  unknown
 *   modified 2008-07-07, bilal arslan, added security fix
 *
 *   $Id$:
 * }}
 * 
 */
 if(!defined('CON_FRAMEWORK')) {
                die('Illegal call');
}


function getSafeModeStatus ()
{
	if (ini_get("safe_mode") == "1")
	{
		return true;	
	} else {
		return false;	
	}
}

function getSafeModeGidStatus ()
{
	if (ini_get("safe_mode_gid") == "1")
	{
		return true;	
	} else {
		return false;	
	}
}

function getSafeModeIncludeDir ()
{
	return ini_get("safe_mode_include_dir");	
}

function getOpenBasedir ()
{
	return ini_get("open_basedir");
}

function getDisabledFunctions ()
{
	return ini_get("disable_functions");	
}
?>