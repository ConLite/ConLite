<?php
/**
 * File:
 * class.metatag.creator.html5.php
 *
 * Description:
 *  Check and/or create metatags for html5
 * 
 * @package Core
 * @subpackage Chains
 * @version $Rev$
 * @since 2.0.0
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright (c) 2012-2013, ConLite Team <www.conlite.org>
 * @license http://www.gnu.de/documents/gpl.en.html GPL v3 (english version)
 * @license http://www.gnu.de/documents/gpl.de.html GPL v3 (deutsche Version)
 * @link http://www.conlite.org ConLite.org
 * 
 * $Id$
 */

// security check
defined('CON_FRAMEWORK') or die('Illegal call');

/**
 * Description of class
 *
 * @author Ortwin Pinke <o.pinke@conlite.org>
 */
class MetaTagCreatorHtml5 {
    
    /**
     *
     * @var array 
     */
    protected static $_MetaExtensions = null;
    
    /**
     *
     * @var array default values for meta names
     */
    protected $_aDefinedMeta = array('application-name','author','description','generator','keywords');
    
    /**
     * Holds all config vars for metatag-creator
     * You may add custom-settings using client-setting
     * meta_tag_creator_html5 | [name of setting] | [value]
     * 
     * possible names|values are
     * only_html5|boolean (default:true) ,if set to true non-valide metas are deleted
     * add_article_meta|boolean (default:true), if set to true metas set in article conf will overwrite existing meta
     * use_cache|boolean (default:true) use cache/cachefile or not
     * cachetime|[seconds] 
     * cachedir|[path to writable cache dir]
     *
     * @var array predefined config array 
     */
    protected $_aConfig = array(
        'only_html5'        =>  true,
        'add_article_meta'  =>  true,
        'use_cache'         =>  true
    );
    
    /**
     *
     * @var string path/filename of cachefile 
     */
    protected $_sCacheFile = null;
    
    /**
     * Incoming and Outgoing MetaTags
     * 
     * @var array holds all metatags
     */
    protected $_aMetaTags = array();
    
    /**
     * New created MetaTags
     *
     * @var array 
     */
    protected $_aCreatedMetaTags = array();


    /**
     *
     * @var boolean switch on debugging output
     */
    protected $_bDebug = false;
    
    /**
     * Object of the current article
     *
     * @var Article
     */
    protected $_oArticle = NULL;

    /**
     * Constructor
     * 
     * @global int $idart
     * @global int $client
     * @global int $lang
     * @param array $aMTags given array of metatags
     * @param array $aConfig configuration array
     * 
     * @return void
     */
    public function __construct($aMTags, $aConfig) {
        global $idart, $client, $lang;
        
        $this->_iIdart = (int) $idart;
        $this->_iClient = (int) $client;
        $this->_iLang = (int) $lang;
        
        if(is_null(self::$_MetaExtensions)) {
            $file = dirname(dirname(__FILE__))."/conf/MetaExtension.php";
            if ($aTmp = include_once($file)) {
                self::$_MetaExtensions = $aTmp;
            }
            self::$_MetaExtensions = array_merge(self::$_MetaExtensions, $this->_aDefinedMeta);
        }
        
        if(is_array($aConfig) && count($aConfig) > 0) {
            $this->_aConfig = array_merge($this->_aConfig, $aConfig);
        }
        $aCustomConfig = getEffectiveSettingsByType("meta_tag_creator_html5");
        if(is_array($aCustomConfig) && count($aCustomConfig) > 0) {
            $this->_aConfig = array_merge($this->_aConfig, $aCustomConfig);
        }
        
        if(is_array($aMTags) && count($aMTags) > 0) {
            $this->_aMetaTags = array_merge($this->_aMetaTags, $aMTags);
        }
        if($this->_bDebug) {
            echo "<pre>";
            print_r($this->_aMetaTags);
        }
        $this->_createCacheFileHash();
    }
    
