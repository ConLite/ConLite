<?php

namespace ConLite\GenericDb;

use stdClass;
abstract class Item extends ItemBaseAbstract
{
    /**
     * @var string
     */
    public $_lastSQL;
    /**
     * Storage of the source table to use for the user informations
     * @var  array
     */
    public $values;

    /**
     * Storage of the fields which were modified, where the keys are the
     * fieldnames and the values just simple booleans.
     * @var  array
     */
    protected $modifiedValues;

    /**
     * Stores the old primary key, just in case somebody wants to change it
     * @var  string
     */
    protected $oldPrimaryKey;

    /**
     * List of funcion names of the filters used when data is stored to the db.
     * @var  array
     */
    protected $_arrInFilters = ['urlencode', 'clHtmlSpecialChars', 'addslashes'];

    /**
     * List of funcion names of the filtersused when data is retrieved from the db
     * @var  array
     */
    protected $_arrOutFilters = ['stripslashes', 'htmldecode', 'urldecode'];

    /**
     * Class name of meta object
     * @var  string
     */
    protected $_metaObject;

    /**
     * Constructor function
     *
     * @param string $sTable The table to use as information source
     * @param string $sPrimaryKey The primary key to use
     * @param int $iLifetime
     */
    public function __construct($sTable = '', $sPrimaryKey = '', $iLifetime = 10)
    {
        parent::__construct($sTable, $sPrimaryKey, get_parent_class($this), $iLifetime);
    }

    function __destruct()
    {
        //print_r(self::$_oCache);
    }

    /**
     * Loads an item by colum/field from the database.
     *
     * @param string $sField Specifies the field
     * @param mixed $mValue Specifies the value
     * @param bool $bSafe Use inFilter or not
     * @return  bool    True if the load was successful
     */
    public function loadBy($sField, mixed $mValue, $bSafe = true): bool
    {
        if ($bSafe) {
            $mValue = $this->_inFilter($mValue);
        }
        if ($sField === $this->primaryKey) {
            $aRecordSet = self::$_oCache->getItem($this->table . "_" . $mValue);
        } else {
            $aRecordSet = self::$_oCache->getItemByProperty($this->table . "_" . $sField, $mValue);
        }

        if ($aRecordSet) {
            // entry in cache found, load entry from cache
            $this->loadByRecordSet($aRecordSet);
            return true;
        }

        // SQL-Statement to select by field
        $sql = sprintf("SELECT * FROM `%s` WHERE %s = '%s'", $this->table, $sField, $mValue);
        //$sql = $this->db->prepare($sql, $this->table, $sField, $mValue);
        // Query the database
        $this->db->query($sql);

        $this->_lastSQL = $sql;

        if ($this->db->num_rows() > 1) {
            $sMsg = "Tried to load a single line with field $sField and value $mValue from "
                . $this->table . " but found more than one row";
            cWarning(__FILE__, __LINE__, $sMsg);
        }

        // Advance to the next record, return false if nothing found
        if (!$this->db->next_record()) {
            return false;
        }

        $this->loadByRecordSet($this->db->toArray());
        return true;
    }

    /**
     * Loads an item by passed where clause from the database.
     * This function is expensive, since it executes allways a query to the database
     * to retrieve the primary key, even if the record set is aleady cached.
     * NOTE: Passed value has to be escaped before. This will not be done by this function.
     *
     * @param string $sWhere The where clause like 'idart = 123 AND idlang = 1'
     * @return  bool    True if the load was successful
     */
    protected function _loadByWhereClause($sWhere)
    {
        // SQL-Statement to select by whee clause
        $sql = sprintf("SELECT %s AS pk FROM `%s` WHERE ", $this->primaryKey, $this->table) . $sWhere;
        //$sql = $this->db->prepare($sql, $this->primaryKey, $this->table);
        // Query the database
        $this->db->query($sql);

        $this->_lastSQL = $sql;

        if ($this->db->num_rows() > 1) {
            $sMsg = "Tried to load a single line with where clause '" . $sWhere . "' from "
                . $this->table . " but found more than one row";
            cWarning(__FILE__, __LINE__, $sMsg);
        }

        // Advance to the next record, return false if nothing found
        if (!$this->db->next_record()) {
            return false;
        }

        $id = $this->db->f('pk');
        return $this->loadByPrimaryKey($id);
    }

