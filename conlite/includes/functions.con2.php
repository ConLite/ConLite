<?php

/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * Contenido Content Functions
 * 
 * Requirements: 
 * @con_php_req 5.0
 * @con_notice Please add only stuff which is relevant for the frontend
 *             AND the backend. This file should NOT contain any backend editing
 *             functions to improve frontend performance:
 *
 *
 * @package    Contenido Backend includes
 * @version    1.3.7
 * @author     Timo A. Hummel
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.6
 *
 * $Id$:
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

/**
 * Generates the code for one
 * article
 *
 * @param int $idcat Id of category
 * @param int $idart Id of article
 * @param int $lang Id of language
 * @param int $client Id of client
 * @param int $layout Layout-ID of alternate Layout (if false, use associated layout)
 *
 * @author Jan Lengowski <jan.lengowski@4fb.de>
 * @copyright four for business AG <www.4fb.de>
 */
function conGenerateCode($idcat, $idart, $lang, $client, $layout = false) {
    global $frontend_debug, $_cecRegistry;

    $debug = 0;

    if ($debug)
        echo "conGenerateCode($idcat, $idart, $lang, $client, $layout);<br>";

    global $db, $db2, $sess, $cfg, $code, $cfgClient, $client, $lang, $encoding;

    if (!is_object($db2))
        $db2 = new DB_ConLite;

    /* extract IDCATART */
    $sql = "SELECT
	                    idcatart
	                FROM
	                    " . $cfg["tab"]["cat_art"] . "
	                WHERE
	                    idcat = '" . Contenido_Security::toInteger($idcat) . "' AND
	                    idart = '" . Contenido_Security::toInteger($idart) . "'";

    $db->query($sql);
    $db->next_record();

    $idcatart = $db->f("idcatart");

    /* If neither the
      article or the category is
      configured, no code will be
      created and an error occurs. */
    $sql = "SELECT
	                    a.idtplcfg AS idtplcfg
	                FROM
	                    " . $cfg["tab"]["art_lang"] . " AS a,
	                    " . $cfg["tab"]["art"] . " AS b
	                WHERE
	                    a.idart     = '" . Contenido_Security::toInteger($idart) . "' AND
	                    a.idlang    = '" . Contenido_Security::escapeDB($lang, $db) . "' AND
	                    b.idart     = a.idart AND
	                    b.idclient  = '" . Contenido_Security::escapeDB($client, $db) . "'";

    $db->query($sql);
    $db->next_record();

    if ($db->f("idtplcfg") != 0) {

        /* Article is configured */
        $idtplcfg = $db->f("idtplcfg");

        if ($debug)
            echo "configuration for article found: $idtplcfg<br><br>";

        $a_c = array();

        $sql2 = "SELECT
		                        *
		                     FROM
		                        " . $cfg["tab"]["container_conf"] . "
		                     WHERE
		                        idtplcfg = '" . Contenido_Security::toInteger($idtplcfg) . "'
		                     ORDER BY
		                        number ASC";

        $db2->query($sql2);

        while ($db2->next_record()) {
            $a_c[$db2->f("number")] = $db2->f("container");
        }
    } else {

        /* Check whether category is
          configured. */
        $sql = "SELECT
		                        a.idtplcfg AS idtplcfg
		                    FROM
		                        " . $cfg["tab"]["cat_lang"] . " AS a,
		                        " . $cfg["tab"]["cat"] . " AS b
		                    WHERE
		                        a.idcat     = '" . Contenido_Security::toInteger($idcat) . "' AND
		                        a.idlang    = '" . Contenido_Security::escapeDB($lang, $db) . "' AND
		                        b.idcat     = a.idcat AND
		                        b.idclient  = '" . Contenido_Security::escapeDB($client, $db) . "'";

        $db->query($sql);
        $db->next_record();

        if ($db->f("idtplcfg") != 0) {

            /* Category is configured,
              extract varstring */
            $idtplcfg = $db->f("idtplcfg");

            if ($debug)
                echo "configuration for category found: $idtplcfg<br><br>";

            $a_c = array();

            $sql2 = "SELECT
			                            *
			                         FROM
			                            " . $cfg["tab"]["container_conf"] . "
			                         WHERE
			                            idtplcfg = '" . Contenido_Security::toInteger($idtplcfg) . "'
			                         ORDER BY
			                            number ASC";

            $db2->query($sql2);

            while ($db2->next_record()) {
                $a_c[$db2->f("number")] = $db2->f("container");
            }
        } else {

            /* Article nor Category
              is configured. Creation of
              Code is not possible. Write
              Errormsg to DB. */

            if ($debug)
                echo "Neither CAT or ART are configured!<br><br>";

            $code = '<html><body>No code was created for this art in this category.</body><html>';

            $sql = "SELECT * FROM " . $cfg["tab"]["code"] . " WHERE idcatart='" . Contenido_Security::toInteger($idcatart) . "' AND idlang='" . Contenido_Security::escapeDB($lang, $db) . "'";

            $db->query($sql);

            if ($db->next_record()) {
                $sql = "UPDATE " . $cfg["tab"]["code"] . " SET code='" . Contenido_Security::escapeDB($code, $db) . "', idlang='" . Contenido_Security::escapeDB($lang, $db) . "', idclient='" . Contenido_Security::escapeDB($client, $db) . "'
                        WHERE idcatart='" . Contenido_Security::toInteger($idcatart) . "' AND idlang='" . Contenido_Security::escapeDB($lang, $db) . "'";
                $db->query($sql);
            } else {
                $sql = "INSERT INTO " . $cfg["tab"]["code"] . " (idcode, idcatart, code, idlang, idclient) VALUES ('" . Contenido_Security::toInteger($db->nextid($cfg["tab"]["code"])) . "', '" . Contenido_Security::toInteger($idcatart) . "',
                        '" . Contenido_Security::escapeDB($code, $db) . "', '" . Contenido_Security::escapeDB($lang, $db) . "', '" . Contenido_Security::escapeDB($client, $db) . "')";
                $db->query($sql);
            }

            return "0601";
        }
    }

    /* Get IDLAY and IDMOD array */
    $sql = "SELECT
	                    a.idlay AS idlay,
	                    a.idtpl AS idtpl
	                FROM
	                    " . $cfg["tab"]["tpl"] . " AS a,
	                    " . $cfg["tab"]["tpl_conf"] . " AS b
	                WHERE
	                    b.idtplcfg  = '" . Contenido_Security::toInteger($idtplcfg) . "' AND
	                    b.idtpl     = a.idtpl";

    $db->query($sql);
    $db->next_record();

    $idlay = $db->f("idlay");

    if ($layout != false) {
        $idlay = $layout;
    }

    $idtpl = $db->f("idtpl");

    if ($debug)
        echo "Using Layout: $idlay and Template: $idtpl for generation of code.<br><br>";

    /* List of used modules */
    $sql = "SELECT
	                    number,
	                    idmod
	                FROM
	                    " . $cfg["tab"]["container"] . "
	                WHERE
	                    idtpl = '" . Contenido_Security::toInteger($idtpl) . "'
	                ORDER BY
	                    number ASC";

    $db->query($sql);

    while ($db->next_record()) {
        $a_d[$db->f("number")] = $db->f("idmod");
    }

    $oLayout = new cApiLayout(Contenido_Security::toInteger($idlay));
    $raw_code = $oLayout->getLayout();
    $code = AddSlashes($raw_code);

    /* Create code for all containers */
    if ($idlay) {
        tplPreparseLayout($idlay, $raw_code);
        $tmp_returnstring = tplBrowseLayoutForContainers($idlay, $raw_code);
        $a_container = explode("&", $tmp_returnstring);

        foreach ($a_container as $key => $value) {

            if (is_numeric($a_d[$value])) {
                $thisModule = '<?php $cCurrentModule = ' . ((int) $a_d[$value]) . '; ?>';
                $thisContainer = '<?php $cCurrentContainer = ' . ((int) $value) . '; ?>';
            }

            $oModule = new cApiModule($a_d[$value]);
            $output = $thisModule . $thisContainer . $oModule->get("output");
            $template = $oModule->get("template");

            $output = AddSlashes($output) . "\n";

            $varstring = array();
            if (!empty($a_c[$value])) {
                $a_c[$value] = preg_replace("/(&\$)/", "", $a_c[$value]);
                $tmp1 = preg_split("/&/", $a_c[$value]);

                foreach ($tmp1 as $key1 => $value1) {

                    $tmp2 = explode("=", $value1);
                    foreach ($tmp2 as $key2 => $value2) {
                        $varstring["$tmp2[0]"] = $tmp2[1];
                    }
                }
            }
            $CiCMS_Var = '$C' . $value . 'CMS_VALUE';
            $CiCMS_VALUE = '';

            foreach ($varstring as $key3 => $value3) {
                $tmp = urldecode($value3);
                $tmp = str_replace("\'", "'", $tmp);
                $CiCMS_VALUE .= $CiCMS_Var . '[' . $key3 . ']="' . $tmp . '"; ';
                $output = str_replace("\$CMS_VALUE[$key3]", $tmp, $output);
                $output = str_replace("CMS_VALUE[$key3]", $tmp, $output);
            }

            $output = str_replace("CMS_VALUE", $CiCMS_Var, $output);
            $output = str_replace("\$" . $CiCMS_Var, $CiCMS_Var, $output);

            $output = preg_replace("/(CMS_VALUE\[)([0-9]*)(\])/i", "", $output);

            if ($frontend_debug["container_display"] == true) {
                $fedebug .= "Container: CMS_CONTAINER[$value]" . '\\\\n';
            }
            if ($frontend_debug["module_display"] == true) {
                $fedebug .= "Modul: " . $oModule->get("name") . '\\\\n';
            }
            if ($frontend_debug["module_timing_summary"] == true || $frontend_debug["module_timing"] == true) {
                $fedebug .= 'Eval-Time: $modtime' . $value . '\\\\n';
                $output = '<?php $modstart' . $value . ' = getmicrotime(); ?' . '>' . $output . '<?php $modend' . $value . ' = getmicrotime()+0.001; $modtime' . $value . ' = $modend' . $value . ' - $modstart' . $value . '; ?' . '>';
            }

            if (!empty($fedebug)) {
                $output = addslashes('<?php echo \'<img onclick="javascript:showmod' . $value . '();" src="' . $cfg['path']['contenido_fullhtml'] . 'images/but_preview.gif">\'; ?' . '>' . "<br>") . $output;
                $output = $output . addslashes('<?php echo \'<script language="javascript">function showmod' . $value . ' () { window.alert(\\\'\'. "' . addslashes($fedebug) . '".\'\\\');} </script>\'; ?' . '>');
            }

            if ($frontend_debug["module_timing_summary"] == true) {
                $output .= addslashes(' <?php $cModuleTimes["' . $value . '"] = $modtime' . $value . '; ?>');
                $output .= addslashes(' <?php $cModuleNames["' . $value . '"] = "' . addslashes($oModule->get("name")) . '"; ?>');
            }
            /* Replace new containers */
            $code = preg_replace("/<container( +)id=\\\\\"$value\\\\\"(.*)>(.*)<\/container>/Uis", "CMS_CONTAINER[$value]", $code);

            $code = preg_replace("/<container( +)id=\\\\\"$value\\\\\"(.*)\/>/i", "CMS_CONTAINER[$value]", $code);

            $code = str_ireplace("CMS_CONTAINER[$value]", "<?php $CiCMS_VALUE ?>\r\n" . $output, $code);

            $fedebug = "";
        }
    }

    /* Find out what kind of CMS_... Vars are in use */
    $sql = "SELECT
	                    *
	                FROM
	                    " . $cfg["tab"]["content"] . " AS A,
	                    " . $cfg["tab"]["art_lang"] . " AS B,
	                    " . $cfg["tab"]["type"] . " AS C
	                WHERE
	                    A.idtype    = C.idtype AND
	                    A.idartlang = B.idartlang AND
	                    B.idart     = '" . Contenido_Security::toInteger($idart) . "' AND
	                    B.idlang    = '" . Contenido_Security::escapeDB($lang, $db) . "'";

    $db->query($sql);

    while ($db->next_record()) {
        $a_content[$db->f("type")][$db->f("typeid")] = $db->f("value");
    }

    $sql = "SELECT idartlang, pagetitle FROM " . $cfg["tab"]["art_lang"] . " WHERE idart='" . Contenido_Security::toInteger($idart) . "' AND idlang='" . Contenido_Security::escapeDB($lang, $db) . "'";

    $db->query($sql);
    $db->next_record();

    $idartlang = $db->f("idartlang");

    $pagetitle = stripslashes($db->f("pagetitle"));

    if ($pagetitle == '') {
        CEC_Hook::setDefaultReturnValue($pagetitle);
        $pagetitle = CEC_Hook::executeAndReturn('Contenido.Content.CreateTitletag');
    }

    /* replace all CMS_TAGS[] */
    $sql = "SELECT type, code FROM " . $cfg["tab"]["type"];

    $db->query($sql);

    $match = array();
    while ($db->next_record()) {

        $tmp = preg_match_all("/(" . $db->f("type") . "\[+\d+\])/i", $code, $match);
        $a_[strtolower($db->f("type"))] = $match[0];

        $success = array_walk($a_[strtolower($db->f("type"))], 'extractNumber');

        $search = array();
        $replacements = array();

        foreach ($a_[strtolower($db->f("type"))] as $val) {
            eval($db->f("code"));

            $search[$val] = $db->f("type") . "[$val]";
            $replacements[$val] = $tmp;
            $keycode[$db->f("type")][$val] = $tmp;
        }

        $code = str_ireplace($search, $replacements, $code);
    }

    /* add/replace title */
    if ($pagetitle != "") {
        $code = preg_replace("/<title>.*?<\/title>/is", "{TITLE}", $code, 1);

        if (strstr($code, "{TITLE}")) {
            $code = str_ireplace("{TITLE}", addslashes("<title>$pagetitle</title>"), $code);
        } else {
            $code = str_ireplace_once("</head>", addslashes("<title>" . $pagetitle . "</title>\n</head>"), $code);
        }
    } else {
        $code = str_replace('<title></title>', '', $code);
    }

    // metatags
    $availableTags = conGetAvailableMetaTagTypes();
    $metatags = array();
    foreach ($availableTags as $key => $value) {
        $metavalue = conGetMetaValue($idartlang, $key);
        if (strlen($metavalue) > 0) {
            //$metatags[$value["name"]] = array(array("attribute" => $value["fieldname"], "value" => $metavalue), ...);
            $metatags[] = array($value["fieldname"] => $value["name"], 'content' => $metavalue);
        }
    }

    // generator tag
    $aVersion = explode('.', $cfg['version']);
    $sCLVersion = $aVersion[0] . '.' . $aVersion[1];
    $metatags[] = array('name' => 'generator', 'content' => 'CMS ConLite ' . $sCLVersion);

    // charset/encoding tag
    if (getEffectiveSetting('generator', 'html5', "false") == "true") {
        $metatags[] = array('charset' => $encoding[$lang]);
    } else if (getEffectiveSetting('generator', 'xhtml', "false") == "true") {
        $metatags[] = array('http-equiv' => 'Content-Type', 'content' => 'application/xhtml+xml; charset=' . $encoding[$lang]);
    } else {
        $metatags[] = array('http-equiv' => 'Content-Type', 'content' => 'text/html; charset=' . $encoding[$lang]);
    }

    // check chains
    $_cecIterator = $_cecRegistry->getIterator("Contenido.Content.CreateMetatags");

    if ($_cecIterator->count() > 0) {
        $tmpMetatags = $metatags;
        if (!is_array($tmpMetatags)) {
            $tmpMetatags = array();
        }

        while ($chainEntry = $_cecIterator->next()) {
            $tmpMetatags = $chainEntry->execute($tmpMetatags);
        }

        //added 2008-06-25 Timo Trautmann 
        //system metatags were merged to user meta tags 
        //and user meta tags were not longer replaced by system meta tags
        /** @todo recode whole meta tag handling */
        if (is_array($tmpMetatags)) {
            //check for all system meta tags if there is already a user meta tag
            foreach ($tmpMetatags as $aAutValue) {
                $bExists = false;

                //get name of meta tag for search
                $sSearch = '';
                if (array_key_exists('name', $aAutValue)) {
                    $sSearch = $aAutValue['name'];
                } else if (array_key_exists('http-equiv', $aAutValue)) {
                    $sSearch = $aAutValue['http-equiv'];
                }

                //check if meta tag is already in list of user meta tags
                if (strlen($sSearch) > 0) {
                    foreach ($metatags as $aValue) {
                        if (array_key_exists('name', $aValue)) {
                            if ($sSearch == $aValue['name']) {
                                $bExists = true;
                                break;
                            }
                        } else if (array_key_exists('http-equiv', $aAutValue)) {
                            if ($sSearch == $aValue['http-equiv']) {
                                $bExists = true;
                                break;
                            }
                        }
                    }
                }

                //add system meta tag if there is no user meta tag
                if ($bExists == false && isset($aAutValue['content']) && strlen($aAutValue['content']) > 0) {
                    array_push($metatags, $aAutValue);
                }
            }
        }
    }
    $sMetatags = '';

    foreach ($metatags as $value) {
        if (getEffectiveSetting('generator', 'html5', "false") == "true") {
            if (isset($value['name']) && $value['name'] == 'date')
                continue;
        }
        if (!empty($value['content'])) {
            $value['content'] = clHtmlEntityDecode($value['content'], ENT_QUOTES, strtoupper($encoding[$lang]));
            $value['content'] = htmlspecialchars_decode($value['content'], ENT_QUOTES);
        }

        // build up metatag string
        $oMetaTagGen = new cHTML5Meta();
        $oMetaTagGen->updateAttributes($value);

        /* HTML does not allow ID for meta tags */
        $oMetaTagGen->removeAttribute("id");

        /* Check if metatag already exists */
        if (isset($value["name"]) && preg_match('/(<meta(?:\s+)name(?:\s*)=(?:\s*)(?:\\\\"|\\\\\')(?:\s*)' . $value["name"] . '(?:\s*)(?:\\\\"|\\\\\')(?:[^>]+)>\r?\n?)/i', $code, $aTmetatagfound)) {
            $code = str_replace($aTmetatagfound[1], $oMetaTagGen->render() . "\n", $code);
        } else if (array_key_exists("charset", $value)
                && preg_match('/(<meta(?:\s+)charset(?:\s*)=(?:\s*)(?:\\\\"|\\\\\')(?:\s*)(.*)(?:\s*)(?:\\\\"|\\\\\')(?:\s*)(?:\s*|\/)(?:[\^\>]+)\r?\n?)/i', $code, $aTmetatagfound)) {
            $code = str_replace($aTmetatagfound[1], $oMetaTagGen->render() . "\n", $code);
        } else {
            $sMetatags .= $oMetaTagGen->render() . "\n";
        }
    }

    /* Add meta tags */
    $code = str_ireplace_once("</head>", $sMetatags . "</head>", $code);
    $code = str_ireplace_once("</html>", "<!-- This website is powered by ConLite, the lightweight content management system.\nFor more info and download visit www.conlite.org -->\n</html>", $code);

    /* write code into the database */
    $date = date("Y-m-d H:i:s");

    if ($layout == false) {
        $sql = "SELECT * FROM " . $cfg["tab"]["code"] . " WHERE idcatart = '" . Contenido_Security::toInteger($idcatart) . "' AND idlang = '" . Contenido_Security::escapeDB($lang, $db) . "'";

        $db->query($sql);

        if ($db->next_record()) {
            if ($debug)
                echo "UPDATED code for lang:$lang, client:$client, idcatart:$idcatart";
            $sql = "UPDATE " . $cfg["tab"]["code"] . " SET code='" . Contenido_Security::escapeDB($code, $db, false) . "', idlang='" . Contenido_Security::escapeDB($lang, $db) . "', idclient='" . Contenido_Security::escapeDB($client, $db) . "'
					WHERE idcatart='" . Contenido_Security::toInteger($idcatart) . "' AND idlang='" . Contenido_Security::escapeDB($lang, $db) . "'";
            $db->query($sql);
        } else {
            if ($debug)
                echo "INSERTED code for lang:$lang, client:$client, idcatart:$idcatart";
            $sql = "INSERT INTO " . $cfg["tab"]["code"] . " (idcode, idcatart, code, idlang, idclient) VALUES ('" . Contenido_Security::toInteger($db->nextid($cfg["tab"]["code"])) . "', '" . Contenido_Security::toInteger($idcatart) . "',
					'" . Contenido_Security::escapeDB($code, $db, false) . "', '" . Contenido_Security::escapeDB($lang, $db) . "', '" . Contenido_Security::escapeDB($client, $db) . "')";
            $db->query($sql);
        }

        $sql = "UPDATE " . $cfg["tab"]["cat_art"] . " SET createcode = '0' WHERE idcatart='" . Contenido_Security::toInteger($idcatart) . "'";
        $db->query($sql);
    }

    // execute CEC hook
    $code = CEC_Hook::executeAndReturn('Contenido.Content.conGenerateCode', $code);

    return $code;
}

