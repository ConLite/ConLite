<?php

/**
 * Project:
 * Contenido Content Management System
 *
 * Description:
 * API to index a contenido article
 * API to search in the index structure
 * API to display the searchresults
 *
 * Requirements:
 * @con_php_req 5.0
 *
 *
 * @package    Contenido Backend classes
 * @version    1.0.2
 * @author     Willi Man
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.6
 *
 * {@internal
 *   created  2004-01-15
 *   modified 2008-06-30, Frederic Schneider, add security fix
 *   modified 2008-07-11, Dominik Ziegler, marked class search_helper as deprecated
 *   modified 2008-11-12, Andreas Lindner, add special treatment for iso-8859-2
 *   modified 2011-02-08, Murat Purc, removed PHP 4.3 related code, cleanup and formatting, created SearchBaseAbstract class
 *
 *   $Id$:
 * }}
 *
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

/**
 * Abstract base search class. Provides general properties and functions 
 * for child implementations.
 *
 * @author  Murat Purc <murat@purc.de>
 */
abstract class SearchBaseAbstract {

    /**
     * Contenido database object
     * @var DB_ConLite
     */
    protected $oDB;

    /**
     * Contenido configuration data
     * @var array
     */
    protected $cfg;

    /**
     * Language id of a client
     * @var int
     */
    protected $lang;

    /**
     * Client id
     * @var int
     */
    protected $client;

    /**
     * Flag to enable debug
     * @var bool
     */
    protected $bDebug;

    /**
     * Initialises some properties
     *
     * @param  DB_ConLite  $oDB     Optional database instance
     * @param  bool          $bDebug  Optional, flag to enable debugging
     */
    protected function __construct($oDB = null, $bDebug = false) {
        global $cfg, $lang, $client;

        $this->cfg = $cfg;
        $this->lang = $lang;
        $this->client = $client;

        $this->setDebug((bool) $bDebug);

        if ($oDB == null) {
            $this->db = new DB_ConLite();
        } elseif (is_object($oDB)) {
            $this->db = $oDB;
        }
    }

    /**
     * Setter for debug
     *
     * @param  bool $bDebug
     */
    public function setDebug($bDebug) {
        $this->bDebug = (bool) $bDebug;
    }

    /**
     * Main debug function, prints dumps parameter if debugging is enabled
     *
     * @param  string  $msg  Some text
     * @param  mixed   $var  The variable to dump
     */
    protected function _debug($msg, $var) {
        if (!$this->bDebug) {
            return;
        }
        $dump = '<pre>' . $msg . ': ';
        if (is_array($var) || is_object($var)) {
            $dump .= print_r($var, true);
        } else {
            $dump .= $var;
        }
        $dump .= '</pre>' . "\n";
        echo $dump;
    }

}

/**
 * Contenido API - Index Object
 *
 * This object creates an index of an article
 *
 * Create object with
 * $oIndex = new Index($db); # where $db is the global Contenido database object.
 * Start indexing with
 * $oIndex->start($idart, $aContent);
 * where $aContent is the complete content of an article specified by its content types.
 * It looks like
 * Array (
 *     [CMS_HTMLHEAD] => Array (
 *         [1] => Herzlich Willkommen...
 *         [2] => ...auf Ihrer Website!
 *     )
 *     [CMS_HTML] => Array (
 *         [1] => Die Inhalte auf dieser Website ...
 *
 * The index for keyword 'willkommen' would look like '&12=1(CMS_HTMLHEAD-1)' which means the keyword 'willkommen' occurs 1 times in article with articleId 12 and content type CMS_HTMLHEAD[1].
 *
 * TODO: The basic idea of the indexing process is to take the complete content of an article and to generate normalized index terms
 * from the content and to store a specific index structure in the relation 'con_keywords'.
 * To take the complete content is not very flexible. It would be better to differentiate by specific content types or by any content.
 * The &, =, () and - seperated string is not easy to parse to compute the search result set.
 * It would be a better idea (and a lot of work) to extend the relation 'con_keywords' to store keywords by articleId (or content source identifier) and content type.
 * The functions removeSpecialChars, setStopwords, setContentTypes and setCmsOptions should be sourced out into a new helper-class.
 * Keep in mind that class Search and SearchResult uses an instance of object Index.
 * Consider character tables in relation 'con_chartable'.
 */
cInclude('includes', 'functions.encoding.php');

class Index extends SearchBaseAbstract {

    /**
     * the content of the cms-types of an article
     * @var array
     */
    var $keycode = array();

    /**
     * the list of keywords of an article
     * @var array
     */
    var $keywords = array();

    /**
     * the words, which should not be indexed
     * @var array
     */
    var $stopwords = array();

    /**
     * the keywords of an article stored in the DB
     * @var array
     */
    var $keywords_old = array();

    /**
     * the keywords to be deleted
     * @var array
     */
    var $keywords_del = array();

    /**
     * 'auto' or 'self'
     * The field 'auto' in table con_keywords is used for automatic indexing.
     * The value is a string like "&12=2(CMS_HTMLHEAD-1,CMS_HTML-1)", which means a keyword occurs 2 times in article with $idart 12
     * and can be found in CMS_HTMLHEAD[1] and CMS_HTML[1].
     * The field 'self' can be used in the article properties to index the article manually.
     * @var string
     */
    var $place;

    /**
     * array of cms types
     * @var array
     */
    var $cms_options = array();

    /**
     * array of all available cms types
     *
     * htmlhead      - HTML Headline
     * html          - HTML Text
     * head          - Headline (no HTML)
     * text          - Text (no HTML)
     * img           - Upload id of the element
     * imgdescr      - Image description
     * link          - Link (URL)
     * linktarget    - Linktarget (_self, _blank, _top ...)
     * linkdescr     - Linkdescription
     * swf           - Upload id of the element
     * etc.
     *
     * @var array
     */
    protected static $_cms_type = [];

    /**
     * the suffix of all available cms types
     * @var array
     */
    protected static $_cms_type_suffix = [];

    /**
     * Constructor, set object properties
     * @param  DB_ConLite  $oDB  Contenido Database object
     * @return void
     */
    function __construct($oDB = null) {
        parent::__construct($oDB);

        $this->setContentTypes();
    }

