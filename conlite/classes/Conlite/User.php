<?php

namespace ConLite\Conlite;

use ConLite\GenericDb\Item;
use Contenido_Perm;
use Contenido_Security;

class User extends Item
{
    public function __construct($id = false) {
        parent::__construct(\cRegistry::getConfigValue('tab','phplib_auth_user_md5'), "user_id");
        $this->setFilters();
        if ($id !== false) {
            $this->loadByPrimaryKey($id);
        }
    }

    /**
     * Stores the modified user object to the database
     * @param string type Specifies the type (class, category etc) for the property to retrieve
     * @param string name Specifies the name of the property to retrieve
     * @param boolean group Specifies if this function should recursively search in groups
     * @return string The value of the retrieved property
     */
    public function getUserProperty($type, $name, $group = false) {
        global $cfg, $perm;

        if (!is_object($perm)) {
            $perm = new Contenido_Perm();
        }

        $result = false;

        if ($group) {
            $groups = $perm->getGroupsForUser($this->values['user_id']);

            if (is_array($groups)) {
                foreach ($groups as $value) {
                    $sql = "SELECT value FROM " . $cfg["tab"]["group_prop"] . "
                            WHERE group_id = '" . $value . "'
                              AND type = '$type'
                              AND name = '$name'";
                    $this->db->query($sql);

                    if ($this->db->nextRecord()) {
                        $result = $this->db->f("value");
                    }
                }
            }
        }

        $sql = "SELECT value FROM " . $cfg["tab"]["user_prop"] . "
                WHERE user_id = '" . Contenido_Security::escapeDB($this->values['user_id'], $this->db) . "'
                  AND type = '" . Contenido_Security::escapeDB($type, $this->db) . "'
                  AND name = '" . Contenido_Security::escapeDB($name, $this->db) . "'";
        $this->db->query($sql);

        if ($this->db->nextRecord()) {
            $result = $this->db->f("value");
        }

        if ($result !== false) {
            return urldecode($result);
        } else {
            return false;
        }
    }

    /**
     * Stores the modified user object to the database
     *
     * @param   string  $sType    Specifies the type (class, category etc) for the property to retrieve
     * @param   bool    $bGroup   Specifies if this function should recursively search in groups
     * @return  array   The value of the retrieved property
     * */
    public function getUserPropertiesByType($sType, $bGroup = false) {
        global $cfg, $perm;

        if (!is_object($perm)) {
            $perm = new Contenido_Perm();
        }

        $aResult = array();

        if ($bGroup == true) {
            $aGroups = $perm->getGroupsForUser($this->values['user_id']);

            if (is_array($aGroups)) {
                foreach ($aGroups as $iID) {
                    $sSQL = "SELECT name, value FROM " . $cfg["tab"]["group_prop"] . "
                             WHERE group_id = '" . Contenido_Security::escapeDB($iID, $this->db) . "'
                                AND type = '" . Contenido_Security::escapeDB($sType, $this->db) . "'";
                    $this->db->query($sSQL);

                    while ($this->db->nextRecord()) {
                        $aResult[$this->db->f("name")] = urldecode($this->db->f("value"));
                    }
                }
            }
        }

        $sSQL = "SELECT name, value FROM " . $cfg["tab"]["user_prop"] . "
                 WHERE user_id = '" . Contenido_Security::escapeDB($this->values['user_id'], $this->db) . "'
                 AND type = '" . Contenido_Security::escapeDB($sType, $this->db) . "'";
        $this->db->query($sSQL);

        while ($this->db->nextRecord()) {
            $aResult[$this->db->f("name")] = urldecode($this->db->f("value"));
        }

        return $aResult;
    }

    /**
     * Retrieves all available properties of the user
     *
     * @return array|bool
     */
    public function getUserProperties() {
        global $cfg;

        $sql = "SELECT type, name FROM " . $cfg["tab"]["user_prop"] . "
                WHERE user_id = '" . Contenido_Security::escapeDB($this->values['user_id'], $this->db) . "'";
        $this->db->query($sql);

        if ($this->db->num_rows() == 0) {
            return false;
        }

        $props = array();
        while ($this->db->nextRecord()) {
            $props[] = array("name" => $this->db->f("name"),
                "type" => $this->db->f("type"));
        }

        return $props;
    }

    /**
     * Stores a property to the database
     * @param string type Specifies the type (class, category etc) for the property to retrieve
     * @param string name Specifies the name of the property to retrieve
     * @param string value Specifies the value to insert
     */
    public function setUserProperty($type, $name, $value) {
        global $cfg;

        $value = urlencode($value);

        // Check if such an entry already exists
        if ($this->getUserProperty($type, $name) !== false) {
            $sql = "UPDATE " . $cfg["tab"]["user_prop"] . "
                    SET value = '$value'
                    WHERE user_id = '" . Contenido_Security::escapeDB($this->values['user_id'], $this->db) . "'
                      AND type = '" . Contenido_Security::escapeDB($type, $this->db) . "'
                      AND name = '" . Contenido_Security::escapeDB($name, $this->db) . "'";
            $this->db->query($sql);
        } else {
            $sql = "INSERT INTO  " . $cfg["tab"]["user_prop"] . "
                    SET value = '" . Contenido_Security::escapeDB($value, $this->db) . "',
                        user_id = '" . Contenido_Security::escapeDB($this->values['user_id'], $this->db) . "',
                          type = '" . Contenido_Security::escapeDB($type, $this->db) . "',
                          name = '" . Contenido_Security::escapeDB($name, $this->db) . "',
                        iduserprop = " . $this->db->nextid($cfg["tab"]["user_prop"]);
            $this->db->query($sql);
        }
    }

    /**
     * Deletes a user property from the table
     * @param string type Specifies the type (class, category etc) for the property to retrieve
     * @param string name Specifies the name of the property to retrieve
     */
    public function deleteUserProperty($type, $name) {
        global $cfg;

        // Check if such an entry already exists
        $sql = "DELETE FROM  " . $cfg["tab"]["user_prop"] . "
                    WHERE user_id = '" . Contenido_Security::escapeDB($this->values['user_id'], $this->db) . "' AND
                          type = '" . Contenido_Security::escapeDB($type, $this->db) . "' AND
                          name = '" . Contenido_Security::escapeDB($name, $this->db) . "'";
        $this->db->query($sql);
    }

    public function setPassword($password)
    {
    }
}