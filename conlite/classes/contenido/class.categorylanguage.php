<?php
/**
 * File:
 * class.categorylanguage.php
 *
 * Description:
 *  cApi class
 * 
 * @package Core
 * @subpackage cApi
 * @version $Rev: 352 $
 * @since 2.0
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright (c) 2015, conlite.org
 * @license http://www.gnu.de/documents/gpl.en.html GPL v3 (english version)
 * @license http://www.gnu.de/documents/gpl.de.html GPL v3 (deutsche Version)
 * @link http://www.conlite.org ConLite.org
 * 
 * $Id: class.categorylanguage.php 352 2015-09-24 12:12:51Z oldperl $
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}


class cApiCategoryLanguageCollection extends ItemCollection
{
    public function __construct($select = false)
    {
        global $cfg;
        parent::__construct($cfg["tab"]["cat_lang"], "idcatlang");
        $this->_setItemClass("cApiCategoryLanguage");
        $this->_setJoinPartner("cApiCategoryCollection");
        if ($select !== false) {
            $this->select($select);
        }
    }

    /** @deprecated  [2011-03-15] Old constructor function for downwards compatibility */
    public function cApiCategoryLanguageCollection($select = false)
    {
        cWarning(__FILE__, __LINE__, "Deprecated method call, use __construct()");
        $this->__construct($select);
    }
}


class cApiCategoryLanguage extends Item
{
    /**
     * Constructor Function
     * @param  mixed  $mId  Specifies the ID of item to load
     */
    public function __construct($mId = false)
    {
        global $cfg;
        parent::__construct($cfg["tab"]["cat_lang"], "idcatlang");
        $this->setFilters(array(), array());
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }

    /** @deprecated  [2011-03-15] Old constructor function for downwards compatibility */
    public function cApiCategoryLanguage($mId = false)
    {
        cWarning(__FILE__, __LINE__, "Deprecated method call, use __construct()");
        $this->__construct($mId);
    }

    public function setField($field, $value)
    {
        switch ($field) {
            case "name":
                $this->setField("urlname", $value);
                break;
            case "urlname":
                $value = clHtmlSpecialChars(capiStrCleanURLCharacters($value), ENT_QUOTES);
                break;
        }

        parent::setField($field, $value);
    }

    public function assignTemplate($idtpl)
    {
        $c_tplcfg = new cApiTemplateConfigurationCollection();

        if ($this->get("idtplcfg") != 0) {
            // Remove old template first
            $c_tplcfg->delete($this->get("idtplcfg"));
        }

        $tplcfg = $c_tplcfg->create($idtpl);

        $this->set("idtplcfg", $tplcfg->get("idtplcfg"));
        $this->store();

        return ($tplcfg);
    }

    public function getTemplate()
    {
        $c_tplcfg = new cApiTemplateConfiguration($this->get("idtplcfg"));
        return ($c_tplcfg->get("idtpl"));
    }

    public function hasStartArticle()
    {
        cInclude("includes", "functions.str.php");
        return strHasStartArticle($this->get("idcat"), $this->get("idlang"));
    }
}

?>