    /**
     * Start indexing the article.
     *
     * @param   int    $idart  Article Id
     * @param   array  $aContent  The complete content of an article specified by its content types.
     * It looks like
     * Array (
     *     [CMS_HTMLHEAD] => Array (
     *         [1] => Herzlich Willkommen...
     *         [2] => ...auf Ihrer Website!
     *     )
     *     [CMS_HTML] => Array (
     *         [1] => Die Inhalte auf dieser Website ...
     *
     * @param   string  $place  The field where to store the index information in db.
     * @param   array   $cms_options  One can specify explicitly cms types which should not be indexed.
     * @param   array   $aStopwords  Array with words which should not be indexed.
     * @return  void
     */
    function start($idart, $aContent, $place = 'auto', $cms_options = array(), $aStopwords = array()) {
        if (!is_int((int) $idart) || $idart < 0) {
            return null;
        } else {
            $this->idart = $idart;
        }

        $this->_debug('Start Index for ', $this->idart);

        $this->place = $place;
        $this->keycode = $aContent;
        $this->setStopwords($aStopwords);
        $this->setCmsOptions($cms_options);

        $this->createKeywords();
        $this->getKeywords();
        $this->saveKeywords();

        $new_keys = array_keys($this->keywords);
        $old_keys = array_keys($this->keywords_old);

        $this->keywords_del = array_diff($old_keys, $new_keys);
        /*
          echo '<pre>';
          print_r($new_keys);
          print_r($old_keys);
          print_r($this->keywords_del);
          echo '</pre>';
         * 
         */
        if (count($this->keywords_del) > 0) {
            $this->deleteKeywords();
        }
    }

    /**
     * for each cms-type create index structure.
     * it looks like
     * Array (
     *     [die] => CMS_HTML-1
     *     [inhalte] => CMS_HTML-1
     *     [auf] => CMS_HTML-1 CMS_HTMLHEAD-2
     *     [dieser] => CMS_HTML-1
     *     [website] => CMS_HTML-1 CMS_HTML-1 CMS_HTMLHEAD-2
     * )
     *
     * @param none
     * @return void
     */
    function createKeywords() {
        $tmp_keys = array();
        $replace = array('&nbsp;', '&amp;', '&lt;', '&gt;', '&quot;', '&#039;');

        // Only create keycodes, if some are available
        if (is_array($this->keycode)) {
            foreach ($this->keycode as $idtype => $data) {
                if ($this->checkCmsType($idtype)) {
                    foreach ($data as $typeid => $code) {
                        $this->_debug('createKeywords: raw code from data array', $code);

                        $code = stripslashes($code); // remove backslash
                        $code = str_ireplace(array('<br>', '<br />'), "\n", $code); // replace HTML line breaks with newlines
                        $code = strip_tags($code); // remove html tags
                        if (strlen($code) > 0) {
                            $code = clHtmlEntityDecode($code);
                        }
                        $this->_debug('createKeywords: code after clean', $code);

                        $tmp_keys = preg_split('/[\s,]+/', trim($code)); // split content by any number of commas or space characters
                        $this->_debug('createKeywords: tmp_keys', $tmp_keys);

                        foreach ($tmp_keys as $value) {
                            $value = strtolower($value); // index terms are stored with lower case
                            $value = preg_replace('/[^\w]+/u', '', $value);

                            if (empty(trim($value))) {
                                continue;
                            }

                            if (!in_array($value, $this->stopwords)) {
                                // eliminate stopwords
                                $value = $this->removeSpecialChars($value);

                                if (strlen($value) > 1) {
                                    // do not index single characters
                                    $this->keywords[$value] = $this->keywords[$value] . $idtype . '-' . $typeid . ' ';
                                    $this->_debug('createKeywords: entry array keywords', $this->keywords);
                                }
                            }
                        }
                    }
                }

                unset($tmp_keys);
            }
        }

        $this->_debug('createKeywords: keywords returned', $this->keywords);
    }

    /**
     * generate index_string from index structure and save keywords
     * The index_string looks like "&12=2(CMS_HTMLHEAD-1,CMS_HTML-1)"
     * @return void
     */
    function saveKeywords() {
        $tmp_count = array();

        foreach ($this->keywords as $keyword => $count) {
            $bProceed = true;
            $this->_debug('keyword', $keyword);
            $tmp_count = preg_split('/[\s]/', trim($count));
            $this->_debug('tmp_count', $tmp_count);
            $occurrence = count($tmp_count);
            $tmp_count = array_unique($tmp_count);
            $cms_types = implode(',', $tmp_count);
            $index_string = '&' . $this->idart . '=' . $occurrence . '(' . $cms_types . ')';

            if (!array_key_exists($keyword, $this->keywords_old)) {
                // if keyword is new, save index information

                $nextid = $this->db->nextid($this->cfg['tab']['keywords']);

                $sql = "INSERT INTO " . $this->cfg['tab']['keywords'] . "
                            (keyword, " . $this->place . ", idlang, idkeyword)
                        VALUES
                            ('" . Contenido_Security::escapeDB($keyword, $this->db) . "', '" . Contenido_Security::escapeDB($index_string, $this->db) . "', " . Contenido_Security::toInteger($this->lang) . ", " . Contenido_Security::toInteger($nextid) . ")";
            } else {
                // if keyword allready exists, create new index_string
                if (preg_match("/&" . $this->idart . "=/", $this->keywords_old[$keyword])) {
                    $index_string = preg_replace("/&" . $this->idart . "=[0-9]+\([,\w-]+\)/", $index_string, $this->keywords_old[$keyword]);
                    if ($index_string === $this->keywords_old[$keyword]) {
                        $bProceed = false;
                        $this->_debug('db update', 'no update needed');
                    }
                } else {
                    $index_string = $this->keywords_old[$keyword] . $index_string;
                }

                $sql = "UPDATE " . $this->cfg['tab']['keywords'] . "
                        SET " . $this->place . " = '" . $index_string . "'
                        WHERE idlang='" . Contenido_Security::toInteger($this->lang) . "' AND keyword='" . Contenido_Security::escapeDB($keyword, $this->db) . "'";
            }

            if ($bProceed) {
                $this->_debug('sql', $sql);
                $this->db->query($sql);
            }
        }
    }

