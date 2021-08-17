<?php

/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * Workflow management class
 * 
 * Requirements: 
 * @con_php_req 5.0
 * 
 *
 * @package    Contenido Backend classes
 * @version    1.6
 * @author     Timo Hummel
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * 
 * {@internal 
 *   created 2003-07-18
 *   
 *   $Id: class.workflow.php 128 2019-07-03 11:58:28Z oldperl $
 * }}
 * 
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

/**
 * Class Workflows
 * Class for workflow management
 * @author Timo A. Hummel <Timo.Hummel@4fb.de>
 * @version 0.2
 * @copyright four for business 2003
 */
class Workflows extends ItemCollection {

    /**
     * Constructor Function
     * @param none
     */
    function __construct() {
        global $cfg;
        parent::__construct($cfg["tab"]["workflow"], "idworkflow");
        $this->_setItemClass("Workflow");
    }

    function create() {
        global $auth, $client, $lang;
        $newitem = parent::createNewItem();
        $newitem->setField("created", date("Y-m-d H-i-s"));
        $newitem->setField("idauthor", $auth->auth["uid"]);
        $newitem->setField("idclient", $client);
        $newitem->setField("idlang", $lang);
        $newitem->store();

        return ($newitem);
    }

    /**
     * Deletes all corresponding informations to this workflow and delegate call to parent
     * @param integer $idWorkflow - id of workflow to delete
     */
    function delete($idWorkflow) {
        global $cfg;
        $oDb = new DB_ConLite();

        $aItemIdsDelete = array();
        $sSql = 'SELECT idworkflowitem FROM ' . $cfg["tab"]["workflow_items"] . ' WHERE idworkflow = ' . Contenido_Security::toInteger($idWorkflow) . ';';
        $oDb->query($sSql);
        while ($oDb->next_record()) {
            array_push($aItemIdsDelete, Contenido_Security::escapeDB($oDb->f('idworkflowitem'), $oDb));
        }

        $aUserSequencesDelete = array();
        $sSql = 'SELECT idusersequence FROM ' . $cfg["tab"]["workflow_user_sequences"] . ' WHERE idworkflowitem in (' . implode(',', $aItemIdsDelete) . ');';
        $oDb->query($sSql);
        while ($oDb->next_record()) {
            array_push($aUserSequencesDelete, Contenido_Security::escapeDB($oDb->f('idusersequence'), $oDb));
        }

        $sSql = 'DELETE FROM ' . $cfg["tab"]["workflow_user_sequences"] . ' WHERE idworkflowitem in (' . implode(',', $aItemIdsDelete) . ');';
        $oDb->query($sSql);

        $sSql = 'DELETE FROM ' . $cfg["tab"]["workflow_actions"] . ' WHERE idworkflowitem in (' . implode(',', $aItemIdsDelete) . ');';
        $oDb->query($sSql);

        $sSql = 'DELETE FROM ' . $cfg["tab"]["workflow_items"] . ' WHERE idworkflow = ' . Contenido_Security::toInteger($idWorkflow) . ';';
        $oDb->query($sSql);

        $sSql = 'DELETE FROM ' . $cfg["tab"]["workflow_allocation"] . ' WHERE idworkflow = ' . Contenido_Security::toInteger($idWorkflow) . ';';
        $oDb->query($sSql);

        $sSql = 'DELETE FROM ' . $cfg["tab"]["workflow_art_allocation"] . ' WHERE idusersequence in (' . implode(',', $aUserSequencesDelete) . ');';
        $oDb->query($sSql);

        parent::delete($idWorkflow);
    }

}

/**
 * Class Workflow
 * Class for a single workflow item
 * @author Timo A. Hummel <Timo.Hummel@4fb.de>
 * @version 0.1
 * @copyright four for business 2003
 */
class Workflow extends Item {

    /**
     * Constructor
     * 
     * @global array $cfg 
     */
    function __construct() {
        global $cfg;
        parent::__construct($cfg["tab"]["workflow"], "idworkflow");
    }

}