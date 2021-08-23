<?php

/**
 * 
 */
// security check
defined('CON_FRAMEWORK') or die('Illegal call');

abstract class cItemBaseAbstract {

    /**
     * Database instance, contains the database object
     * @var  DB_ConLite
     */
    protected $db;

    /**
     * Second DB instance, is required for some additional queries without
     * losing an current existing query result.
     * @var  DB_ConLite
     */
    protected $secondDb;

    /**
     * Property collection instance
     * @var  PropertyCollection
     */
    protected $properties;

    /**
     * Item cache instance
     * @var  Contenido_ItemCache
     */
    protected static $_oCache;

    /**
     * GenericDB settings, see $cfg['sql']
     * @var  array
     */
    protected $_settings;

    /**
     * Storage of the source table to use for the information
     * @var  string
     */
    protected $table;

    /**
     * Storage of the primary key
     * @var  string
     * @todo remove access from public
     */
    public $primaryKey;

    /**
     * Checks for the virginity of created objects. If true, the object
     * is virgin and no operations on it except load-Functions are allowed.
     * @todo remove access from public
     * @var  bool
     */
    public $virgin;

    /**
     * Lifetime of results/created objects?
     * FIXME  Not used at the moment!
     * @var  int
     */
    protected $lifetime;

    /**
     * Storage of the last occured error
     * @var  string
     */
    protected $lasterror = '';

    /**
     * Cache the result items
     * FIXME  seems to not used, remove it!
     * @var  array
     */
    protected $cache;

    /**
     * Classname of current instance
     * @var  string
     */
    protected $_className;
    protected $_bDebug;
    
    protected $_bLoaded;

    /**
     * Sets some common properties
     *
     * @param  string  $sTable       Name of table
     * @param  string  $sPrimaryKey  Primary key of table
     * @param  string  $sClassName   Name of parent class
     * @param  int     $iLifetime    Lifetime of the object in seconds (NOT USED!)
     * @throws  Contenido_ItemException  If table name or primary key is not set
     */
    protected function __construct($sTable, $sPrimaryKey, $sClassName, $iLifetime = 10) {
        global $cfg;

        $this->db = new DB_ConLite();

        if ($sTable == '') {
            $sMsg = "$sClassName: No table specified. Inherited classes *need* to set a table";
            throw new Contenido_ItemException($sMsg);
        } elseif ($sPrimaryKey == '') {
            $sMsg = "No primary key specified. Inherited classes *need* to set a primary key";
            throw new Contenido_ItemException($sMsg);
        }

        $this->_settings = $cfg['sql'];

        // instanciate caching
        $aCacheOpt = (isset($this->_settings['cache'])) ? $this->_settings['cache'] : array();
        self::$_oCache = cItemCache::getInstance($sTable, $aCacheOpt);

        $this->table = $sTable;
        $this->primaryKey = $sPrimaryKey;
        $this->virgin = true;
        $this->lifetime = $iLifetime;
        $this->_className = $sClassName;
    }

    /**
     * Escape string for using in SQL-Statement.
     *
     * @param   string  $sString  The string to escape
     * @return  string  Escaped string
     */
    public function escape($sString) {
        return $this->db->escape($sString);
    }
    
    /**
     * Checks if an object is loaded
     * If it is true an object is loaded
     * If it is false then no object is loaded and only load-functions are allowed to be used
     * @return bool Whether an object has been loaded
     */
    public function isLoaded() {
        return (bool) $this->_bLoaded;
    }

    /**
     * Sets loaded state of class
     * If it is true an object is loaded
     * If it is false then no object is loaded and only load-functions are allowed to be used
     *
     * @param bool $value
     *         Whether an object is loaded
     */
    protected function _setLoaded($value) {
        $this->_bLoaded = (bool) $value;
    }

    /**
     * Returns the second database instance, usable to run additional statements
     * without losing current query results.
     *
     * @return  DB_ConLite
     */
    protected function _getSecondDBInstance() {
        if (!isset($this->secondDb) || !($this->secondDb instanceof DB_ConLite)) {
            $this->secondDb = new DB_ConLite();
        }
        return $this->secondDb;
    }

    /**
     * Returns properties instance, instantiates it if not done before.
     *
     * @return  PropertyCollection
     */
    protected function _getPropertiesCollectionInstance() {
        // Runtime on-demand allocation of the properties object
        if (!isset($this->properties) || !($this->properties instanceof PropertyCollection)) {
            $this->properties = new PropertyCollection();
        }
        return $this->properties;
    }

    /**
     * Resets class variables back to default
     * This is handy in case a new item is tried to be loaded into this class instance.
     */
    protected function _resetItem() {
        $this->_setLoaded(false);
        $this->properties = null;
        $this->lasterror = '';
    }
}