    /**
     * 
     * @return array generated cached metatag array
     */
    public function generateMetaTags() {
        if($this->_aConfig['use_cache'] && $this->_checkCacheFile()) {
            return $this->_getCacheFile();
        }
        // add metatags
        $this->_addArticleMeta();
        $this->_addFacebookMetaTags();
        
        $this->_mergeNewMetaTags();
        if(count($this->_aMetaTags) > 0) {
            $this->_checkForHtml5Tags();
        }        
        if($this->_bDebug) {
            echo "<pre>";
            print_r($this->_aMetaTags);
        }
        if($this->_aConfig['use_cache'] && $this->_createCacheFile()) {
            return $this->_getCacheFile();
        } 
        return $this->_aMetaTags;
    }
    
    /**
     * Adds article meta to meta array
     * 
     * @global int $lang
     * @global array $encoding
     * @return void
     */
    protected function _addArticleMeta() {
        global $lang, $encoding;
        if($this->_aConfig['add_article_meta'] === false) return false;
        if(is_null($this->_oArticle) || !is_object($this->_oArticle)) {
            $this->_oArticle = new Article($this->_iIdart, $this->_iClient, $this->_iLang);        
        }
        $aHeadLines = $this->_checkAndMergeArrays($this->_oArticle->getContent("htmlhead"),
                $this->_oArticle->getContent("head"));
        $aText = $this->_checkAndMergeArrays($this->_oArticle->getContent("html"),
                $this->_oArticle->getContent("text"));
        $sHead = $this->_getFirstArrayValue($aHeadLines);
        $sText = $this->_getFirstArrayValue($aText);
        if($sHead) {
            $sHead = substr(str_replace(chr(13).chr(10),' ',strip_tags($sHead)),0,100);
            $this->_addMeta('description', $sHead);
        }
        if($sText) {
            $sText = keywordDensity($sHead, strip_tags(urldecode($sText)), $encoding[$lang]);
            $this->_addMeta('keywords', $sText);
        }
        
        // get custom meta from article conf
        $aAvailableMeta = conGetAvailableMetaTagTypes();
        foreach($aAvailableMeta as $iIdMeta=>$aValue) {
            if($aValue['fieldname'] != 'name') continue;
            if($this->_isHtml5Ext($aValue['name'])) {
                $sTmpContent = conGetMetaValue($this->_oArticle->getIdArtLang(), $iIdMeta);
                if(empty($sTmpContent)) continue;
                $this->_addMeta($aValue['name'], $sTmpContent);
            }
        }
        unset($oArticle);
    }
    
