<?php
/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 *  
 * 
 * Requirements: 
 * @con_php_req 5.0
 * 
 *
 * @package    Contenido Backend classes
 * @version    1.0
 * @author     Unknown
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * 
 * {@internal 
 *   created 
 *   modified 2008-07-04, bilal arslan, added security fix
 *   $Id$: 
 * }}
 * 
 */
 
if(!defined('CON_FRAMEWORK')) {
	die('Illegal call');
}


function cecFrontendCategoryAccess ($idlang, $idcat, $user)
{
	global $cfg;
	
	$db = new DB_ConLite;
	
	$FrontendUser = new FrontendUser;
	$FrontendUser->loadByPrimaryKey($user);

	if ($FrontendUser->virgin)
	{
		return false;	
	}
	
	$groups = $FrontendUser->getGroupsForUser();

	$FrontendPermissionCollection = new FrontendPermissionCollection;
	
	$sql = "SELECT idcatlang FROM ".$cfg["tab"]["cat_lang"]." WHERE idcat = " . Contenido_Security::toInteger($idcat) . " AND idlang = " . Contenido_Security::toInteger($idlang);
	$db->query($sql);
	
	if ($db->next_record())
	{
		$idcatlang = $db->f("idcatlang");	
	} else {
		return false;	
	}
	
	foreach ($groups as $group)
	{
		$allow = $FrontendPermissionCollection->checkPerm($group, "category", "access", $idcatlang);
		
		if ($allow == true)
		{
			return true;	
		}
	}
	
	return false;
}
?>