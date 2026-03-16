<?php
/**
 * Project:
 * Contenido Content Management System
 *
 * Description:
 * Objects for Category handling.
 *
 * Requirements:
 * @con_php_req 5.0
 *
 *
 * @package    Contenido Backend classes
 * @version    0.8.2
 * @author     Rudi Bieller
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 *
 * {@internal
 *   created 2008-02-15
 *   modified 2008-02-22 Contenido_Categories now implements Countable
 *   modified 2008-08-20 Removed unnecessary/redundant security fixes (typecasting is already done in getter methods) that were made during security fixing phase
 *             changed method setDebug() in Contenido_Category_Base to allow all debug modes available
 *   modified 2009-01-05 Bugfix in Contenido_Categories::load() Subcategories will be loaded only if set so.
 *   modified 2009-01-14 Removed duplicate row in sql select at method load()
 *   $Id$:
 * }}
 *
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}


/**
 * Base class for Contenido_Category, Contenido_Categories, Contenido_Category_Language.
 * @version 0.9.0
 * @author Rudi Bieller
 * @copyright four for business AG <www.4fb.de>
 * {@internal
 * created 2008-02-15
 * }}
 */
class Contenido_Category_Base
{
    /**
     * @var DB_ConLite
     * @access protected
     */
    protected DB_ConLite $oDb;
    /**
     * @var array
     * @access protected
     */
    protected array $aCfg;
    /**
     * @var boolean
     * @access protected
     */
    protected bool $bDbg;
    /**
     * @var string
     * @access protected
     */
    protected string $sDbgMode = 'hidden';
    /**
     * @var Debug_File|Debug_Visible|Debug_Hidden|Debug_VisibleAdv|Debug_DevNull|null
     * @access protected
     */
    protected Debug_File|Debug_Visible|Debug_Hidden|Debug_VisibleAdv|Debug_DevNull|null $oDbg;

    /**
     * Constructor.
     * @access public
     * @param DB_ConLite $oDb
     * @param array $aCfg
     * @return void
     * @author Rudi Bieller
     */
    public function __construct(DB_ConLite $oDb, array $aCfg)
    {
        $this->oDb = $oDb;
        $this->aCfg = $aCfg;
        $this->bDbg = false;
        $this->oDbg = null;
    }

    /**
     * Set internal property for debugging on/off and choose appropriate debug object
     * @access public
     * @param boolean $bDebug
     * @param string $sDebugMode
     * @return  void
     * @author Rudi Bieller
     */
    public function setDebug(bool $bDebug = true, string $sDebugMode = 'visible'): void
    {
        if ($bDebug === false) {
            $this->bDbg = false;
            $this->oDbg = null;
            $this->sDbgMode = 'hidden';
        } else {
            if (!in_array($sDebugMode, array('visible', 'visible_adv', 'file', 'devnull', 'hidden'))) {
                $sDebugMode = 'devnull';
            }
            $this->sDbgMode = $sDebugMode;
            $this->bDbg = true;
            $this->oDbg = DebuggerFactory::getDebugger($sDebugMode);
        }
    }
}


/**
 * Implementation of a Contenido Category.
 * @version 0.9.0
 * @author Rudi Bieller
 * @copyright four for business AG <www.4fb.de>
 * {@internal
 * created 2008-02-15
 * }}
 */
class Contenido_Category extends Contenido_Category_Base
{
    protected int $iIdCat;
    protected int $iIdClient;
    protected int $iIdParent;
    protected int $iIdPre;
    protected int $iIdPost;
    protected int $iStatus;

    protected string $sAuthor;
    protected string $sCreated;
    protected string $sModified;

    /**
     * @var Contenido_Category_Language
     * @access protected
     */
    protected Contenido_Category_Language $oCategoryLanguage;
    /**
     * @var int
     * @access protected
     */
    protected int $iIdLang;

    /**
     * @var boolean
     * @access protected
     */
    protected bool $bLoadSubCategories;

    /**
     * @var Contenido_Categories|null
     * @access protected
     */
    protected Contenido_Categories|null $oSubCategories; // if required, this holds the SubCategories of current Category