    /**
     * 
     *
     * <!-- Facebook Meta Data -->
     * <meta property="og:image" content="http://www.conlite.org/uploads/fb_thumbs/sample.jpg"/>
     * <meta property="og:locality" content="Eltmann"/>
     * <meta property="og:country-name" content="Germany"/>
     * <meta property="og:latitude" content="49.9718"/>
     * <meta property="og:longitude" content="10.6666"/>
     * <meta property="og:type" content="blog"/>
     * <meta property="og:title" content="Virgin Atlantic Advert 2010"/>
     * <meta property="og:url" content="http://www.conlite.org/de/sample-article/"/>
     * <meta property="og:site_name" content="ConLite Portal"/>
     * <meta property="fb:admins" content="123456789,234567891"/>
     * <meta property="fb:page_id" content="200185198887" />
     * 
     * @return boolean
     */
    protected function _addFacebookMetaTags() {
        if(!$this->_aConfig['add_facebook_meta']) return;
        // add always article data, cause they needed for fb-meta
        if($this->_aConfig['add_article_meta'] === false) {
            $this->_aConfig['add_article_meta'] = true;
            $this->_addArticleMeta();
        }        
        $aAllowedFbMetas = array("og:image","og:locality","og:country-name","og:latitude","og:longitude",
            "og:type","og:title","og:url","og:site_name","fb_admins","fb:page_id");
        $this->_aDefinedMeta = array_merge($this->_aDefinedMeta, $aAllowedFbMetas);
        $aFbSystemSettings = getEffectiveSettingsByType('facebook');
        $bHasSysSettings = (count($aFbSystemSettings) > 0)?true:false;
        foreach ($aAllowedFbMetas as $sMetaName) {
            if($sProp = $this->_oArticle->getProperty('facebook', $sMetaName)) {
                if(empty($sProp)) {
                    continue;
                } else {
                    $this->_addPropertyMeta($sMetaName, $sProp);
                }
            }
            if($bHasSysSettings) {
                if(array_key_exists($sMetaName, $aFbSystemSettings)) {
                    $this->_addPropertyMeta($sMetaName, $aFbSystemSettings[$sMetaName]);
                }
            }
            
            switch ($sMetaName) {
                case "og:url":
                    $aParams = array ('idcat' => $this->_iIdart,'lang' => $this->_iLang);
                    $sUrl = Contenido_Url::getInstance()->build($aParams, true);
                    $this->_addPropertyMeta($sMetaName, $sUrl);
                    break;
                
                case "og:title":
                    $sTitle = $this->_oArticle->getField("title");
                    $this->_addPropertyMeta($sMetaName, $sTitle);
                    break;
                
                case "og:image":
                    if(!isset($this->_aCreatedMetaTags[$sMetaName])) {
                        $aImages = $this->_oArticle->getContent("img");
                        $iImg = 0;
                        if(is_array($aImages) && count($aImages) > 0) {
                            if(isset($aFbSystemSettings['cms_img_no']) 
                                    && (int)$aFbSystemSettings['cms_img_no'] > 0
                                    && array_key_exists($aFbSystemSettings['cms_img_no'], $aImages)) {
                                $iImg = $aFbSystemSettings['cms_img_no'];
                            } else {
                                $iImg = 1;
                            }
                            if($iImg && $aFbSystemSettings['use_cms_img']) {
                                /* @var $oImg UploadItem */
                                $oImg = new UploadItem($iImg);
                                $sImgHtmlPath = $oImg->getField("url");
                                unset($oImg);                                
                            }
                        }                        
                    }
                    break;
            }
        }        
    }
    
    protected function _mergeNewMetaTags() {
        if(count($this->_aCreatedMetaTags) > 0) {
            foreach($this->_aCreatedMetaTags as $iKey=>$aValue) {
                $iKey = $this->_inMetaArray($aValue['name'], $this->_aMetaTags);
                if($iKey !== false) {
                    $this->_aMetaTags[$iKey]['content'] = $aValue['content'];
                } else {
                    array_push($this->_aMetaTags, $aValue);
                }
            }
        }
    }

    /**
     * Check meta array for valid html5 meta tags
     * 
     * @todo add support for other meta tags than name
     * @return void
     */
    protected function _checkForHtml5Tags() {
        if(!$this->_aConfig['only_html5']) return;
        foreach($this->_aMetaTags as $iKey => $aValue) {
            if(key_exists('name', $aValue)) {
                if($this->_isHtml5Ext($aValue['name'])) continue;
                unset($this->_aMetaTags[$iKey]);
            }
        }
        
    }
    
    /**
     * Check if extensions is registered
     * 
     * @uses $_MetaExtensions Array of default and registered extensions
     * @param string $sExt
     * @return boolean
     */
    protected function _isHtml5Ext($sExt) {
        $sExt = strtolower($sExt);
        // check standard tags first
        if(in_array($sExt, $this->_aDefinedMeta)) return true;
        // check only names to save time
        if(in_array($sExt, self::$_MetaExtensions['names'])) return true;
        // now check keys with deeper arrays
        if(array_key_exists($sExt, self::$_MetaExtensions)) return true;
        // parts
        foreach(self::$_MetaExtensions as $sKey=>$aValue) {
            if($sKey === $sExt) return true;
            if(stristr($sKey, $sExt)) return true;
        }        
        return false;
    }