    /**
     * if keywords don't occur in the article anymore, update index_string and delete keyword if necessary
     * @param none
     * @return void
     */
    function deleteKeywords() {
        foreach ($this->keywords_del as $key_del) {
            $index_string = preg_replace("/&$this->idart=[0-9]+\([\w-,]+\)/", "", $this->keywords_old[$key_del]);

            if (strlen($index_string) == 0) {
                // keyword is not referenced by any article
                $sql = "DELETE FROM " . $this->cfg['tab']['keywords'] . "
                    WHERE idlang='" . Contenido_Security::toInteger($this->lang) . "' AND keyword='" . Contenido_Security::escapeDB($key_del, $this->db) . "'";
            } else {
                $sql = "UPDATE " . $this->cfg['tab']['keywords'] . "
                    SET " . $this->place . " = '" . $index_string . "'
                    WHERE idlang='" . Contenido_Security::toInteger($this->lang) . "' AND keyword='" . Contenido_Security::escapeDB($key_del, $this->db) . "'";
            }
            $this->_debug('sql', $sql);
            $this->db->query($sql);
        }
    }

    /**
     * get the keywords of an article
     * @param none
     * @return void
     */
    function getKeywords() {
        $keys = implode("','", array_keys($this->keywords));

        $sql = "SELECT
                    keyword, auto, self
                FROM
                    " . $this->cfg['tab']['keywords'] . "
                WHERE
                    idlang=" . Contenido_Security::toInteger($this->lang) . "  AND
                    (keyword IN ('" . $keys . "')  OR " . $this->place . " REGEXP '&" . Contenido_Security::toInteger($this->idart) . "=')";

        $this->_debug('getKeywords: sql', $sql);

        $this->db->query($sql);

        $place = $this->place;

        while ($this->db->next_record()) {
            $this->keywords_old[$this->db->f('keyword')] = $this->db->f($place);
        }

        $this->_debug('getKeywords: array keywords_old', $this->keywords_old);
    }

    /**
     * remove special characters from index term
     * @param $key Keyword
     * @return $key
     */
    function removeSpecialChars($key) {

        $aSpecialChars = array(
            "-", "_", "'", ".", "!", "\"", "#", "$", "%", "&", "(", ")", "*", "+", ",", "/",
            ":", ";", "<", "=", ">", "?", "@", "[", "\\", "]", "^", "`", "{", "|", "}", "~"
        );

        for ($i = 127; $i < 192; $i++) {
            array_push($aSpecialChars, chr($i));  // some other special characters
        }

        // TODO: The transformation of accented characters must depend on the selected encoding of the language of
        // a client and should not be treated in this method.
        // modified 2007-10-01, H. Librenz - added as hotfix for encoding problems (doesn't find any words with
        //                                      umlaut vowels in it since you turn on UTF-8 as language encoding)

        $sEncoding = getEncodingByLanguage($this->db, $this->lang, $this->cfg);

        if (strtolower($sEncoding) != 'iso-8859-2') {
            $key = clHtmlEntities($key, NULL, $sEncoding);
        } else {
            $key = htmlentities_iso88592($key);
        }

        $aUmlautMap = array(
            '&Uuml;' => 'ue',
            '&uuml;' => 'ue',
            '&Auml;' => 'ae',
            '&auml;' => 'ae',
            '&Ouml;' => 'oe',
            '&ouml;' => 'oe',
            '&szlig;' => 'ss'
        );

        foreach ($aUmlautMap as $sUmlaut => $sMapped) {
            $key = str_replace($sUmlaut, $sMapped, $key);
        }

        $key = clHtmlEntityDecode($key);
        $key = str_replace($aSpecialChars, '', $key);

        ini_set('mbstring.substitute_character', "none");
        $key = mb_convert_encoding($key, 'UTF-8', 'UTF-8');

        return $key;
    }

    /**
     * @modified 2008-04-17, Timo Trautmann - reverse function to removeSpecialChars
     *                                        (important for syntaxhighlighting searchterm in searchresults)
     * adds umlauts to search term
     * @param $key Keyword
     * @return $key
     */
    function addSpecialUmlauts($key) {
        $key = clHtmlEntities($key, null, getEncodingByLanguage($this->db, $this->lang, $this->cfg));
        $aUmlautMap = array(
            'ue' => '&Uuml;',
            'ue' => '&uuml;',
            'ae' => '&Auml;',
            'ae' => '&auml;',
            'oe' => '&Ouml;',
            'oe' => '&ouml;',
            'ss' => '&szlig;'
        );

        foreach ($aUmlautMap as $sUmlaut => $sMapped) {
            $key = str_replace($sUmlaut, $sMapped, $key);
        }

        $key = clHtmlEntityDecode($key);
        return $key;
    }

    /**
     * 
     * @return array array with arrays of type and typesuffix
     */
    public function getContentTypes(): array {
        if (empty(self::$_cms_type)) {
            $this->setContentTypes();
        }

        return array(
            'cms_type' => self::$_cms_type,
            'cms_type_suffix' => self::$_cms_type_suffix
        );
    }

    /**
     * set the array of stopwords which should not be indexed
     * @param array $aStopwords
     * @return void
     */
    function setStopwords($aStopwords) {
        if (is_array($aStopwords) && count($aStopwords) > 0) {
            $this->stopwords = $aStopwords;
        }
    }

    /**
     * set the cms types
     * @param none
     * @return void
     */
    function setContentTypes() {
        $sql = "SELECT type, idtype FROM " . $this->cfg['tab']['type'] . ' ';
        $this->_debug('sql', $sql);
        $this->db->query($sql);
        while ($this->db->next_record()) {
            self::$_cms_type[$this->db->f('type')] = $this->db->f('idtype');
            self::$_cms_type_suffix[$this->db->f('idtype')] = substr($this->db->f('type'), 4, strlen($this->db->f('type')));
        }
    }

    /**
     * set the cms_options array of cms types which should be treated special
     * @param none
     * @return void
     */
    function setCmsOptions($cms_options) {
        if (is_array($cms_options) && count($cms_options) > 0) {
            foreach ($cms_options as $opt) {
                $opt = strtoupper($opt);

                if (strlen($opt) > 0) {
                    if (!stristr($opt, 'cms_')) {
                        if (in_array($opt, $this->getContentTypes()['cms_type_suffix'])) {
                            $this->cms_options[$opt] = 'CMS_' . $opt;
                        }
                    } else {
                        if (array_key_exists($opt, $this->getContentTypes()['cms_type'])) {
                            $this->cms_options[$opt] = $opt;
                        }
                    }
                }
            }
        } else {
            $this->cms_options = array();
        }
    }