    /**
     * Loads an item by ID from the database.
     *
     * @param string $mValue Specifies the primary key value
     * @return  bool    True if the load was successful
     */
    public function loadByPrimaryKey($mValue)
    {
        $bSuccess = $this->loadBy($this->primaryKey, $mValue);

        if (($bSuccess == true) && method_exists($this, '_onLoad')) {
            $this->_onLoad();
        }
        return $bSuccess;
    }

    /**
     * Loads an item by it's recordset.
     *
     * @param array $aRecordSet The recordset of the item
     */
    public function loadByRecordSet(array $aRecordSet)
    {
        $this->values = $aRecordSet;
        $this->oldPrimaryKey = $this->values[$this->primaryKey];
        $this->virgin = false;
        self::$_oCache->addItem($this->table . "_" . $this->oldPrimaryKey, $this->values);
    }

    /**
     * Checks if a the item is already loaded.
     * @return boolean
     */
    public function isLoaded()
    {
        return !$this->virgin;
    }

    /**
     * Function which is called whenever an item is loaded.
     * Inherited classes should override this function if desired.
     *
     * @return  void
     */
    protected function _onLoad()
    {

    }

    /**
     * Gets the value of a specific field.
     *
     * @param string $sField Specifies the field to retrieve
     * @return  mixed   Value of the field
     */
    public function getField($sField)
    {
        if ($this->virgin == true) {
            $this->lasterror = 'No item loaded';
            return false;
        }
        return $this->_outFilter($this->values[$sField]);
    }

    /**
     * Wrapper for getField (less to type).
     *
     * @param string $sField Specifies the field to retrieve
     * @return  mixed   Value of the field
     */
    public function get($sField)
    {
        return $this->getField($sField);
    }

    /**
     * Sets the value of a specific field.
     *
     * @param string $sField Field name
     * @param string $mValue Value to set
     * @param bool $bSafe Flag to run defined inFilter on passed value
     */
    public function setField($sField, $mValue, $bSafe = true): bool
    {
        if ($this->virgin == true) {
            $this->lasterror = 'No item loaded';
            return false;
        }

        $this->modifiedValues[$sField] = true;

        if ($sField == $this->primaryKey) {
            $this->oldPrimaryKey = $this->values[$sField];
        }

        $this->values[$sField] = $bSafe == true ? $this->_inFilter($mValue) : $mValue;
        return true;
    }

    /**
     * Shortcut to setField.
     *
     * @param string $sField Field name
     * @param string $mValue Value to set
     * @param bool $bSafe Flag to run defined inFilter on passed value
     */
    public function set($sField, $mValue, $bSafe = true)
    {
        return $this->setField($sField, $mValue, $bSafe);
    }

    /**
     * Stores the loaded and modified item to the database.
     *
     * @return  bool
     */
    public function store()
    {
        if (!$this->isLoaded()) {
            $this->lasterror = 'No item loaded';
            return false;
        }

        $sql = 'UPDATE `' . $this->table . '` SET ';
        $first = true;

        if (!is_array($this->modifiedValues)) {
            return true;
        }

        foreach (array_keys($this->modifiedValues) as $key) {
            if ($first == true) {
                $sql .= "`$key` = '" . $this->values[$key] . "'";
                $first = false;
            } else {
                $sql .= ", `$key` = '" . $this->values[$key] . "'";
            }
        }

        $sql .= " WHERE " . $this->primaryKey . " = '" . $this->oldPrimaryKey . "'";

        $this->db->query($sql);

        $this->_lastSQL = $sql;

        if ($this->db->affected_rows() > 0) {
            self::$_oCache->addItem($this->table . "_" . $this->oldPrimaryKey, $this->values);
        }

        return $this->db->affected_rows() >= 1;
    }

    /**
     * Returns current item data as an assoziative array.
     */
    public function toArray(): array|false
    {
        if ($this->virgin == true) {
            $this->lasterror = 'No item loaded';
            return false;
        }

        $aReturn = [];
        foreach (array_keys($this->values) as $field) {
            $aReturn[$field] = $this->getField($field);
        }
        return $aReturn;
    }

    /**
     * Returns current item data as an object.
     */
    public function toObject(): stdClass|false
    {
        $return = $this->toArray();
        return (false !== $return) ? (object)$return : $return;
    }

    /**
     * Sets a custom property.
     *
     * @param string $sType Specifies the type
     * @param string $sName Specifies the name
     * @param mixed $mValue Specifies the value
     * @return  bool
     */
    public function setProperty($sType, $sName, mixed $mValue)
    {
        // If this object wasn't loaded before, return false
        if ($this->virgin == true) {
            $this->lasterror = 'No item loaded';
            return false;
        }

        // Set the value
        $oProperties = $this->_getPropertiesCollectionInstance();
        return $oProperties->setValue(
            $this->primaryKey, $this->get($this->primaryKey), $sType, $sName, $mValue
        );
    }

