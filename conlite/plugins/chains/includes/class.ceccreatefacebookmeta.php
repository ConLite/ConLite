<?php
/**
 * File:
 * class.createfacebookmeta.php
 *
 * Description:
 *  Creates/edits html tag for fb usage
 * 
 * @package Core
 * @subpackage Chains
 * @version $Rev: 291 $
 * @since 2.0.0
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright (c) 2012-2013, ConLite Team <www.conlite.org>
 * @license http://www.gnu.de/documents/gpl.en.html GPL v3 (english version)
 * @license http://www.gnu.de/documents/gpl.de.html GPL v3 (deutsche Version)
 * @link http://www.conlite.org ConLite.org
 * 
 * $Id: class.ceccreatefacebookmeta.php 291 2014-01-14 23:48:52Z oldperl $
 */

// security check
defined('CON_FRAMEWORK') or die('Illegal call');

/**
 * Description of createFacebookMeta
 *
 * @author Ortwin Pinke <o.pinke@conlite.org>
 */
class cecCreateFacebookMeta {
    
    protected $_aConfig = array();
    
    private $_sFbHeadTag = '<html prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">';

    public function __construct() {
        $this->_aConfig = getEffectiveSettingsByType("meta_tag_creator_html5", array("add_facebook_meta"=>false));
    }
    
    public function createHeadTag($sCode) {
        $bIsHTML5 = ((getEffectiveSetting('generator', 'html5', 'false') == 'false') ? false : true);
        if($bIsHTML5 && $this->_aConfig['add_facebook_meta']) return str_ireplace_once("<html>", $this->_sFbHeadTag, $sCode);
        return $sCode;
    }
}

cAutoload::addClassmapConfig(array("cecCreateFacebookMeta" => "conlite/plugins/chains/includes/class.ceccreatefacebookmeta.php"))
?>