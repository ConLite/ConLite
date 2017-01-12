<?php
/**
 * File:
 * class.articlelanguage.php
 *
 * Description:
 *  cApi class
 * 
 * @package Core
 * @subpackage cApi
 * @version $Rev: 353 $
 * @since 2.0
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright (c) 2015, conlite.org
 * @license http://www.gnu.de/documents/gpl.en.html GPL v3 (english version)
 * @license http://www.gnu.de/documents/gpl.de.html GPL v3 (deutsche Version)
 * @link http://www.conlite.org ConLite.org
 * 
 * $Id: class.articlelanguage.php 353 2015-09-24 19:18:33Z oldperl $
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}


class cApiArticleLanguageCollection extends ItemCollection {
    
    public function __construct($select = false) {
        global $cfg;
        parent::__construct($cfg["tab"]["art_lang"], "idartlang");
        $this->_setItemClass("cApiArticleLanguage");
        $this->_setJoinPartner("cApiArticleCollection");

        if ($select !== false) {
            $this->select($select);
        }
    }
    
    public function getIdArtLang($iIdart, $iIdlang) {
        $this->setWhere('idart', Contenido_Security::toInteger($iIdart));
        $this->setWhere('idlang', Contenido_Security::toInteger($iIdlang));
        if($this->query() && $this->count() > 0) {
            return $this->next()->get('idartlang');
        }
        return false;
    }
}


class cApiArticleLanguage extends Item
{
    /**
     * Constructor Function
     * @param  mixed  $mId  Specifies the ID of item to load
     */
    public function __construct($mId = false)
    {
        global $cfg;
        parent::__construct($cfg["tab"]["art_lang"], "idartlang");
        $this->setFilters(array(), array());
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }
    
    public function loadByArticleAndLanguageId($idart, $idlang) {
        $result = true;
        if (!$this->isLoaded()) {            
            $idartlang = $this->_getIdArtLang($idart, $idlang);
            $result = $this->loadByPrimaryKey($idartlang);
        }
        return $result;
    }
    
    
    protected function _getIdArtLang($idart, $idlang) {
        $sql = sprintf('SELECT idartlang FROM `%s` WHERE idart = %d AND idlang = %d', cRegistry::getConfigValue('tab', 'art_lang'), $idart, $idlang);
        $this->db->query($sql);
        $this->db->next_record();
        return $this->db->f('idartlang');
    }
    
    public function getContent($type = '', $id = NULL) {
        if (NULL === $this->content) {
            $this->_loadArticleContent();
        }

        if (empty($this->content)) {
            return '';
        }

        if ($type == '') {
            return $this->content;
        }

        $type = strtolower($type);

        if (false === stripos($type, 'cms_')) {
            $type = 'cms_' . $type;
        }

        if (is_null($id)) {
            // return Array
            return $this->content[$type];
        }

        // return String
        return (isset($this->content[$type][$id])) ? $this->content[$type][$id] : '';
    }
    
    protected function _loadArticleContent() {
        if (NULL !== $this->content) {
            return;
        }

        $sql = "SELECT b.type, a.typeid, a.value FROM `".cRegistry::getConfigValue('tab', 'content')
                ."` AS a, `".cRegistry::getConfigValue('tab', 'type')
                ."` AS b WHERE a.idartlang = ".$this->get('idartlang')
                ." AND b.idtype = a.idtype ORDER BY a.idtype, a.typeid";

        $this->db->query($sql);

        $this->content = array();
        while ($this->db->next_record()) {
            $this->content[strtolower($this->db->f('type'))][$this->db->f('typeid')] = urldecode($this->db->f('value'));
        }
    }
}
?>