<?php
/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * Contenido Note Display
 * 
 * Requirements: 
 * @con_php_req 5.0
 * 
 *
 * @package    Contenido Backend includes
 * @version    1.0.0
 * @author     unknown
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.6
 * 
 * {@internal 
 *   created unknown
 *   modified 2008-06-27, Dominik Ziegler, add security fix
 *
 *   $Id$:
 * }}
 * 
 */

if(!defined('CON_FRAMEWORK')) {
	die('Illegal call');
}


if (isset($action) && $action == "note_delete" && isset($deleteitem))
{
	$oNoteCollection = new NoteCollection();
	$oNoteCollection->delete($deleteitem);
}

if(isset($itemtype) && isset($itemid)) {
    $page = new cPage;

    $oNoteList = new NoteList($itemtype, $itemid);
    $oNoteList->setDeleteable(true);

    $page->setExtra('background: ' . cRegistry::getConfigValue('color', 'table_light'));
    $page->setMargin(0);
    $page->setContent($oNoteList);
    $page->render();
}