    /**
     * @var boolean
     * @access protected
     */
    protected bool $bHasSubCategories;

    /**
     * @var int
     * @access protected
     */
    protected int $iCurrentSubCategoriesLoadDepth; // current level of SubCategories

    /**
     * @var int
     * @access protected
     */
    protected int $iSubCategoriesLoadDepth; // up to which level should SubCategories be loaded

    /**
     * @var DB_ConLite
     * @access private
     */
    private DB_ConLite $_oDb;


    /**
     * Constructor.
     * @access public
     * @param DB_ConLite $oDb
     * @param array $aCfg
     * @return void
     * @author Rudi Bieller
     */
    public function __construct(DB_ConLite $oDb, array $aCfg)
    {
        parent::__construct($oDb, $aCfg);
        $this->oSubCategories = null;
        $this->bHasSubCategories = false;
        $this->iSubCategoriesLoadDepth = 0;
        $this->iCurrentSubCategoriesLoadDepth = 0;
    }

    /**
     * Loads properties for a given idcat. Optionally, also properties from catlang will be loaded into object.
     *
     * @access public
     * @param int $iIdCat
     * @param boolean $bIncludeLanguage If set to true, also creates Contenido_Category_Language object
     * @param int $iIdlang If $bIncludeLanguage is set to true, you must set this value, too or use setIdLang() before!
     * @return boolean
     * @throws InvalidArgumentException
     * @throws Exception TODO
     * @author Rudi Bieller
     */
    public function load(int $iIdCat, bool $bIncludeLanguage = false, int $iIdlang = -1)
    {
        if (intval($iIdCat) <= 0) {
            throw new InvalidArgumentException('Idcat to load must be greater than 0!');
        }
        $this->setIdLang($iIdlang);
        if ($bIncludeLanguage === true && $this->getIdLang() == -1) {
            throw new InvalidArgumentException('When setting $bIncludeLanguage to true you must provide an $iIdlang!');
        }
        $sSql = 'SELECT 
					idclient, parentid, preid, postid, status, author, created, lastmodified 
				FROM 
					' . $this->aCfg['tab']['cat'] . ' 
				WHERE 
					idcat = ' . Contenido_Security::toInteger($iIdCat);
        if ($this->bDbg === true) {
            $this->oDbg->show($sSql, 'Contenido_Category::load($iIdCat, $bIncludeLanguage = false, $iIdlang = -1): $sSql');
        }
        $this->oDb->query($sSql);
        if ($this->oDb->getErrno() != 0) {
            return false;
        }
        $this->oDb->nextRecord();
        $this->setIdCat($iIdCat);
        $this->setIdClient($this->oDb->f('idclient'));
        $this->setIdParent($this->oDb->f('parentid'));
        $this->setIdPre($this->oDb->f('preid'));
        $this->setIdPost($this->oDb->f('postid'));
        $this->setStatus($this->oDb->f('status'));
        $this->setAuthor($this->oDb->f('author'));
        $this->setDateCreated($this->oDb->f('created'));
        $this->setDateModified($this->oDb->f('lastmodified'));
        if ($bIncludeLanguage === true) {
            $oCategoryLanguage = new Contenido_Category_Language($this->oDb, $this->aCfg);
            $oCategoryLanguage->setDebug($this->bDbg, $this->sDbgMode);
            $oCategoryLanguage->setIdCat($this->getIdCat());
            $oCategoryLanguage->setIdLang($this->getIdLang());
            $oCategoryLanguage->load();
            $this->setCategoryLanguage($oCategoryLanguage);
        }
        if (isset($this->bLoadSubCategories) && $this->bLoadSubCategories === true) {
            $this->_getSubCategories($iIdCat, $bIncludeLanguage, $iIdlang);
        }
        return true;
    }