    /**
     * check if the current cms type is in the cms_options array
     * @param $idtype
     *
     * @return bolean
     */
    function checkCmsType($idtype) {
        $idtype = strtoupper($idtype);
        return (in_array($idtype, $this->cms_options)) ? false : true;
    }

}

/**
 * Contenido API - Search Object
 *
 * This object starts a indexed fulltext search
 *
 * TODO:
 * The way to set the search options could be done much more better!
 * The computation of the set of searchable articles should not be treated in this class.
 * It is better to compute the array of searchable articles from the outside and to pass the array of searchable articles as parameter.
 * Avoid foreach loops.
 *
 * Use object with
 *
 * $options = array('db' => 'regexp', // use db function regexp
 *                  'combine' => 'or'); // combine searchwords with or
 *
 * The range of searchable articles is by default the complete content which is online and not protected.
 *
 * With option 'searchable_articles' you can define your own set of searchable articles.
 * If parameter 'searchable_articles' is set the options 'cat_tree', 'categories', 'articles', 'exclude', 'artspecs',
 * 'protected', 'dontshowofflinearticles' don't have any effect.
 *
 * $options = array('db' => 'regexp', // use db function regexp
 *                  'combine' => 'or', // combine searchwords with or
 *                  'searchable_articles' => array(5, 6, 9, 13));
 *
 * One can define the range of searchable articles by setting the parameter 'exclude' to false which means the range of categories
 * defined by parameter 'cat_tree' or 'categories' and the range of articles defined by parameter 'articles' is included.
 *
 * $options = array('db' => 'regexp', // use db function regexp
 *                  'combine' => 'or', // combine searchwords with or
 *                  'exclude' => false, // => searchrange specified in 'cat_tree', 'categories' and 'articles' is included
 *                  'cat_tree' => array(12), // tree with root 12 included
 *                  'categories' => array(100,111), // categories 100, 111 included
 *                  'articles' => array(33), // article 33 included
 *                  'artspecs' => array(2, 3), // array of article specifications => search only articles with these artspecs
 *                  'res_per_page' => 2, // results per page
 *                  'protected' => true); // => do not search articles or articles in categories which are offline or protected
 *                  'dontshowofflinearticles' => false); // => search offline articles or articles in categories which are offline
 *
 * You can build the complement of the range of searchable articles by setting the parameter 'exclude' to true which means the range of categories
 * defined by parameter 'cat_tree' or 'categories' and the range of articles defined by parameter 'articles' is excluded from search.
 *
 * $options = array('db' => 'regexp', // use db function regexp
 *                  'combine' => 'or', // combine searchwords with or
 *                  'exclude' => true, // => searchrange specified in 'cat_tree', 'categories' and 'articles' is excluded
 *                  'cat_tree' => array(12), // tree with root 12 excluded
 *                  'categories' => array(100,111), // categories 100, 111 excluded
 *                  'articles' => array(33), // article 33 excluded
 *                  'artspecs' => array(2, 3), // array of article specifications => search only articles with these artspecs
 *                  'res_per_page' => 2, // results per page
 *                  'protected' => true); // => do not search articles or articles in categories which are offline or protected
 *                  'dontshowofflinearticles' => false); // => search offline articles or articles in categories which are offline
 *
 * $search = new Search($options);
 *
 * $cms_options = array("htmlhead", "html", "head", "text", "imgdescr", "link", "linkdescr");
 * search only in these cms-types
 * $search->setCmsOptions($cms_options);
 *
 * $search_result = $search->searchIndex($searchword, $searchwordex); // start search
 *
 * The search result structure has following form
 *    Array (
 *        [20] => Array (
 *            [CMS_HTML] => Array (
 *                [0] => 1
 *                [1] => 1
 *                [2] => 1
 *            )
 *            [keyword] => Array (
 *                [0] => content
 *                [1] => contenido
 *                [2] => wwwcontenidoorg
 *            )
 *            [search] => Array (
 *                [0] => con
 *                [1] => con
 *                [2] => con
 *            )
 *            [occurence] => Array (
 *                [0] => 1
 *                [1] => 5
 *                [2] => 1
 *            )
 *            [similarity] => 60
 *        )
 *    )
 *
 * The keys of the array are the article ID's found by search.
 *
 * Searching 'con' matches keywords 'content', 'contenido' and 'wwwcontenidoorg' in article with ID 20 in content type CMS_HTML[1].
 * The search term occurs 7 times.
 * The maximum similarity between searchterm and matching keyword is 60%.
 *
 * with $oSearchResults = new SearchResult($search_result, 10);
 * one can rank and display the results
 *
 * @version 1.0.1
 *
 * @author Willi Man
 * @copyright four for business AG <www.4fb.de>
 */
class Search extends SearchBaseAbstract {

    /**
     * Instance of class Index
     * @var object
     */
    var $index;

    /**
     * array of available cms types
     * @var array
     */
    var $cms_type = array();

    /**
     * suffix of available cms types
     * @var array
     */
    var $cms_type_suffix = array();

    /**
     * the search words
     * @var array
     */
    var $search_words = array();

    /**
     * the words which should be excluded from search
     * @var array
     */
    var $search_words_exclude = array();

    /**
     * type of db search
     * like => 'sql like', regexp => 'sql regexp'
     * @var string
     */
    var $search_option;

    /**
     * logical combination of searchwords (and, or)
     * @var string
     */
    var $search_combination;

    /**
     * array of searchable articles
     * @var array
     */
    var $searchable_arts = array();

    /**
     * article specifications
     * @var array
     */
    var $article_specs = array();

    /**
     * If $protected = true => do not search articles which are offline or articles in catgeories which are offline (protected)
     * @var boolean
     */
    var $protected;

    /**
     * If $dontshowofflinearticles = false => search offline articles or articles in categories which are offline
     * @var boolean
     */
    var $dontshowofflinearticles;

