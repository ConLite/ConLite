<?php

/**
 * 
 */
// security check
defined('CON_FRAMEWORK') or die('Illegal call');

class cItemCache {

    /**
     * List of self instances (Contenido_ItemCache)
     * @var  array
     */
    protected static $_oInstances = array();

    /**
     * Assoziative cache array
     * @var  array
     */
    protected $_aItemsCache = array();

    /**
     * Table name for current instance
     * @var  string
     */
    protected $_sTable = '';

    /**
     * Max number of items to cache
     * @var  int
     */
    protected $_iMaxItemsToCache = 10;

    /**
     * Enable caching
     * @var  bool
     */
    protected $_bEnable = false;
    protected $_iFrame;

    /**
     * Contructor of Contenido_ItemCache
     * @param  string  $sTable   Table name
     * @param  array   $aOptions Options array as follows:
     *                 - $aOptions['max_items_to_cache'] = (int) Number of items to cache
     *                 - $aOptions['enable'] = (bool) Flag to enable caching
     */
    protected function __construct($sTable, array $aOptions = array()) {
        $this->_sTable = $sTable;
        if (isset($aOptions['max_items_to_cache']) && (int) $aOptions['max_items_to_cache'] > 0) {
            $this->_iMaxItemsToCache = (int) $aOptions['max_items_to_cache'];
        }
        if (isset($aOptions['enable']) && is_bool($aOptions['enable'])) {
            $this->_bEnable = (bool) $aOptions['enable'];
        }

        if (isset($_GET['frame']) && is_numeric($_GET['frame'])) {
            $this->_iFrame = (int) $_GET['frame'];
        } else {
            $this->_bEnable = false;
        }
    }

    /**
     * Prevent cloning
     */
    protected function __clone() {
        
    }

    /**
     * Returns item cache instance, creates it, if not done before.
     * Works as a singleton for one specific table.
     *
     * @param  string  $sTable   Table name
     * @param  array   $aOptions Options array as follows:
     *                 - $aOptions['max_items_to_cache'] = (int) Number of items to cache
     *                 - $aOptions['enable'] = (bool) Flag to enable caching
     */
    public static function getInstance($sTable, array $aOptions = array()) {
        if (!isset(self::$_oInstances[$sTable])) {
            self::$_oInstances[$sTable] = new self($sTable, $aOptions);
        }
        return self::$_oInstances[$sTable];
    }

    /**
     * Returns items cache list.
     *
     * @return  array
     */
    public function getItemsCache() {
        return $this->_aItemsCache[$this->_iFrame];
    }

    /**
     * Returns existing entry from cache by it's id.
     *
     * @param   mixed  $mId
     * @return  array|null
     */
    public function getItem($mId) {
        if (!$this->_bEnable) {
            return null;
        }

        if (isset($this->_aItemsCache[$this->_iFrame][$mId])) {
            return $this->_aItemsCache[$this->_iFrame][$mId];
        } else {
            return null;
        }
    }

    /**
     * Returns existing entry from cache by matching propery value.
     *
     * @param   mixed  $mProperty
     * @param   mixed  $mValue
     * @return  array|null
     */
    public function getItemByProperty($mProperty, $mValue) {
        if (!$this->_bEnable) {
            return null;
        }

        // loop thru all cached entries and try to find a entry by it's property
        if (is_array($this->_aItemsCache[$this->_iFrame]) && count($this->_aItemsCache[$this->_iFrame]) > 0) {
            foreach ($this->_aItemsCache[$this->_iFrame] as $id => $aEntry) {
                if (isset($aEntry[$mProperty]) && $aEntry[$mProperty] == $mValue) {
                    return $aEntry;
                }
            }
        }
        return null;
    }

    /**
     * Returns existing entry from cache by matching properties and their values.
     *
     * @param   array  $aProperties  Assoziative key value pairs
     * @return  array|null
     */
    public function getItemByProperties(array $aProperties) {
        if (!$this->_bEnable) {
            return null;
        }

        // loop thru all cached entries and try to find a entry by it's property
        foreach ($this->_aItemsCache as $id => $aEntry) {
            $mFound = null;
            foreach ($aProperties as $key => $value) {
                if (isset($aEntry[$key]) && $aEntry[$key] == $value) {
                    if (null === $mFound) {
                        $mFound = true;
                    }
                } else {
                    $mFound = false;
                    break;
                }
                if (true === $mFound) {
                    return $aEntry;
                }
            }
        }
        return null;
    }

    /**
     * Adds passed item data to internal cache
     *
     * @param   mixed  $mId
     * @param   array  $aData  Usually the recordset
     * @return  void
     */
    public function addItem($mId, array $aData) {
        if (!$this->_bEnable) {
            return null;
        }

        if (isset($this->_aItemsCache[$this->_iFrame])) {
            $aTmpItemsArray = $this->_aItemsCache[$this->_iFrame];

            if ($this->_iMaxItemsToCache == count($aTmpItemsArray)) {
                // we have reached the maximum number of cached items, remove first entry
                $firstEntryKey = array_shift($aTmpItemsArray);
                if (is_array($firstEntryKey))
                    return null;
                unset($this->_aItemsCache[$this->_iFrame][$firstEntryKey]);
            }
        }

        // add entry
        $this->_aItemsCache[$this->_iFrame][$mId] = $aData;
    }

    /**
     * Removes existing cache entry by it's key
     *
     * @param   mixed  $mId
     * @return  void
     */
    public function removeItem($mId) {
        if (!$this->_bEnable) {
            return null;
        }

        // remove entry
        if (isset($this->_aItemsCache[$this->_iFrame][$mId])) {
            unset($this->_aItemsCache[$this->_iFrame][$mId]);
        }
    }

}
