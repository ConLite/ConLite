<?php

defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

class cArticleCollector implements SeekableIterator, Countable {

    /**
     *
     * @var int  
     */
    protected $_iCurrentPosition = 0;
    protected $_aArticles = array();
    protected $_aStartArticles = array();
    protected $_aOptions = array();
    protected $_aOptionsDefault = array();
    private $_bAsObject = TRUE;

    /**
     *
     * @var cApiArticleLanguage 
     */
    private $_oArticleLanguage;

    public function __construct($aOptions = array()) {
        $this->setOptions($aOptions);
        $this->_oArticleLanguage = new cApiArticleLanguage();
    }

    public function loadArticles() {
        $this->_getStartArticles();

        // now lets fetch articles
        $oArtLangColl = new cApiArticleLanguageCollection(); //a
        $oArtLangColl->link("cApiArticleCollection"); //b
        $oArtLangColl->link("cApiCategoryArticleCollection"); //c

        $oArtLangColl->addResultField('idcat');

        if (count($this->_aOptions['categories']) > 0) {
            $oArtLangColl->setWhere("cApiCategoryArticleCollection.idcat", $this->_aOptions['categories'], "IN");
        }
        if (count($this->_aOptions['artspecs']) > 0) {
            $oArtLangColl->setWhere("cApiArticleLanguageCollection.artspec", implode(",", $this->_aOptions['artspec']), "IN");
        }
        
        if (count($this->_aStartArticles) > 0) {
            print_r($this->_aStartArticles);
            if ($this->_aOptions['start'] == false) {
                $oArtLangColl->setWhere("cApiArticleLanguageCollection.idartlang", $this->_aStartArticles, "NOTIN");
                //$sqlStartArticles = "a.idartlang NOT IN ('" . implode("','", $this->_startArticles) . "') AND ";
            }

            if ($this->_aOptions['startonly'] == true) {
                echo "startonly";
                $oArtLangColl->setWhere("cApiArticleLanguageCollection.idartlang", $this->_aStartArticles, "IN");
                //$sqlStartArticles = "a.idartlang IN ('" . implode("','", $this->_startArticles) . "') AND ";
            }
        }

        if ($this->_aOptions['offlineonly'] == true) {
            //$sql .= " AND a.online = 0";
            $oArtLangColl->setWhere("cApiArticleLanguageCollection.online", 0);
        } else if ($this->_aOptions['offline'] == false) {
            //$sql .= " AND a.online = 1";
            $oArtLangColl->setWhere("cApiArticleLanguageCollection.online", 1);
        }

        $oArtLangColl->setWhere("cApiArticleLanguageCollection.idlang", $this->_aOptions['lang']);

        $oArtLangColl->query();
        echo $oArtLangColl->_lastSQL;
        if ($oArtLangColl->count() > 0) {
            $aTable = $oArtLangColl->fetchTable();
            //echo $oArtLangColl->_lastSQL;

            foreach ($aTable as $aItem) {
                $this->_aArticles[] = $aItem['idartlang'];
            }
            print_r($this->_aArticles);
        }
    }

    /**
     * 
     * @param array $aOptions
     */
    public function setOptions(array $aOptions) {
        if (count($this->_aOptionsDefault) == 0) {
            $this->_setOptionsDefault();
        }

        if (isset($aOptions['idcat']) && !isset($aOptions['categories'])) {
            $aOptions['categories'] = array(
                $aOptions['idcat']
            );
        }

        if (isset($aOptions['with_sub_cats']) && $aOptions['with_sub_cats'] === TRUE) {
            foreach ($aOptions['categories'] as $iIdcat) {
                $aTmp = $this->_getSubCats($iIdcat);
                $aOptions['categories'] = array_merge($aOptions['categories'], $aTmp);
            }
        }

        switch ($aOptions['order']) {
            case 'sortsequence':
                $aOptions['order'] = 'artsort';
                break;

            case 'title':
                $aOptions['order'] = 'title';
                break;

            case 'modificationdate':
                $aOptions['order'] = 'lastmodified';
                break;

            case 'publisheddate':
                $aOptions['order'] = 'published';
                break;

            case 'creationdate':
            default:
                $aOptions['order'] = 'created';
                break;
        }

        $this->_aOptions = array_merge($this->_aOptionsDefault, $aOptions);
    }