    /**
     * If $exclude = true => the specified search range is excluded from search, otherwise included
     * @var boolean
     */
    var $exclude;

    /**
     * Array of article id's with information about cms-types, occurence of keyword/searchword, similarity ...
     * @var array
     */
    var $search_result = array();

    /**
     * Constructor
     *
     * @param array $options
     *    $options['db'] 'regexp' => DB search with REGEXP; 'like' => DB search with LIKE; 'exact' => exact match;
     *    $options['combine'] 'and', 'or' Combination of search words with AND, OR
     *    $options['exclude'] 'true'  => searchrange specified in 'cat_tree', 'categories' and 'articles' is excluded; 'false' => searchrange specified in 'cat_tree', 'categories' and 'articles' is included
     *    $options['cat_tree']  e.g. array(8) => The complete tree with root 8 is in/excluded from search
     *    $options['categories'] e.g. array(10, 12) => Categories 10, 12 in/excluded
     *    $options['articles'] e.g. array(23) => Article 33 in/excluded
     *    $options['artspecs'] => e.g. array(2, 3) => search only articles with certain article specifications
     *    $options['protected'] 'true' => do not search articles which are offline (locked) or articles in catgeories which are offline (protected)
     *    $options['dontshowofflinearticles'] 'false' => search offline articles or articles in categories which are offline
     *    $options['searchable_articles'] array of article ID's which should be searchable
     * @param  DB_ConLite  $oDB  Optional database instance
     * @return void
     */
    function __construct($options, $oDB = null) {
        parent::__construct($oDB);

        $this->index = new Index($oDB);

        $this->cms_type = $this->index->getContentTypes()['cms_type'];
        $this->cms_type_suffix = $this->index->getContentTypes()['cms_type_suffix'];

        $this->search_option = (array_key_exists('db', $options)) ? strtolower($options['db']) : 'regexp';
        $this->search_combination = (array_key_exists('combine', $options)) ? strtolower($options['combine']) : 'or';
        $this->protected = (array_key_exists('protected', $options)) ? $options['protected'] : true;
        $this->dontshowofflinearticles = (array_key_exists('dontshowofflinearticles', $options)) ? $options['dontshowofflinearticles'] : false;
        $this->exclude = (array_key_exists('exclude', $options)) ? $options['exclude'] : true;
        $this->article_specs = (array_key_exists('artspecs', $options) && is_array($options['artspecs'])) ? $options['artspecs'] : array();
        $this->index->setCmsOptions($this->cms_type_suffix);

        if (array_key_exists('searchable_articles', $options) && is_array($options['searchable_articles'])) {
            $this->searchable_arts = $options['searchable_articles'];
        } else {
            $this->searchable_arts = $this->getSearchableArticles($options);
        }

        $this->intMinimumSimilarity = 50; # minimum similarity between searchword and keyword in percent
    }