    /**
     * Loads SubCategories depending on values for $this->bLoadSubCategories and $this->iSubCategoriesLoadDepth
     * @access private
     * @param int $iIdcat
     * @param boolean $bIncludeLanguage If set to true, also creates Contenido_Category_Language object
     * @param int $iIdlang If $bIncludeLanguage is set to true, you must set this value, too or use setIdLang() before!
     * @return Contenido_Categories
     * @throws Exception
     * @author Rudi Bieller
     */
    private function _getSubCategories(int $iIdcat, bool $bIncludeLanguage = false, int $iIdlang = -1): Contenido_Categories
    {
        if ($iIdcat <= 0) {
            throw new InvalidArgumentException('Idcat to load must be greater than 0!');
        }
        if ($bIncludeLanguage === true && $this->getIdLang() == -1) {
            throw new InvalidArgumentException('When setting $bIncludeLanguage to true you must provide an $iIdlang!');
        }
        // if we don't have a Contenido_Categories object created yet, do it now
        if (is_null($this->oSubCategories)) {
            $this->oSubCategories = new Contenido_Categories($this->oDb, $this->aCfg);
            $this->_oDb = new DB_ConLite();
        }
        $aSubCategories = $this->_getSubCategoriesAsArray($iIdcat);
        // current load depth: $this->iCurrentSubCategoriesLoadDepth
        // load depth to go to: $this->iSubCategoriesLoadDepth
        foreach ($aSubCategories as $iIdcatCurrent) {
            $oCategory = new Contenido_Category($this->_oDb, $this->aCfg);
            $oCategory->setDebug($this->bDbg, $this->sDbgMode);
            if ($this->iSubCategoriesLoadDepth > 0) {
                $oCategory->setloadSubCategories($this->bLoadSubCategories, ($this->iSubCategoriesLoadDepth - 1));
            }
            $oCategory->load($iIdcatCurrent, $bIncludeLanguage, $iIdlang);
            $this->oSubCategories->add($oCategory);
        }
        return $this->oSubCategories;
    }

    /**
     * Return array with idcats of subcategories of given idcat
     * @access private
     * @param int $iIdcat
     * @return array
     * @author Rudi Bieller
     */
    private function _getSubCategoriesAsArray(int $iIdcat): bool|array
    {
        if (intval($iIdcat) <= 0) {
            throw new InvalidArgumentException('Idcat to load must be greater than 0!');
        }
        $aSubCats = array();
        $sSql = 'SELECT
					cattree.idcat
				FROM
					' . $this->aCfg["tab"]["cat_tree"] . ' AS cattree,
					' . $this->aCfg["tab"]["cat"] . ' AS cat,
					' . $this->aCfg["tab"]["cat_lang"] . ' AS catlang
				WHERE
					cattree.idcat    = cat.idcat AND
					cat.idcat    = catlang.idcat AND
					cat.idclient = ' . $this->getIdClient() . ' AND
					catlang.idlang   = ' . $this->getIdLang() . ' AND
					catlang.visible  = 1 AND 
					cat.parentid = ' . Contenido_Security::toInteger($iIdcat) . '
				ORDER BY
					cattree.idtree';
        if ($this->bDbg === true) {
            $this->oDbg->show($sSql, 'Contenido_Category::_getSubCategoriesAsArray($iIdcat): $sSql');
        }
        $this->oDb->query($sSql);
        if ($this->oDb->getErrno() != 0) {
            return false;
        }
        while ($this->oDb->nextRecord()) {
            $aSubCats[] = $this->oDb->f('idcat');
        }
        return $aSubCats;
    }

    // SETTER

    /**
     * If you need to load SubCategories, set to true and set how deep SubCategories should be loaded
     * @access public
     * @param boolean $bLoad
     * @param int $iLoadDepth
     * @return void
     * @author Rudi Bieller
     */
    public function setloadSubCategories(bool $bLoad = false, int $iLoadDepth = 0): void
    {
        $this->bLoadSubCategories = $bLoad;
        $this->iSubCategoriesLoadDepth = $iLoadDepth;
    }

    /**
     * Set internal property with SubCategories of current Category
     * @access public
     * @param Contenido_Categories $oCategories
     * @return void
     * @author Rudi Bieller
     */
    public function setSubCategories(Contenido_Categories $oCategories): void
    {
        $this->oSubCategories = $oCategories;
    }

    public function setCategoryLanguage(Contenido_Category_Language $oCatLang): void
    {
        $this->oCategoryLanguage = $oCatLang;
    }

