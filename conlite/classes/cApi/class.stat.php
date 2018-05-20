<?php
/**
 * File:
 * class.stat.php
 *
 * Description:
 *  
 * 
 * @package Core
 * @subpackage cApiClasses
 * @version $Rev: 438 $
 * @since 2.0.0
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright (c) 2016, ConLite Team <www.conlite.org>
 * @license http://www.gnu.de/documents/gpl.en.html GPL v3 (english version)
 * @license http://www.gnu.de/documents/gpl.de.html GPL v3 (deutsche Version)
 * @link http://www.conlite.org ConLite.org
 * 
 * $Id: class.stat.php 438 2016-05-17 17:31:24Z oldperl $
 */

// security check
defined('CON_FRAMEWORK') or die('Illegal call');


class cApiStatCollection extends ItemCollection {
    
    public function __construct() {
        global $cfg;
        parent::__construct($cfg['tab']['stat'], 'idstat');
        $this->_setItemClass("cApiStat");
    }

    public function trackView($iIdCatArt, $iIdLang = NULL, $iIdClient = NULL) {
        if (empty($iIdCatArt)) {
            return FALSE;
        }
        if (is_null($iIdLang)) {
            $iIdLang = cRegistry::getLanguageId();
        }

        if (is_null($iIdClient)) {
            $iIdClient = cRegistry::getClientId();
        }

        $this->resetQuery();
        $this->setWhere('idcatart', $iIdCatArt);
        $this->setWhere('idlang', $iIdLang);
        $this->query();
        
        /* @var $oItem cApiStat */
        if (FALSE !== $oItem = $this->next()) {
            $oItem->setField('visited', ((int) $oItem->getField('visited')+1));
        } else {
            $oItem = $this->createNewItem();
            if($oItem->isLoaded()) {
                $oItem->setField('idcatart', $iIdCatArt);
                $oItem->setField('idlang', $iIdLang);
                $oItem->setField('idclient', $iIdClient);
                $oItem->setField('visited', 1);
            } else {
                return FALSE;
            }
        }        
        $oItem->setField('visitdate', date('Y-m-d H:i:s'), FALSE);
        return $oItem->store();
    }
}

class cApiStat extends Item {
    
    public function __construct($mId = false) {
        global $cfg;
        parent::__construct($cfg['tab']['stat'], 'idstat');
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }
}
?>