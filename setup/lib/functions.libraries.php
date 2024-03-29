<?php
/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * <Description>
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

define("E_IMAGERESIZE_GD", 					1);
define("E_IMAGERESIZE_IMAGEMAGICK", 		2);
define("E_IMAGERESIZE_CANTCHECK",			3);
define("E_IMAGERESIZE_NOTHINGAVAILABLE", 	4);

function checkImageResizer ()
{
	
	$iGDStatus = isPHPExtensionLoaded("gd");
	
	if ($iGDStatus == E_EXTENSION_AVAILABLE)
	{
		return E_IMAGERESIZE_GD;	
	}
	
	if (function_exists("imagecreate"))
	{
		return E_IMAGERESIZE_GD;	
	}
	
	if (isImageMagickAvailable())
	{
		return E_IMAGERESIZE_IMAGEMAGICK;	
	}
	
	if ($iGDStatus === E_EXTENSION_CANTCHECK)
	{
		return E_IMAGERESIZE_CANTCHECK;	
	} else {
		return E_IMAGERESIZE_NOTHINGAVAILABLE;	
	}

}

function isImageMagickAvailable ()
{
	global $_imagemagickAvailable;
	
	if (is_bool($_imagemagickAvailable))
	{
		if ($_imagemagickAvailable === true)
		{
			return true;	
		} else {
			return false;	
		}
	}
	
	$output = [];
	
	$retval = "";
	
	@exec("convert",$output, $retval);

    if (!is_array($output) || count($output) == 0)
    {
        return false;
    }
    	
	if (str_contains($output[0],"ImageMagick"))
	{
		$_imagemagickAvailable = true;
		return true;
	} else {
		$_imagemagickAvailable = false;
		return false;
	}
}

?>