    /**
     * Returns a custom property.
     *
     * @param string $sType Specifies the type
     * @param string $sName Specifies the name
     * @return  mixed   Value of the given property or false
     */
    public function getProperty($sType, $sName)
    {
        // If this object wasn't loaded before, return false
        if ($this->virgin == true) {
            $this->lasterror = 'No item loaded';
            return false;
        }

        // Return the value
        $oProperties = $this->_getPropertiesCollectionInstance();
        return $oProperties->getValue(
            $this->primaryKey, $this->get($this->primaryKey), $sType, $sName
        );
    }

    /**
     * Deletes a custom property.
     *
     * @param string $sType Specifies the type
     * @param string $sName Specifies the name
     * @return  bool
     */
    public function deleteProperty($sType, $sName)
    {
        // If this object wasn't loaded before, return false
        if ($this->virgin == true) {
            $this->lasterror = 'No item loaded';
            return false;
        }

        // Delete the value
        $oProperties = $this->_getPropertiesCollectionInstance();
        return $oProperties->deleteValue(
            $this->primaryKey, $this->get($this->primaryKey), $sType, $sName
        );
    }

    /**
     * Deletes a custom property by its id.
     *
     * @param int $idprop Id of property
     * @return  bool
     */
    public function deletePropertyById($idprop)
    {
        $oProperties = $this->_getPropertiesCollectionInstance();
        return $oProperties->delete($idprop);
    }

    /**
     * Deletes the current item
     *
     * @return void
     */
    // Method doesn't work, remove in future versions
    // function delete()
    // {
    //    $this->_collectionInstance->delete($item->get($this->primaryKey));
    //}

    /**
     * Define the filter functions used when data is being stored or retrieved
     * from the database.
     *
     * Examples:
     * <pre>
     * $obj->setFilters(array('addslashes'), array('stripslashes'));
     * $obj->setFilters(array('htmlencode', 'addslashes'), array('stripslashes', 'htmlencode'));
     * </pre>
     *
     * @param array $aInFilters Array with function names
     * @param array $aOutFilters Array with function names
     *
     * @return void
     */
    public function setFilters($aInFilters = [], $aOutFilters = [])
    {
        $this->_arrInFilters = $aInFilters;
        $this->_arrOutFilters = $aOutFilters;
    }

    /**
     * Filters the passed data using the functions defines in the _arrInFilters array.
     *
     * @param mixed $mData Data to filter
     * @return  mixed  Filtered data
     * @see setFilters
     *
     * @todo  This method is used from public scope, but it should be protected
     *
     */
    public function _inFilter(mixed $mData)
    {
        if (is_numeric($mData) || is_array($mData)) {
            return $mData;
        }

        if (is_null($mData)) {
            $mData = '';
        }

        foreach ($this->_arrInFilters as $_arrInFilter) {
            if (function_exists($_arrInFilter)) {
                $mData = $_arrInFilter($mData);
            }
        }
        return $mData;
    }

    /**
     * Filters the passed data using the functions defines in the _arrOutFilters array.
     *
     * @param mixed $mData Data to filter
     * @return  mixed  Filtered data
     * @see setFilters
     *
     */
    protected function _outFilter(mixed $mData)
    {
        if (is_numeric($mData))
            return $mData;

        foreach ($this->_arrOutFilters as $_arrOutFilter) {
            if (function_exists($_arrOutFilter)) {
                $mData = $_arrOutFilter($mData);
            }
        }
        return $mData;
    }

    protected function _setMetaObject($sObjectName)
    {
        $this->_metaObject = $sObjectName;
    }

    public function getMetaObject()
    {
        global $_metaObjectCache;

        if (!is_array($_metaObjectCache)) {
            $_metaObjectCache = [];
        }

        $sClassName = $this->_metaObject;
        $qclassname = strtolower($sClassName);

        if (array_key_exists($qclassname, $_metaObjectCache) && is_object($_metaObjectCache[$qclassname])) {
            if (strtolower($_metaObjectCache[$qclassname]::class) === $qclassname) {
                $_metaObjectCache[$qclassname]->setPayloadObject($this);
                return $_metaObjectCache[$qclassname];
            }
        }

        if (class_exists($sClassName)) {
            $_metaObjectCache[$qclassname] = new $sClassName($this);
            return $_metaObjectCache[$qclassname];
        }
    }

}