    /**
     * 
     * @return int article count
     */
    public function count() {
        return count($this->_aArticles);
    }

    /**
     * Returns current element
     * 
     * @return cApiArticleLanguage|int returns article language object or idartlang
     */
    public function current() {
        $iIdartlang = $this->_aArticles[$this->_iCurrentPosition];
        if ($this->_bAsObject) {
            $oArticle = new cApiArticleLanguage($iIdartlang);
            if ($oArticle->isLoaded()) {
                return $oArticle;
            } else {
                throw new Exception(get_class() . ": Cannot load cApiArticleLanguage object for idartlang (" . $iIdartlang . ")!");
            }
        } else {
            return $iIdartlang;
        }
    }
    
    public function getRandomArticle() {
        $iArtCount = $this->count();
        if($iArtCount > 0) {
            if($iArtCount < 2) {
                return $this->current();
            } else {
                $iRand = mt_rand(0, $iArtCount -1);                
                $this->rewind();
                $this->seek($iRand);
                return $this->current();
            }
        }
        return FALSE;
    }

    /**
     * 
     * @return int current position
     */
    public function key() {
        return $this->_iCurrentPosition;
    }

    /**
     * 
     */
    public function next() {
        ++$this->_iCurrentPosition;
    }

    /**
     * set iterator to start position
     */
    public function rewind() {
        $this->_iCurrentPosition = 0;
    }

    /**
     * 
     * @param type $position
     * @throws OutOfBoundsException
     */
    public function seek($position) {
        $this->_iCurrentPosition = $position;

        if ($this->valid() === false) {
            throw new OutOfBoundsException(get_class() . ": Invalid seek position: " . $position);
        }
    }

    /**
     * valid position of iterator
     * 
     * @return boolean
     */
    public function valid() {
        return isset($this->_aArticles[$this->_iCurrentPosition]);
    }

    private function _setOptionsDefault() {
        $this->_aOptionsDefault = array(
            'categories' => array(),
            'lang' => cRegistry::getLanguageId(),
            'client' => cRegistry::getClientId(),
            'start' => FALSE,
            'startonly' => FALSE,
            'offline' => FALSE,
            'offlineonly' => FALSE,
            'artspecs' => array(),
            'direction' => 'DESC',
            'limit' => 0
        );
    }

    /**
     * Returns Array of first leveled SubCats for a given Idcat
     * 
     * @param int $iIdcat idcat to search for subcats
     * @return array
     */
    private function _getSubCats($iIdcat) {
        $aSubCats = array();
        $oCatColl = new cApiCategoryCollection();
        $oCatColl->setWhere("parentid", $iIdcat);
        $oCatColl->query();
        $aTable = $oCatColl->fetchTable();
        //print_r($aTable);

        foreach ($aTable as $aItem) {
            $aSubCats[] = $aItem['idcat'];
        }
        //print_r($aSubCats);
        return $aSubCats;
    }
    
    private function _getStartArticles() {
        $oCatLangColl = new cApiCategoryLanguageCollection();
        $oCatLangColl->setWhere("idlang", $this->_aOptions['lang']);
        if(count($this->_aOptions['categories']) > 0) {
            $oCatLangColl->setWhere("idcat", implode("','", $this->_aOptions['categories']), 'IN');
        }
        $oCatLangColl->query();
        while($oCatLang = $oCatLangColl->next()) {
            $startId = $oCatLang->get('startidartlang');
            if ($startId > 0) {
                $this->_aStartArticles[$oCatLang->get('idcat')] = $startId;
            }            
        }
    }
}