    public function setIdCat(int $iIdcat): void
    {
        $this->iIdCat = $iIdcat;
    }

    public function setIdClient(int $iIdcient): void
    {
        $this->iIdClient = $iIdcient;
    }

    public function setIdParent(int $iIdcatParent): void
    {
        $this->iIdParent = $iIdcatParent;
    }

    public function setIdPre(int $iIdcatPre): void
    {
        $this->iIdPre = $iIdcatPre;
    }

    public function setIdPost(int $iIdcatPost): void
    {
        $this->iIdPost = $iIdcatPost;
    }

    public function setStatus(int $iStatus): void
    {
        $aValid = array(0, 1);
        if (!in_array($iStatus, $aValid)) {
            throw new InvalidArgumentException('Status must be either 0 or 1');
        }
        $this->iStatus = $iStatus;
    }

    public function setAuthor(string $sAuthor): void
    {
        // TODO: input validation, strlen 32
        $this->sAuthor = $sAuthor;
    }

    public function setDateCreated(string $sDateCreated): void
    {
        // TODO: input validation, correct date/datetime format
        $this->sCreated = $sDateCreated;
    }

    public function setDateModified(string $sDateModified): void
    {
        // TODO: input validation, correct date/datetime format
        $this->sModified = $sDateModified;
    }

    public function setIdLang(int $iIdlang): void
    {
        $this->iIdLang = $iIdlang;
    }

    // GETTER

    public function getSubCategories(): ?Contenido_Categories
    {
        return is_null($this->oSubCategories) ? new Contenido_Categories($this->oDb, $this->aCfg) : $this->oSubCategories;
    }

    public function getCategoryLanguage(): Contenido_Category_Language
    {
        return !is_null($this->oCategoryLanguage) ? $this->oCategoryLanguage : new Contenido_Category_Language($this->oDb, $this->aCfg);
    }

    public function getIdCat(): int
    {
        return !is_null($this->iIdCat) ? $this->iIdCat : -1;
    }

    public function getIdClient(): int
    {
        return !is_null($this->iIdClient) ? $this->iIdClient : -1;
    }

    public function getIdParent(): int
    {
        return !is_null($this->iIdParent) ? $this->iIdParent : -1;
    }

    public function getIdPre(): int
    {
        return !is_null($this->iIdPre) ? $this->iIdPre : -1;
    }

    public function getIdPost(): int
    {
        return !is_null($this->iIdPost) ? $this->iIdPost : -1;
    }

    public function getStatus(): int
    {
        return !is_null($this->iStatus) ? $this->iStatus : -1;
    }

    public function getAuthor(): string
    {
        return !is_null($this->sAuthor) ? $this->sAuthor : '';
    }

    public function getDateCreated(): string
    {
        return !is_null($this->sCreated) ? $this->sCreated : '';
    }

    public function getDateModified(): string
    {
        return !is_null($this->sModified) ? $this->sModified : '';
    }

    public function getIdLang(): int
    {
        return (!is_null($this->iIdLang) && $this->iIdLang > 0) ? $this->iIdLang : -1;
    }
}

/**
 * Implementation of a "Collection" of Contenido Categories.
 * @version 0.9.0
 * @author Rudi Bieller
 * @copyright four for business AG <www.4fb.de>
 * {@internal
 * created 2008-02-15
 * modified 2008-02-25 Implemented ArrayAccess; added methods reverse(), ksort() and krsort().
 * }}
 */
class Contenido_Categories extends Contenido_Category_Base implements IteratorAggregate, ArrayAccess, Countable
{
    /**
     * @var array
     * @access protected
     */
    protected array $aContenidoCategories;
    /**
     * @var int
     * @access protected
     */
    protected int $iIdLang;
    /**
     * @var boolean
     * @access protected
     */
    protected bool $bLoadSubCategories;

    /**
     * @var int
     * @access protected
     */
    protected int $iSubCategoriesLoadDepth; // up to which level should SubCategories be loaded

