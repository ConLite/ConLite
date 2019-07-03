<?php
/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * Frontend groups class
 * 
 * Requirements: 
 * @con_php_req 5.0
 * 
 *
 * @package    Contenido Backend classes
 * @version    1.7
 * @author     unknown
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.6
 * 
 * {@internal
 *   created  unknown
 *   modified 2008-06-30, Frederic Schneider, add security fix
 *   modified 2011-03-14, Murat Purc, adapted to new GenericDB, partly ported to PHP 5, formatting
 *
 *   $Id$:
 * }}
 * 
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}


/**
 * Frontend group management class
 */
class FrontendGroupCollection extends ItemCollection
{
    /**
     * Constructor Function
     * @param none
     */
    public function __construct()
    {
        global $cfg;
        parent::__construct($cfg["tab"]["frontendgroups"], "idfrontendgroup");
        $this->_setItemClass("FrontendGroup");
    }

    /**
     * Creates a new group
     * @param $groupname string Specifies the groupname
     * @param $password string Specifies the password (optional)
     */
    public function create($groupname)
    {
        global $client;

        $group = new FrontendGroup();

        #$_arrInFilters = array('urlencode', 'clHtmlSpecialChars', 'addslashes');

        $mangledGroupName = $group->_inFilter($groupname);
        $this->select("idclient = '$client' AND groupname = '$mangledGroupName'");

        if ($obj = $this->next()) {
            $groupname = $groupname. md5(rand());
        }

        $item = parent::createNewItem();

        $item->set("idclient", $client);
        $item->set("groupname", $groupname);
        $item->store();

        return $item;
    }

    /**
     * Overridden delete method to remove groups from groupmember table
     * before deleting group
     *
     * @param $itemID int specifies the frontend user group
     */
    public function delete($itemID)
    {
        $associations = new FrontendGroupMemberCollection();
        $associations->select("idfrontendgroup = '$itemID'");

        while ($item = $associations->next()) {
            $associations->delete($item->get("idfrontendgroupmember")); 
        }
        parent::delete($itemID); 
    }
}


/**
 * Single FrontendGroup Item
 */
class FrontendGroup extends Item
{
    /**
     * Constructor Function
     * @param  mixed  $mId  Specifies the ID of item to load
     */
    public function __construct($mId = false)
    {
        global $cfg;
        parent::__construct($cfg["tab"]["frontendgroups"], "idfrontendgroup");
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }
}


/**
 * Frontend group member management class
 */
class FrontendGroupMemberCollection extends ItemCollection
{
    /**
     * Constructor Function
     * @param none
     */
    public function __construct()
    {
        global $cfg;
        parent::__construct($cfg["tab"]["frontendgroupmembers"], "idfrontendgroupmember");
        $this->_setJoinPartner ('FrontendGroupCollection');
        $this->_setJoinPartner ('FrontendUserCollection');
        $this->_setItemClass("FrontendGroupMember");
    }

    /**
     * Creates a new association
     * @param $idfrontendgroup int specifies the frontend group
     * @param $idfrontenduser  int specifies the frontend user
     */
    public function create($idfrontendgroup, $idfrontenduser)
    {
        $this->select("idfrontendgroup = '$idfrontendgroup' AND idfrontenduser = '$idfrontenduser'");

        if ($this->next()) {
            return false;
        }

        $item = parent::createNewItem();

        $item->set("idfrontenduser", $idfrontenduser);
        $item->set("idfrontendgroup", $idfrontendgroup);
        $item->store();

        return $item;
    }

    /**
     * Removes an association
     * @param $idfrontendgroup int specifies the frontend group
     * @param $idfrontenduser  int specifies the frontend user
     */
    public function remove($idfrontendgroup, $idfrontenduser)
    {
        $this->select("idfrontendgroup = '$idfrontendgroup' AND idfrontenduser = '$idfrontenduser'");

        if ($item = $this->next()) {
            $this->delete($item->get("idfrontendgroupmember"));
        }
    }

    /**
     * Returns all users in a single group
     * @param $idfrontendgroup int specifies the frontend group
     * @param $asObjects boolean specifies if the function should return objects
     * @return array FrontendUser items 
     */
    public function getUsersInGroup($idfrontendgroup, $asObjects = true)
    {
        $this->select("idfrontendgroup = '$idfrontendgroup'");

        $objects = array();

        while ($item = $this->next()) {
            if ($asObjects) {
                $user = new FrontendUser;
                $user->loadByPrimaryKey($item->get("idfrontenduser"));
                $objects[] = $user;
            } else {
                $objects[] = $item->get("idfrontenduser");
            }
        }

        return ($objects);
    }
}


/**
 * Single FrontendGroup Item
 */
class FrontendGroupMember extends Item
{
    /**
     * Constructor Function
     * @param  mixed  $mId  Specifies the ID of item to load
     */
    public function __construct($mId = false)
    {
        global $cfg;
        parent::__construct($cfg["tab"]["frontendgroupmembers"], "idfrontendgroupmember");
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }
}