/**
 * Returns the idartlang for a given article and language
 * 
 * @deprecated since version 2.0, use renamed conGetArtLang instead
 * @param $idart ID of the article
 * @param $idlang ID of the language
 * @return mixed idartlang of the article or false if nothing was found
 */
function getArtLang($idart, $idlang) {
    return conGetArtLang($idart, $idlang);
}

/**
 * Returns the idartlang for a given article and language
 * 
 * @param $idart ID of the article
 * @param $idlang ID of the language
 * @return mixed idartlang of the article or false if nothing was found
 */
function conGetArtLang($iIdart, $iIdlang) {
    $oArtLangs = new cApiArticleLanguageCollection();
    $iRet = $oArtLangs->getIdArtLang($iIdart, $iIdlang);
    unset($oArtLangs); // save mem
    return $iRet;
}

/**
 * Returns all available meta tag types
 * 
 * @return array Array with available meta types
 */
function conGetAvailableMetaTagTypes() {
    $oMetaTypes = new cApiMetaTypeCollection();
    $aRet = $oMetaTypes->getAvailableMetaTypes();
    unset($oMetaTypes); // save mem
    return $aRet;
}

/**
 * Get the meta tag value for a specific article
 * 
 * @param int $idartlang
 * @param int $idmetatype
 * @return string tag value or empty string
 */