    /**
     * Constructor.
     * @access public
     * @param DB_ConLite $oDb
     * @param array $aCfg
     * @return void
     * @author Rudi Bieller
     */
    public function __construct(DB_ConLite $oDb, array $aCfg)
    {
        parent::__construct($oDb, $aCfg);
        $this->aContenidoCategories = [];
        $this->bLoadSubCategories = false;
        $this->iSubCategoriesLoadDepth = 0;
    }

    /**
     * Loads a range of Category-IDs.
     * @access public
     * @param array $aCategoryIds
     * @param boolean $bIncludeLanguage
     * @param int $iIdlang If $bIncludeLanguage is set to true, you must set this value, too or use setIdLang() before!
     * @return void
     * @throws Exception
     * @author Rudi Bieller
     */
    public function load(array $aCategoryIds, bool $bIncludeLanguage = false, int $iIdlang = -1)
    {
        $this->setIdLang($iIdlang);
        if (sizeof($aCategoryIds) > 0) {
            // loop over passed category ids and create single Category object on each run
            foreach ($aCategoryIds as $iId) {
                $iIdLang = $this->getIdLang();
                $oCategory = new Contenido_Category($this->oDb, $this->aCfg);
                $oCategory->setDebug($this->bDbg, $this->sDbgMode);
                if ($this->iSubCategoriesLoadDepth > 0) {
                    $oCategory->setloadSubCategories($this->bLoadSubCategories, $this->iSubCategoriesLoadDepth);
                }
                $oCategory->load($iId, $bIncludeLanguage, $iIdLang);
                $this->add($oCategory);
            }
        }
    }

    /**
     * Add a Contenido_Category object into internal array ("Collection")
     * @access public
     * @param Contenido_Category $oContenidoCategory
     * @param int|null $iOffset
     * @return void
     * @author Rudi Bieller
     */
    public function add(Contenido_Category $oContenidoCategory, int $iOffset = null): void
    {
        $this->offsetSet($iOffset, $oContenidoCategory);
    }

    /**
     * If you need to load SubCategories, set to true and set how deep SubCategories should be loaded
     * @access public
     * @param boolean $bLoad
     * @param int $iLoadDepth
     * @return void
     * @author Rudi Bieller
     */
    public function setloadSubCategories(bool $bLoad = false, int $iLoadDepth = 0): void
    {
        $this->bLoadSubCategories = $bLoad;
        $this->iSubCategoriesLoadDepth = $iLoadDepth;
    }

    /**
     * Set internal property for Contenido-Idlang
     * @access public
     * @param int $iIdlang
     * @return void
     * @author Rudi Bieller
     */
    public function setIdLang(int $iIdlang): void
    {
        $this->iIdLang = $iIdlang;
    }

    /**
     * Get internal property for Contenido-Idlang
     * @access public
     * @return int
     * @author Rudi Bieller
     */
    public function getIdLang(): int
    {
        return (!is_null($this->iIdLang) && $this->iIdLang > 0) ? $this->iIdLang : -1;
    }

    /**
     * Interface method for Iterator.
     * @access public
     * @return ArrayObject
     * @author Rudi Bieller
     */
    public function getIterator(): ArrayObject
    {
        return new ArrayObject($this->aContenidoCategories);
    }

    /**
     * Interface method for Countable.
     * @access public
     * @return int
     * @author Rudi Bieller
     */
    public function count(): int
    {
        return sizeof($this->aContenidoCategories);
    }

    /**
     * Sort list of Contenido_Category objects by assigned key
     * @access public
     * @return void
     * @author Rudi Bieller
     */
    public function ksort(): void
    {
        ksort($this->aContenidoCategories);
    }

    /**
     * Sort list of Contenido_Category objects by assigned key in reverse order
     * @access public
     * @return void
     * @author Rudi Bieller
     */
    public function krsort(): void
    {
        krsort($this->aContenidoCategories);
    }

    /**
     * Sort list of Contenido_Category objects in reverse order
     * @access public
     * @return void
     * @author Rudi Bieller
     */
    public function reverse(): void
    {
        $this->aContenidoCategories = array_reverse($this->aContenidoCategories);
    }

    // Methods for ArrayAccess