    /**
     * Cachefile exists and not outdated
     * 
     * @return boolean
     */
    protected function _checkCacheFile() {
        if(file_exists($this->_sCacheFile)) {
            $iDiff = time() - filemtime($this->_sCacheFile);
            if($iDiff < $this->_aConfig['cachetime']) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Get meta-array from cachefile
     * 
     * @return array
     */
    protected function _getCacheFile() {
        return unserialize(file_get_contents($this->_sCacheFile));
    }
    
    /**
     * Create cachefile
     * 
     * @return boolean
     */
    protected function _createCacheFile() {
        if(empty($this->_sCacheFile)) {
            return false;
        }
        return (file_put_contents($this->_sCacheFile, serialize($this->_aMetaTags)) === false)?false:true;
    }

    /**
     * Create path to cachefile with hashed filename
     * 
     * @global int $idart
     * @global int $lang
     * @return void
     */
    protected function _createCacheFileHash() {
        global $idart, $lang;
        if(isset($this->_aConfig['cachedir']) 
                && !empty($this->_aConfig['cachedir']) 
                && is_dir($this->_aConfig['cachedir'])
                && is_writable($this->_aConfig['cachedir'])) {
            $hash = 'metatag_'.md5($idart.'/'.$lang);
            $this->_sCacheFile = $this->_aConfig['cachedir'].$hash.'.tmp';
        }
    }
    
    /**
     * Merge 2 arrays
     * 
     * @param array $aArr1
     * @param array $aArr2
     * @return array merged array
     */
    protected function _checkAndMergeArrays($aArr1, $aArr2) {
        if(!is_array($aArr1)) {
            $aArr1 = array();
        }
        if(!is_array($aArr2)) {
            $aArr2 = array();
        }
        return array_merge($aArr1, $aArr2);
    }
    
    /**
     * 
     * @param type $aArr
     * @return mixed text as string or false
     */
    protected function _getFirstArrayValue($aArr) {
        $sText = "";
        foreach ($aArr as $key => $value) {
            if ($value != '') {
                $sText = $value;
                break;
            }
        }
        return (empty($sText))?false:$sText;
    }
    
    /**
     * Add new meta to meta-array
     * overwrite if exist
     * 
     * @param string $sName
     * @param string $sValue
     * @return void
     */
    protected function _addMeta($sName, $sValue) {
        $aTmp = array(
            'name'      =>  $sName,
            'content'   =>  $sValue
        );
        $iTmpKey = $this->_inMetaArray($sName, $this->_aCreatedMetaTags);
        if(false !== $iTmpKey) {
            $this->_aCreatedMetaTags[$iTmpKey]['content'] = $sValue;
        } else {
            array_push($this->_aCreatedMetaTags, $aTmp);
        }
    }
    
    protected function _addPropertyMeta($sName, $sValue) {
        $aTmp = array(
            'property'      =>  $sName,
            'content'   =>  $sValue
        );
        $iTmpKey = $this->_inMetaArray($sName, $this->_aCreatedMetaTags);
        if(false !== $iTmpKey) {
            $this->_aCreatedMetaTags[$iTmpKey]['content'] = $sValue;
        } else {
            array_push($this->_aCreatedMetaTags, $aTmp);
        }
    }
    
    /**
     * Search in meta-array for a name/content
     * returns the key if the needle is found
     * 
     * @param string $sNeedle
     * @param array $aHaystack
     * @param boolean $bStrict
     * @return mixed key_number in haystack or false if nothing was found
     */
    protected function _inMetaArray($sNeedle, $aHaystack, $bStrict = false) {
        foreach($aHaystack as $iKey=>$aValue) {
            if(in_array($sNeedle, $aValue)) return $iKey;
        }
        return false;
    }
}
?>