    /**
     * indexed fulltext search
     * @param string $searchwords The search words
     * @param string $searchwords_exclude The words, which should be excluded from search
     * @return void
     */
    function searchIndex($searchwords, $searchwords_exclude = '') {
        if (strlen(trim($searchwords)) > 0) {
            $this->search_words = $this->stripWords($searchwords);
        } else {
            return false;
        }

        if (strlen(trim($searchwords_exclude)) > 0) {
            $this->search_words_exclude = $this->stripWords($searchwords_exclude);
        }

        $tmp_searchwords = array();
        foreach ($this->search_words as $word) {
            if ($this->search_option == 'like') {
                $word = "'%" . $word . "%'";
            } elseif ($this->search_option == 'exact') {
                $word = "'" . $word . "'";
            }
            array_push($tmp_searchwords, $word);
        }

        if (count($this->search_words_exclude) > 0) {
            foreach ($this->search_words_exclude as $word) {
                if ($this->search_option == 'like') {
                    $word = "'%" . $word . "%'";
                } elseif ($this->search_option == 'exact') {
                    $word = "'" . $word . "'";
                }
                array_push($tmp_searchwords, $word);
                array_push($this->search_words, $word);
            }
        }

        if (count($tmp_searchwords) == 0)
            return false;

        if ($this->search_option == 'regexp') {
            // regexp search
            $kwSql = "keyword REGEXP '" . implode('|', $tmp_searchwords) . "'";
        } elseif ($this->search_option == 'like') {
            // like search
            $search_like = implode(" OR keyword LIKE ", Contenido_Security::escapeDB($tmp_searchwords, $this->db));
            $kwSql = "keyword LIKE '" . $search_like;
        } elseif ($this->search_option == 'exact') {
            // exact match
            $search_exact = implode(" OR keyword = ", Contenido_Security::escapeDB($tmp_searchwords, $this->db));
            $kwSql = "keyword LIKE '" . $search_exact;
        }

        $sql = "SELECT keyword, auto FROM " . $this->cfg['tab']['keywords']
                . " WHERE idlang=" . Contenido_Security::toInteger($this->lang) . " AND " . $kwSql . " ";
        $this->_debug('sql', $sql);
        $this->db->query($sql);

        while ($this->db->next_record()) {

            $tmp_index_string = preg_split('/&/', $this->db->f('auto'), -1, PREG_SPLIT_NO_EMPTY);

            $this->_debug('index', $this->db->f('auto'));

            $tmp_index = array();
            foreach ($tmp_index_string as $string) {
                $tmp_string = preg_replace('/[=\(\)]/', ' ', $string);
                $tmp_index[] = preg_split('/\s/', $tmp_string, -1, PREG_SPLIT_NO_EMPTY);
            }
            $this->_debug('tmp_index', $tmp_index);

            foreach ($tmp_index as $string) {
                $artid = $string[0];

                // filter nonsearchable articles
                if (in_array($artid, $this->searchable_arts)) {

                    $cms_place = $string[2];
                    $keyword = $this->db->f('keyword');
                    $percent = 0;
                    $similarity = 0;
                    foreach ($this->search_words as $word) {
                        similar_text($word, $keyword, $percent); // computes similarity between searchword and keyword in percent
                        if ($percent > $similarity) {
                            $similarity = $percent;
                            $searchword = $word;
                        }
                    }

                    $tmp_cmstype = preg_split('/[,]/', $cms_place, -1, PREG_SPLIT_NO_EMPTY);
                    $this->_debug('tmp_cmstype', $tmp_cmstype);

                    $tmp_cmstype2 = array();
                    foreach ($tmp_cmstype as $type) {
                        $tmp_cmstype2[] = preg_split('/-/', $type, -1, PREG_SPLIT_NO_EMPTY);
                    }
                    $this->_debug('tmp_cmstype2', $tmp_cmstype2);

                    foreach ($tmp_cmstype2 as $type) {
                        if (!$this->index->checkCmsType($type[0])) {
                            // search for specified cms-types
                            if ($similarity >= $this->intMinimumSimilarity) {
                                // include article into searchresult set only if 
                                // similarity between searchword and keyword is big enough
                                $this->search_result[$artid][$type[0]][] = $type[1];
                                $this->search_result[$artid]['keyword'][] = $this->db->f('keyword');
                                $this->search_result[$artid]['search'][] = $searchword;
                                $this->search_result[$artid]['occurence'][] = $string[1];
                                $this->search_result[$artid]['debug_similarity'][] = $percent;
                                if ($similarity > $this->search_result[$artid]['similarity']) {
                                    $this->search_result[$artid]['similarity'] = $similarity;
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($this->search_combination == 'and') {
            // all search words must appear in the article
            foreach ($this->search_result as $article => $val) {
                if (!count(array_diff($this->search_words, $val['search'])) == 0) {
                    //$this->rank_structure[$article] = $rank[$article];
                    unset($this->search_result[$article]);
                }
            }
        }

        if (count($this->search_words_exclude) > 0) {
            // search words to be excluded must not appear in article
            foreach ($this->search_result as $article => $val) {
                if (!count(array_intersect($this->search_words_exclude, $val['search'])) == 0) {
                    //$this->rank_structure[$article] = $rank[$article];
                    unset($this->search_result[$article]);
                }
            }
        }

        $this->_debug('$this->search_result', $this->search_result);
        $this->_debug('$this->searchable_arts', $this->searchable_arts);

        return $this->search_result;
    }

    /**
     * @param $cms_options The cms-types (htmlhead, html, ...) which should explicitly be searched
     * @return void
     */
    function setCmsOptions($cms_options) {
        if (is_array($cms_options) && count($cms_options) > 0) {
            $this->index->setCmsOptions($cms_options);
        }
    }

    /**
     * @param $searchwords The search-words
     * @return Array of stripped search-words
     */
    function stripWords($searchwords) {
        $tmp_words = array();
        $searchwords = stripslashes($searchwords); // remove backslash
        $searchwords = strip_tags($searchwords); // remove html tags

        $tmp_words = preg_split('/[\s,]+/', trim($searchwords)); // split the phrase by any number of commas or space characters

        $tmp_searchwords = array();

        foreach ($tmp_words as $word) {
            $word = strtolower($word);
            $word = $this->index->removeSpecialChars(trim($word));
            if (strlen($word) > 1) {
                array_push($tmp_searchwords, $word);
            }
        }

        return array_unique($tmp_searchwords);
    }

    /**
     * Returns the category tree array.
     *
     * @param  int    $cat_start  Root of a category tree
     * @return array  Category Tree
     * @todo   This is not the job for search, should be oursourced...
     */
    function getSubTree($cat_start) {
        $sql = "SELECT
                B.idcat, B.parentid
            FROM
                " . $this->cfg['tab']['cat_tree'] . " AS A,
                " . $this->cfg['tab']['cat'] . " AS B,
                " . $this->cfg['tab']['cat_lang'] . " AS C
            WHERE
                A.idcat  = B.idcat AND
                B.idcat  = C.idcat AND
                C.idlang = '" . Contenido_Security::toInteger($this->lang) . "' AND
                B.idclient = '" . Contenido_Security::toInteger($this->client) . "'
            ORDER BY
                idtree";
        $this->_debug('sql', $sql);
        $this->db->query($sql);

        $aSubCats = array();
        $i = false;

        while ($this->db->next_record()) {
            if ($this->db->f('parentid') < $cat_start) {
                // ending part of tree
                $i = false;
            }

            if ($this->db->f('idcat') == $cat_start) {
                // starting part of tree
                $i = true;
            }

            if ($i == true) {
                $aSubCats[] = $this->db->f('idcat');
            }
        }
        return $aSubCats;
    }

    /**
     * Returns list of searchable article ids.
     *
     * @param   array  $search_range
     * @return  array  Articles in specified search range
     */
    function getSearchableArticles($search_range) {

        $cat_range = array();
        if (array_key_exists('cat_tree', $search_range) && is_array($search_range['cat_tree'])) {
            if (count($search_range['cat_tree']) > 0) {
                foreach ($search_range['cat_tree'] as $cat) {
                    $cat_range = array_merge($cat_range, $this->getSubTree($cat));
                }
            }
        }

        if (array_key_exists('categories', $search_range) && is_array($search_range['categories'])) {
            if (count($search_range['categories']) > 0) {
                $cat_range = array_merge($cat_range, $search_range['categories']);
            }
        }

        $cat_range = array_unique($cat_range);
        $sCatRange = implode("','", $cat_range);

        if (array_key_exists('articles', $search_range) && is_array($search_range['articles'])) {
            if (count($search_range['articles']) > 0) {
                $sArtRange = implode("','", $search_range['articles']);
            } else {
                $sArtRange = '';
            }
        }

        $id_arts = array();

        if ($this->protected == true) {
            $protected = " C.public = '1' AND C.visible = '1' AND B.online = '1' ";
        } else {
            if ($this->dontshowofflinearticles == true) {
                $protected = " C.visible = '1' AND B.online = '1' ";
            } else {
                $protected = " 1 ";
            }
        }

        if ($this->exclude == true) {
            // exclude searchrange
            $sSearchRange = " A.idcat NOT IN  ('" . $sCatRange . "') AND B.idart NOT IN  ('" . $sArtRange . "') AND ";
        } else {
            // include searchrange
            if (strlen($sArtRange) > 0) {
                $sSearchRange = " A.idcat IN  ('" . $sCatRange . "') AND B.idart IN  ('" . $sArtRange . "') AND ";
            } else {
                $sSearchRange = " A.idcat IN  ('" . $sCatRange . "') AND ";
            }
        }

        if (count($this->article_specs) > 0) {
            $sArtSpecs = " B.artspec IN ('" . implode("','", $this->article_specs) . "') AND ";
        } else {
            $sArtSpecs = '';
        }

        $sql = "SELECT
                    A.idart
                FROM
                    " . $this->cfg["tab"]["cat_art"] . " as A,
                    " . $this->cfg["tab"]["art_lang"] . " as B,
                    " . $this->cfg["tab"]["cat_lang"] . " as C
                WHERE
                    " . $sSearchRange . "
                    B.idlang = '" . Contenido_Security::toInteger($this->lang) . "' AND
                    C.idlang = '" . Contenido_Security::toInteger($this->lang) . "' AND
                    A.idart = B.idart AND
                    A.idcat = C.idcat AND
                    " . $sArtSpecs . "
                    " . $protected . " ";
        $this->_debug('sql', $sql);
        $this->db->query($sql);
        while ($this->db->next_record()) {
            $id_arts[] = $this->db->f('idart');
        }
        return $id_arts;
    }

    /**
     * Fetch all article specifications which are online,
     *
     * @return  array  Array of article specification Ids
     */
    function getArticleSpecifications() {
        $sql = "SELECT
                    idartspec
                FROM
                    " . $this->cfg['tab']['art_spec'] . "
                WHERE
                    client = " . Contenido_Security::toInteger($this->client) . " AND
                    lang = " . Contenido_Security::toInteger($this->lang) . " AND
                    online = 1 ";
        $this->_debug('sql', $sql);
        $this->db->query($sql);
        $aArtspec = array();
        while ($this->db->next_record()) {
            $aArtspec[] = $this->db->f('idartspec');
        }
        return $aArtspec;
    }

    /**
     * Set article specification
     * @param  int  $iArtspecID
     * @return void
     */
    function setArticleSpecification($iArtspecID) {
        array_push($this->article_specs, $iArtspecID);
    }

    /**
     * Add all article specifications matching name of article specification (client dependent but language independent)
     * @param  string  $sArtSpecName
     * @return void
     */
    function addArticleSpecificationsByName($sArtSpecName) {
        if (!isset($sArtSpecName) || strlen($sArtSpecName) == 0) {
            return false;
        }

        $sql = "SELECT
                    idartspec
                FROM
                    " . $this->cfg['tab']['art_spec'] . "
                WHERE
                    client = " . Contenido_Security::toInteger($this->client) . " AND
                    artspec = '" . Contenido_Security::escapeDB($sArtSpecName, $this->db) . "' ";
        $this->_debug('sql', $sql);
        $this->db->query($sql);
        while ($this->db->next_record()) {
            array_push($this->article_specs, $this->db->f('idartspec'));
        }
    }

}

/**
 * Contenido API - SearchResult Object
 *
 * This object ranks and displays the result of the indexed fulltext search.
 * If you are not comfortable with this API feel free to use your own methods to display the search results.
 * The search result is basically an array with article ID's.
 *
 * If $search_result = $search->searchIndex($searchword, $searchwordex);
 *
 * use object with
 *
 * $oSearchResults = new SearchResult($search_result, 10);
 *
 * $oSearchResults->setReplacement('<span style="color:red">', '</span>'); // html-tags to emphasize the located searchwords
 *
 * $num_res = $oSearchResults->getNumberOfResults();
 * $num_pages = $oSearchResults->getNumberOfPages();
 * $res_page = $oSearchResults->getSearchResultPage(1); // first result page
 * foreach ($res_page as $key => $val) {
 *     $headline = $oSearchResults->getSearchContent($key, 'HTMLHEAD');
 *     $first_headline = $headline[0];
 *     $text = $oSearchResults->getSearchContent($key, 'HTML');
 *     $first_text = $text[0];
 *     $similarity = $oSearchResults->getSimilarity($key);
 *     $iOccurrence = $oSearchResults->getOccurrence($key);
 * }
 *
 * @version 1.0.0
 *
 * @author Willi Man
 * @copyright four for business AG <www.4fb.de>
 *
 */
class SearchResult extends SearchBaseAbstract {

    /**
     * Instance of class Index
     * @var object
     */
    var $index;

    /**
     * Number of results
     * @var int
     */
    var $results;

    /**
     * Number of result pages
     * @var int
     */
    var $pages;

    /**
     * Current result page
     * @var int
     */
    var $result_page;

    /**
     * Results per page to display
     * @var int
     */
    var $result_per_page;

    /**
     * Array of html-tags to emphasize the searchwords
     * @var array
     */
    var $replacement = array();

    /**
     * Array of article id's with ranking information
     * @var array
     */
    var $rank_structure = array();

    /**
     * Array of result-pages with array's of article id's
     * @var array
     */
    var $ordered_search_result = array();

    /**
     * Array of article id's with information about cms-types, occurence of keyword/searchword, similarity ...
     * @var array
     */
    var $search_result = array();

    /**
     * Compute ranking factor for each search result and order the search results by ranking factor
     * NOTE: The ranking factor is the sum of occurences of matching searchterms weighted by similarity (in %) between searchword
     * and matching word in the article.
     * TODO: One can think of more sophisticated ranking strategies. One could use the content type information for example
     * because a matching word in the headline (CMS_HEADLINE[1]) could be weighted more than a matching word in the text (CMS_HTML[1]).
     *
     * @param  array  $search_result  List of article ids
     * @param  int    $result_per_page  Number of items per page
     * @param  DB_ConLite  $oDB  Optional db instance
     * @param  bool  $bDebug  Optional flag to enable debugging
     */
    function __construct($search_result, $result_per_page, $oDB = null, $bDebug = false) {
        parent::__construct($oDB, $bDebug);

        $this->index = new Index($oDB);

        $this->search_result = $search_result;
        $this->_debug('$this->search_result', $this->search_result);

        $this->result_per_page = $result_per_page;
        $this->results = count($this->search_result);

        # compute ranking factor for each search result
        foreach ($this->search_result as $article => $val) {
            $this->rank_structure[$article] = $this->getOccurrence($article) * ( $this->getSimilarity($article) / 100);
        }
        $this->_debug('$this->rank_structure', $this->rank_structure);

        $this->setOrderedSearchResult($this->rank_structure, $this->result_per_page);
        $this->pages = count($this->ordered_search_result);
        $this->_debug('$this->ordered_search_result', $this->ordered_search_result);
    }

    /**
     * @param $ranked_search
     * @param $result_per_page
     * @return void
     */
    function setOrderedSearchResult($ranked_search, $result_per_page) {
        asort($ranked_search);

        $sorted_rank = array_reverse($ranked_search, true);

        if (isset($result_per_page) && $result_per_page > 0) {
            $split_result = array();
            $split_result = array_chunk($sorted_rank, $result_per_page, true);
            $this->ordered_search_result = $split_result;
        } else {
            $this->ordered_search_result[] = $sorted_rank;
        }
    }

    /**
     * @param $cms_type
     * @param $art_id Id of an article
     * @return Content of an article, specified by it's content type
     */
    function getContent($art_id, $cms_type, $id = 0) {
        $article = new Article($art_id, $this->client, $this->lang);
        return $article->getContent($cms_type, $id);
    }

    /**
     * @param $cms_type Content type
     * @param $art_id Id of an article
     * @return Content of an article in search result, specified by its type
     */
    function getSearchContent($art_id, $cms_type, $cms_nr = NULL) {
        $cms_type = strtoupper($cms_type);
        if (strlen($cms_type) > 0) {
            if (!stristr($cms_type, 'cms_')) {
                if (in_array($cms_type, $this->index->getContentTypes()['cms_type'])) {
                    $cms_type = 'CMS_' . $cms_type;
                }
            } else {
                if (!array_key_exists($cms_type, $this->index->getContentTypes()['cms_type_suffix'])) {
                    return array();
                }
            }
        }

        $article = new Article($art_id, $this->client, $this->lang);
        $content = array();
        if (isset($this->search_result[$art_id][$cms_type])) {
            // if searchword occurs in cms_type
            $search_words = $this->search_result[$art_id]['search'];
            $search_words = array_unique($search_words);

            $id_type = $this->search_result[$art_id][$cms_type];
            $id_type = array_unique($id_type);

            if (isset($cms_nr) && is_numeric($cms_nr)) {
                // get content of cms_type[cms_nr]
                //build consistent escaped string(Timo Trautmann) 2008-04-17
                $cms_content = clHtmlEntities(clHtmlEntityDecode(strip_tags($article->getContent($cms_type, $cms_nr))));
                if (count($this->replacement) == 2) {
                    foreach ($search_words as $word) {
                        //build consistent escaped string, replace ae ue .. with original html entities (Timo Trautmann) 2008-04-17
                        $word = clHtmlEntities(clHtmlEntityDecode($this->index->addSpecialUmlauts($word)));
                        $match = array();
                        preg_match("/$word/i", $cms_content, $match);
                        if (isset($match[0])) {
                            $pattern = $match[0];
                            $replacement = $this->replacement[0] . $pattern . $this->replacement[1];
                            $cms_content = preg_replace("/$pattern/i", $replacement, $cms_content); // emphasize located searchwords
                        }
                    }
                }
                $content[] = htmlspecialchars_decode($cms_content);
            } else {
                // get content of cms_type[$id], where $id are the cms_type numbers found in search
                foreach ($id_type as $id) {
                    $cms_content = strip_tags($article->getContent($cms_type, $id));

                    if (count($this->replacement) == 2) {
                        foreach ($search_words as $word) {
                            preg_match("/$word/i", $cms_content, $match);
                            if (isset($match[0])) {
                                $pattern = $match[0];
                                $replacement = $this->replacement[0] . $pattern . $this->replacement[1];
                                $cms_content = preg_replace("/$pattern/i", $replacement, $cms_content); // emphasize located searchwords
                            }
                        }
                    }
                    $content[] = $cms_content;
                }
            }
        } else {
            // searchword was not found in cms_type
            if (isset($cms_nr) && is_numeric($cms_nr)) {
                $content[] = strip_tags($article->getContent($cms_type, $cms_nr));
            } else {
                $art_content = $article->getContent($cms_type);
                if (count($art_content) > 0) {
                    foreach ($art_content as $val) {
                        $content[] = strip_tags($val);
                    }
                }
            }
        }
        return $content;
    }

    /**
     * Returns articles in page.
     *
     * @param   int    $page_id
     * @return  array  Artices in page $page_id
     */
    function getSearchResultPage($page_id) {
        $this->result_page = $page_id;
        $result_page = $this->ordered_search_result[$page_id - 1];
        return $result_page;
    }

    /**
     * Returns number of result pages
     * @return  int
     */
    function getNumberOfPages() {
        return $this->pages;
    }

    /**
     * Returns number of results
     * @return  int
     */
    function getNumberOfResults() {
        return $this->results;
    }

    /**
     * @param $art_id Id of an article
     * @return Similarity between searchword and matching word in article
     */
    function getSimilarity($art_id) {
        return $this->search_result[$art_id]['similarity'];
    }

    /**
     * @param $art_id Id of an article
     * @return Number of matching searchwords found in article
     */
    function getOccurrence($art_id) {
        $aOccurence = $this->search_result[$art_id]['occurence'];
        $iSumOfOccurence = 0;
        for ($i = 0; $i < count($aOccurence); $i++) {
            $iSumOfOccurence += $aOccurence[$i];
        }

        return $iSumOfOccurence;
    }

    /**
     * @param string $rep1 The opening html-tag to emphasize the searchword e.g. '<b>'
     * @param string $rep2 The closing html-tag e.g. '</b>'
     * @return void
     */
    function setReplacement($rep1, $rep2) {
        if (strlen(trim($rep1)) > 0 && strlen(trim($rep2)) > 0) {
            array_push($this->replacement, $rep1);
            array_push($this->replacement, $rep2);
        }
    }

    /**
     * @param $artid
     * @return Category Id
     * @todo  Is not job of search, should be outsourced!
     */
    function getArtCat($artid) {
        $sql = "SELECT idcat FROM " . $this->cfg['tab']['cat_art'] . "
                WHERE idart = " . Contenido_Security::toInteger($artid) . " ";
        $this->db->query($sql);
        if ($this->db->next_record()) {
            return $this->db->f('idcat');
        }
    }

}