    /**
     * Interface method for ArrayAccess.
     * @access public
     * @param int $mOffset
     * @return boolean
     * @author Rudi Bieller
     */
    public function offsetExists($mOffset)
    {
        return array_key_exists($this->aContenidoCategories, $mOffset);
    }

    /**
     * Interface method for ArrayAccess.
     * @access public
     * @param int $mOffset
     * @return obj
     * @author Rudi Bieller
     */
    public function offsetGet($mOffset)
    {
        return $this->aContenidoCategories[$mOffset];
    }

    /**
     * Interface method for ArrayAccess.
     * @access public
     * @param int $mOffset
     * @param mixed $mValue
     * @return void
     * @author Rudi Bieller
     */
    public function offsetSet($mOffset, $mValue): void
    {
        if (is_null($mOffset)) {
            $this->aContenidoCategories[] = $mValue;
        } else {
            $this->aContenidoCategories[$mOffset] = $mValue;
        }
    }

    /**
     * Interface method for ArrayAccess.
     * @access public
     * @param int $mOffset
     * @return void
     * @author Rudi Bieller
     */
    public function offsetUnset($mOffset): void
    {
        unset($this->aContenidoCategories[$mOffset]);
    }
}

/**
 * Implementation of a Contenido Category for a given Contenido Language.
 * @version 0.9.0
 * @author Rudi Bieller
 * @copyright four for business AG <www.4fb.de>
 * {@internal
 * created 2008-02-15
 * }}
 */
class Contenido_Category_Language extends Contenido_Category_Base
{
    protected int $iIdCatlang;
    protected $iIdCat;
    protected int $iIdTplcfg;

    protected $sName;
    protected $sAlias;
    protected $iVisible;
    protected $iPublic;
    protected $iStatus;
    protected $sAuthor;
    protected $sDateCreated;
    protected $sDateModified;
    protected $iStartIdartlang;
    protected $sUrlname;

    /**
     * Constructor.
     * @access public
     * @param DB_ConLite $oDb
     * @param array $aCfg
     * @return void
     * @author Rudi Bieller
     */
    public function __construct(DB_ConLite $oDb, array $aCfg)
    {
        parent::__construct($oDb, $aCfg);
    }

    /**
     * Load cat_lang for a given idcat.
     * @access public
     * @param int|null $iIdCatLang
     * @return boolean
     * @throws Exception
     * @author Rudi Bieller
     */
    public function load(int $iIdCatLang = null): bool
    {
        if ($this->getIdCat() == -1 || $this->getIdLang() == -1) {
            throw new Exception('idcat and idlang must be set in order to load from con_cat_lang!');
        }
        if (is_null($iIdCatLang)) {
            $sSql = 'SELECT 
						idcatlang, idtplcfg, name, visible, public, status, author, created, lastmodified, startidartlang, urlname 
					FROM 
						' . $this->aCfg["tab"]["cat_lang"] . ' 
					WHERE 
						idcat = ' . $this->getIdCat() . ' AND 
						idlang = ' . $this->getIdLang();
        } else {
            $sSql = 'SELECT 
						idcatlang, idtplcfg, name, visible, public, status, author, created, lastmodified, startidartlang, urlname 
					FROM 
						' . $this->aCfg["tab"]["cat_lang"] . ' 
					WHERE 
						idcatlang = ' . Contenido_Security::toInteger($iIdCatLang);
        }
        $this->oDb->query($sSql);
        if ($this->oDb->getErrno() != 0) {
            return false;
        }
        $this->oDb->nextRecord();
        $this->setIdCatLang($this->oDb->f('idcatlang'));
        $this->setIdCat($this->getIdCat());
        $this->setIdLang($this->getIdLang());
        $this->setIdTemplateConfig($this->oDb->f('idtplcfg'));
        $this->setName($this->oDb->f('name'));
        $this->setAlias($this->oDb->f('urlname'));
        $this->setVisible($this->oDb->f('visible'));
        $this->setPublic($this->oDb->f('public'));
        $this->setStatus($this->oDb->f('status'));
        $this->setAuthor($this->oDb->f('author'));
        $this->setDateCreated($this->oDb->f('created'));
        $this->setDateLastModified($this->oDb->f('lastmodified'));
        $this->setStartIdLang($this->oDb->f('startidartlang'));
        $this->setUrlName($this->oDb->f('urlname'));
        return true;
    }

