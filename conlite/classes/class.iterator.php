<?php

/**
 * @package Core
 * @subpackage Util
 * @version $Rev$
 * @since 2.1
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright (c) 2019, conlite.org
 * @license http://www.gnu.de/documents/gpl.en.html GPL v3 (english version)
 * @license http://www.gnu.de/documents/gpl.de.html GPL v3 (deutsche Version)
 * @link http://www.conlite.org ConLite.org
 * 
 * $Id:$
 */
// security check
defined('CON_FRAMEWORK') or die('Illegal call');

class cIterator {

    /**
     * Holds the items which should be iterated
     * @var array
     */
    var $_aIteratorItems;

    /**
     * Holds the keys of the array which should be iterated
     *
     * @var array
     */
    protected $_aKeys;

    /**
     * 
     * @param array $aItems items to add
     */
    public function __construct($aItems) {
        if (is_array($aItems)) {
            $this->_aIteratorItems = $aItems;
        } else {
            $this->_aIteratorItems = array();
        }

        $this->reset();
    }

    /**
     * reset: Resets the iterator to the first element
     *
     * This function moves the iterator to the first element
     * 
     * @return none
     */
    function reset() {
        $this->_aKeys = array_keys($this->_aIteratorItems);
    }

    /**
     * next: Returns the next item in the iterator
     *
     * This function returns the item, or false if no
     * items are left.
     * 
     * @return mixed item or false if nothing was found
     */
    function next() {
        $key = array_shift($this->_aKeys);
        return isset($this->_aIteratorItems[$key]) ? $this->_aIteratorItems[$key] : false;
    }

    /**
     * count: Returns the number of items in the iterator
     * 
     * @return int Number of items
     */
    function count() {
        if(is_countable($this->_aIteratorItems)) {
            return count($this->_aIteratorItems);
        }
        return 0;
    }

}

?>