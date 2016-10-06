<?php
/**
 * File:
 * class.cHTML.List.php
 *
 * Description:
 *  cHTML List
 * 
 * @package Core
 * @subpackage cHTML
 * @version $Rev: 369 $
 * @since 2.0
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright (c) 2015, conlite.org
 * @license http://www.gnu.de/documents/gpl.en.html GPL v3 (english version)
 * @license http://www.gnu.de/documents/gpl.de.html GPL v3 (deutsche Version)
 * @link http://www.conlite.org ConLite.org
 * 
 * $Id: class.chtml5.list.php 369 2015-10-27 10:53:15Z oldperl $
 */

// security check
defined('CON_FRAMEWORK') or die('Illegal call');

/**
 * Creates an ordered or unordered List
 * 
 * @author Ortwin Pinke <o.pinke@conlite.org>
 */
class cHTML5List extends cHTML {
    
    /**
     *
     * @var array Array of cHTMLListElements
     */
    protected $_aListElements = array();
    
    private $_aListElementClasses = array();
    
    private $_sListElementStyle;






    /**
     * Constructor
     * 
     * @param boolean $bUnorderd use ul or ol default: TRUE = ul
     */
    public function __construct($bUnorderd = TRUE) {        
        parent::__construct();
        $this->setContentlessTag(false);
        if($bUnorderd) {
            $this->_tag = "ul";
        } else {
            $this->_tag = "ol";
        }
    }
    
    /**
     * Add listelement
     * 
     * @param cHTMLListElement $oElement
     */
    public function addListElement(cHTML5ListElement $oElement) {
        $this->_aListElements[] = $oElement;
    }
    
    /**
     * Autofill of list with array of strings or objects
     * Objects must have a render method
     * 
     * @see cHTMLListElement
     * @param array $aContent Array of Strings or Objects 
     */
    public function autoFill($aContent) {
        if(is_array($aContent) && count($aContent) > 0) {
            foreach($aContent as $mContent) {
                $this->_aListElements[] = new cHTML5ListElement($mContent);
            }
        }
    }
    
    public function addClassForElements($sClass) {
        $this->_aListElementClasses[] = $sClass;
    }
    
    public function setStyleForElements($sStyle) {
        $this->_sListElementStyle = $sStyle;
    }
    
    public function toHTML() {
        if(empty($this->_aListElements)) {
            return FALSE;
        }
        $sContent = "";
        $iCountElementClasses = count($this->_aListElementClasses);
        /* @var $oListElement cHTML5ListElement */
        foreach($this->_aListElements as $oListElement) {
            if($iCountElementClasses > 0) {
                foreach($this->_aListElementClasses as $sClass) {
                    $oListElement->addClass($sClass);
                }
            }
            if(!empty($this->_sListElementStyle)) {
                $oListElement->setStyle($this->_sListElementStyle);
            }
            $sContent .= $oListElement->render()."\n";
        }
        $this->_setContent($sContent);
        return parent::toHTML();
    }
}