function conGetMetaValue($idartlang, $idmetatype) {
    $sRet = "";
    if (!empty($idartlang)) {
        $oMetaTags = new cApiMetaTagCollection();
        $oMetaTags->setWhere('idartlang', Contenido_Security::toInteger($idartlang));
        $oMetaTags->setWhere('idmetatype', Contenido_Security::toInteger($idmetatype));
        $oMetaTags->query();

        if ($oMetaTags->count() > 0) {
            $sRet = $oMetaTags->next()->get('metavalue');
        }
        unset($oMetaTags); // save mem
    }
    return $sRet;
}

/**
 * Set the meta tag value for a specific article
 *
 * @param $idartlang ID of the article
 * @param $idmetatype Metatype-ID
 * @param $value Value of the meta tag
 *
 * @author Timo A. Hummel <Timo.Hummel@4fb.de>
 * @copyright four for business AG 2003
 */
function conSetMetaValue($idartlang, $idmetatype, $value) {
    global $cfg;

    $db = new DB_ConLite;
    $sql = "DELETE FROM " . $cfg["tab"]["meta_tag"] . "
			WHERE idartlang = '" . Contenido_Security::toInteger($idartlang) . "'
			AND idmetatype = '" . Contenido_Security::toInteger($idmetatype) . "'";

    $db->query($sql);

    $nextid = $db->nextid($cfg["tab"]["meta_tag"]);

    $sql = "INSERT INTO " . $cfg["tab"]["meta_tag"] . " SET idartlang = '" . Contenido_Security::toInteger($idartlang) . "',
			idmetatype = '" . Contenido_Security::toInteger($idmetatype) . "',
			idmetatag = '" . Contenido_Security::toInteger($nextid) . "',
			metavalue = '" . Contenido_Security::escapeDB($value, $db) . "'";

    $db->query($sql);
}