    // SETTER

    public function setIdCatLang(int $iIdcatlang): void
    {
        $this->iIdCatlang = $iIdcatlang;
    }

    public function setIdCat(int $iIdcat): void
    {
        $this->iIdCat = $iIdcat;
    }

    public function setIdLang(int $iIdlang): void
    {
        $this->iIdlang = $iIdlang;
    }

    public function setIdTemplateConfig(int $iIdTplcfg): void
    {
        $this->iIdTplcfg = $iIdTplcfg;
    }

    public function setName(string$sName): void
    {
        $this->sName = $sName;
    }

    public function setAlias(string $sAlias): void
    {
        $this->sAlias = $sAlias;
    }

    public function setVisible(int $iVisible): void
    {
        $aValid = array(0, 1);
        if (!in_array($iVisible, $aValid)) {
            throw new InvalidArgumentException('Visible must be either 0 or 1');
        }
        $this->iVisible = $iVisible;
    }

    public function setPublic(int $iPublic): void
    {
        $aValid = array(0, 1);
        if (!in_array($iPublic, $aValid)) {
            throw new InvalidArgumentException('Public must be either 0 or 1');
        }
        $this->iPublic = $iPublic;
    }

    public function setStatus(int $iStatus): void
    {
        $aValid = array(0, 1);
        if (!in_array($iStatus, $aValid)) {
            throw new InvalidArgumentException('Status must be either 0 or 1');
        }
        $this->iStatus = $iStatus;
    }

    public function setAuthor(string $sAuthor): void
    {
        $this->sAuthor = $sAuthor;
    }

    public function setDateCreated(string $sDateCreated): void
    {
        $this->sDateCreated = $sDateCreated;
    }

    public function setDateLastModified(string $sDateLastModified): void
    {
        $this->sDateModified = $sDateLastModified;
    }

    public function setStartIdLang(int $iStartIdlang): void
    {
        $this->iStartIdartlang = $iStartIdlang;
    }

    public function setUrlName(string$sUrlName): void
    {
        $this->sUrlname = $sUrlName;
    }

    // GETTER

    public function getIdCatLang()
    {
        return !is_null($this->iIdCatlang) ? $this->iIdCatlang : -1;
    }

    public function getIdCat(): int
    {
        return !is_null($this->iIdCat) ? (int)$this->iIdCat : -1;
    }

    public function getIdLang(): int
    {
        return !is_null($this->iIdlang) ? $this->iIdlang : -1;
    }

    public function getIdTemplateConfig(): int
    {
        return !is_null($this->iIdTplcfg) ? $this->iIdTplcfg : -1;
    }

    public function getName(): string
    {
        return !is_null($this->sName) ? (string)$this->sName : '';
    }

    public function getAlias(): string
    {
        return !is_null($this->sAlias) ? (string)$this->sAlias : '';
    }

    public function getVisible(): int
    {
        return !is_null($this->iVisible) ? (int)$this->iVisible : -1;
    }

    public function getPublic(): int
    {
        return !is_null($this->iPublic) ? (int)$this->iPublic : -1;
    }

    public function getStatus(): int
    {
        return !is_null($this->iStatus) ? (int)$this->iStatus : -1;
    }

    public function getAuthor(): string
    {
        return !is_null($this->sAuthor) ? (string)$this->sAuthor : '';
    }

    public function getDateCreated(): string
    {
        return !is_null($this->sDateCreated) ? (string)$this->sDateCreated : '';
    }

    public function getDateLastModified(): string
    {
        return !is_null($this->sDateModified) ? (string)$this->sDateModified : '';
    }

    public function getStartIdLang(): int
    {
        return !is_null($this->iStartIdartlang) ? (int)$this->iStartIdartlang : -1;
    }

    public function getUrlName(): string
    {
        return !is_null($this->sUrlname) ? (string)$this->sUrlname : '';
    }
}