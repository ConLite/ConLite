<?php
/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * Configuration File
 * 
 * Requirements: 
 * @con_php_req 5
 * 
 *
 * @package    ContenidoBackendArea
 * @version    0.1
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
 *   modified 2008-07-03, bilal arslan, added security fix
 *
 *   $Id$:
 * }}
 * 
 */

if(!defined('CON_FRAMEWORK')) {
  die('Illegal call');
}
 
 
// Relative path to contenido directory, for all inclusions, in most cases: "../contenido/"
$contenido_path = "../conlite/";

// If language isn't specified, set this client and language (ID)
$load_lang		= "1";
$load_client	= "1";

/* Various debugging options */
$frontend_debug["container_display"]    = false;
$frontend_debug["module_display"]	= false;
$frontend_debug["module_timing"]	= false;
$frontend_debug["module_timing_summary"]= false;

/* Set to 1 to brute-force module regeneration */
$force = 0;
?>