/**
 * 
 * @param int $client
 * @param int $lang
 */
function conGenerateKeywords(int $client = null, int $lang = null) {
    $aOptions = [];
    $aOptions['start'] = true;
    $aOptions['offline'] = true;
    $aOptions['client'] = $client ?? 0;
    $aOptions['lang'] = $lang ?? 0;

    $oArticleCollector = new cArticleCollector();
    $oArticleCollector->setOptions($aOptions);
    $oArticleCollector->loadArticles();
   /* @var $oArticle cApiArticleLanguage */
    if ($oArticleCollector->count() > 0) {
        foreach ($oArticleCollector as $oArticle) {
            $aArticleContent = [];
            $aArticleContent = $oArticle->getContent();
            if(!empty($aArticleContent)) {
                /* @var $oIndex Index */
                $oIndex = new Index();
                //$oIndex->setDebug(true);
                $oIndex->start($oArticle->get('idart'), $aArticleContent, 'auto', array("img", "link", "linktarget", "swf"));
            }
        }
    }
}

/**
 * get content from article 
 * @param $article_lang ArticleLanguageId of an article (idartlang) 
 * @return array Array with content of an article indexed by content-types
 *
 * @author Willi Man
 * Created   :   12.05.2004
 * Modified  :   13.05.2004
 * @copyright four for business AG 2003
 */
function conGetContentFromArticle($article_lang) {

    global $cfg;
    $db_con = new DB_ConLite;

    $sql = "SELECT
					*
				FROM
					" . $cfg["tab"]["content"] . " AS A,
					" . $cfg["tab"]["art_lang"] . " AS B,
					" . $cfg["tab"]["type"] . " AS C
				WHERE
					A.idtype    = C.idtype AND
					A.idartlang = B.idartlang AND
					A.idartlang     = '" . Contenido_Security::escapeDB($article_lang, $db_con) . "' ";

    $db_con->query($sql);

    while ($db_con->next_record()) {
        $a_content[$db_con->f("type")][$db_con->f("typeid")] = urldecode($db_con->f("value"));
    }

    return $a_content;
}

?>
