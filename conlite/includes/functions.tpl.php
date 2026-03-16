<?php
/**
 * file: functions.tpl.php
 *
 * Template related functions
 *
 * @package ConLite\Includes\Functions\Template
 * @author Ortwin Pinke <ortwin.pinke@conlite.org>
 * @copryright 2025- ConLite Team
 * @link https://conlite.org
 * @version 2.0.0
 * @since ConLite V 3.0.0
 *
 */
/*
 * Project:
 * Contenido Content Management System
 *
 * Description:
 * Define the Template related functions
 *
 * Requirements:
 * @con_php_req 5.0
 *
 *
 * @package    Contenido Backend includes
 * @version    1.0.1
 * @author     Olaf Niemann
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.6
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

cInclude("includes", "functions.con.php");

/**
 * Edit or create a new Template
 */
function tplEditTemplate($changeLayout, $idTpl, $name, $description, $idLayout, $c, $default)
{

    global $db;
    global $sess;
    global $auth;
    global $client;
    global $cfg;

    $date = date("YmdHis");
    $author = $auth->auth["uname"];

    if (!$idTpl) {

        $idTpl = $db->nextid($cfg["tab"]["tpl"]);
        $idtplcfg = $db->nextid($cfg["tab"]["tpl_conf"]);

        /* Insert new entry in the
          Template Conf table */
        $sql = "INSERT INTO " . $cfg["tab"]["tpl_conf"] . "
                    (idtplcfg, idtpl, author) VALUES
                   ('" . Contenido_Security::toInteger($idtplcfg) . "', '" . Contenido_Security::toInteger($idTpl) . "', '" . Contenido_Security::escapeDB($auth->auth["uname"], $db) . "')";

        $db->query($sql);

        /* Insert new entry in the
          Template table */
        $sql = "INSERT INTO " . $cfg["tab"]["tpl"] . "
                    (idtpl, idtplcfg, name, description, deletable, idlay, idclient, author, created, lastmodified) VALUES
                    ('" . Contenido_Security::toInteger($idTpl) . "', '" . Contenido_Security::toInteger($idtplcfg) . "', '" . Contenido_Security::escapeDB($name, $db) . "', '" . Contenido_Security::escapeDB($description, $db) . "',
                    '1', '" . Contenido_Security::toInteger($idLayout) . "', '" . Contenido_Security::toInteger($client) . "', '" . Contenido_Security::escapeDB($author, $db) . "', '" . Contenido_Security::escapeDB($date, $db) . "',
                    '" . Contenido_Security::escapeDB($date, $db) . "')";

        $db->query($sql);

        // set correct rights for element
        cInclude("includes", "functions.rights.php");
        createRightsForElement("tpl", $idTpl);
    } else {

        /* Update */
        $sql = "UPDATE " . $cfg["tab"]["tpl"] . " SET name='" . Contenido_Security::escapeDB($name, $db) . "', description='" . Contenido_Security::escapeDB($description, $db) . "', idlay='" . Contenido_Security::toInteger($idLayout) . "',
                    author='" . Contenido_Security::escapeDB($author, $db) . "', lastmodified='" . Contenido_Security::escapeDB($date, $db) . "' WHERE idtpl='" . Contenido_Security::toInteger($idTpl) . "'";
        $db->query($sql);

        if (is_array($c)) {

            /* Delete all container assigned to this template */
            $sql = "DELETE FROM " . $cfg["tab"]["container"] . " WHERE idtpl='" . Contenido_Security::toInteger($idTpl, $db) . "'";
            $db->query($sql);

            foreach ($c as $idcontainer => $dummyval) {

                $sql = "INSERT INTO " . $cfg["tab"]["container"] . " (idcontainer, idtpl, number, idmod) VALUES ";
                $sql .= "(";
                $sql .= "'" . Contenido_Security::toInteger($db->nextid($cfg["tab"]["container"])) . "', ";
                $sql .= "'" . Contenido_Security::toInteger($idTpl) . "', ";
                $sql .= "'" . Contenido_Security::toInteger($idcontainer) . "', ";
                $sql .= "'" . Contenido_Security::toInteger($c[$idcontainer]) . "'";
                $sql .= ") ";
                $db->query($sql);
            }
        }

        /* Generate code */
        conGenerateCodeForAllartsUsingTemplate($idTpl);
    }

    if ($default == 1) {
        $sql = "UPDATE " . $cfg["tab"]["tpl"] . " SET defaulttemplate = '0' WHERE idclient = '" . cRegistry::getClientId() . "'";
        $db->query($sql);

        $sql = "UPDATE " . $cfg["tab"]["tpl"] . " SET defaulttemplate = '1' WHERE idtpl = '" . Contenido_Security::toInteger($idTpl) . "' AND idclient = '" . Contenido_Security::toInteger($client) . "'";
        $db->query($sql);
    } else {
        $sql = "UPDATE " . $cfg["tab"]["tpl"] . " SET defaulttemplate = '0' WHERE idtpl = '" . Contenido_Security::toInteger($idTpl) . "' AND idclient = '" . Contenido_Security::toInteger($client) . "'";
        $db->query($sql);
    }


    //******** if layout is changed stay at 'tpl_edit' otherwise go to 'tpl'
    if ($changeLayout != 1) {
        $url = $sess->url("main.php?area=tpl_edit&idtpl=$idTpl&frame=4");
        header("location: $url");
    }

    return $idTpl;
}

/**
 * Delete a template
 *
 * @param int $idtpl ID of the template to duplicate
 *
 * @return $new_idtpl ID of the duplicated template
 * @author Olaf Niemann <Olaf.Niemann@4fb.de>
 * @author Jan Lengowski <Jan.Lengowski@4fb.de>
 * @copyright four for business AG <www.4fb.>
 */
function tplDeleteTemplate($idtpl)
{

    global $db, $client, $lang, $cfg, $area_tree, $perm;

    $sql = "DELETE FROM " . $cfg["tab"]["tpl"] . " WHERE idtpl='" . Contenido_Security::toInteger($idtpl) . "'";
    $db->query($sql);

    /* JL 160603 : Delete all unnecessary entries */

    $sql = "DELETE FROM " . $cfg["tab"]["container"] . " WHERE idtpl = '" . Contenido_Security::toInteger($idtpl) . "'";
    $db->query($sql);

    $idsToDelete = array();
    $sql = "SELECT idtplcfg FROM " . $cfg["tab"]["tpl_conf"] . " WHERE idtpl = '" . Contenido_Security::toInteger($idtpl) . "'";
    $db->query($sql);
    while ($db->next_record()) {
        $idsToDelete[] = $db->f("idtplcfg");
    }

    foreach ($idsToDelete as $id) {

        $sql = "DELETE FROM " . $cfg["tab"]["tpl_conf"] . " WHERE idtplcfg = '" . Contenido_Security::toInteger($id) . "'";
        $db->query($sql);

        $sql = "DELETE FROM " . $cfg["tab"]["container_conf"] . " WHERE idtplcfg = '" . Contenido_Security::toInteger($id) . "'";
        $db->query($sql);
    }

    cInclude("includes", "functions.rights.php");
    deleteRightsForElement("tpl", $idtpl);
}

/**
 * Browse a specific layout for containers
 *
 * @param int $idtpl Layout number to browse
 *
 * @return string &-seperated String of all containers
 *
 * @author Jan Lengowski <Jan.Lengowski@4fb.de>
 * @copyright four for business AG <www.4fb.>
 */
function tplBrowseLayoutForContainers($idlay, $raw_code = NULL)
{
    global $db;
    global $cfg;
    global $containerinf;

    if (is_null($raw_code) || empty($raw_code)) {
        $sql = "SELECT code FROM " . $cfg["tab"]["lay"] . " WHERE idlay='" . Contenido_Security::toInteger($idlay) . "'";
        $db->query($sql);
        $db->next_record();
        $code = $db->f("code");
    } else {
        $code = $raw_code;
    }

    preg_match_all("/CMS_CONTAINER\[([0-9]*)\]/", $code, $a_container);
    $iPosBody = stripos($code, '<body>');
    $sCodeBeforeHeader = substr($code, 0, $iPosBody);
    if (!empty($a_container)) {
        foreach ($a_container[1] as $value) {
            if (preg_match("/CMS_CONTAINER\[$value\]/", $sCodeBeforeHeader)) {
                $containerinf[$idlay][$value]["is_body"] = false;
            } else {
                $containerinf[$idlay][$value]["is_body"] = true;
            }
        }
    }

    if (is_array($containerinf[$idlay])) {
        foreach ($containerinf[$idlay] as $key => $value) {
            $a_container[1][] = $key;
        }
    }

    $container = array();

    foreach ($a_container[1] as $value) {
        if (!in_array($value, $container)) {
            $container[] = $value;
        }
    }

    asort($container);

    if (is_array($container) && !empty($container)) {
        $tmp_returnstring = implode("&", $container);
    } else {
        $tmp_returnstring = "";
    }
    return $tmp_returnstring;
}

/**
 * Retrieve the container name
 *
 * @param int $idtpl Layout number to browse
 * @param int $container Container number
 *
 * @return string Container name
 */
function tplGetContainerName($idlay, $container)
{
    global $containerinf;

    if (is_array($containerinf[$idlay])) {
        if (array_key_exists($container, $containerinf[$idlay])) {
            return $containerinf[$idlay][$container]["name"];
        }
    }
}

/**
 * Retrieve the container mode
 *
 * @param int $idtpl Layout number to browse
 * @param int $container Container number
 *
 * @return string Container name
 */
function tplGetContainerMode($idlay, $container)
{
    global $containerinf;

    if (is_array($containerinf[$idlay])) {
        if (array_key_exists($container, $containerinf[$idlay])) {
            return $containerinf[$idlay][$container]["mode"];
        }
    }
}

/**
 * Retrieve the allowed container types
 *
 * @param int $idtpl Layout number to browse
 * @param int $container Container number
 *
 * @return array Allowed container types
 */
function tplGetContainerTypes($idlay, $container)
{
    global $containerinf;

    $list = array();

    if (is_array($containerinf[$idlay])) {
        if (array_key_exists($container, $containerinf[$idlay])) {
            if ($containerinf[$idlay][$container]["types"] != "") {
                $list = explode(",", $containerinf[$idlay][$container]["types"]);

                foreach ($list as $key => $value) {
                    $list[$key] = trim($value);
                }
            }
        }
    }
    return $list;
}

/**
 * Retrieve the default module
 *
 * @param int $idtpl Layout number to browse
 * @param int $container Container number
 *
 * @return array Allowed container types
 */
function tplGetContainerDefault($idlay, $container)
{
    global $containerinf;

    if (is_array($containerinf[$idlay])) {
        if (array_key_exists($container, $containerinf[$idlay])) {
            return $containerinf[$idlay][$container]["default"];
        }
    }
}

/**
 * Preparse the layout for caching purposes
 *
 * @param int $idtpl Layout number to browse
 *
 * @return none
 */
function tplPreparseLayout($idlay, $raw_code = NULL)
{
    global $containerinf;
    global $db;
    global $cfg;

    if (is_null($raw_code) || empty($raw_code)) {
        $sql = "SELECT code FROM " . $cfg["tab"]["lay"] . " WHERE idlay='" . Contenido_Security::toInteger($idlay) . "'";
        $db->query($sql);
        $db->next_record();
        $code = $db->f("code");
    } else {
        $code = $raw_code;
    }

    $parser = new HtmlParser($code);
    $bIsBody = false;
    while ($parser->parse()) {
        if (strtolower($parser->iNodeName) == 'body') {
            $bIsBody = true;
        }

        if ($parser->iNodeName == "container" && $parser->iNodeType == NODE_TYPE_ELEMENT) {
            $idcontainer = $parser->iNodeAttributes["id"];

            $sMode = (isset($parser->iNodeAttributes["mode"])) ? $parser->iNodeAttributes["mode"] : 'optional';
            $sDefault = (isset($parser->iNodeAttributes["default"])) ? $parser->iNodeAttributes["default"] : '';
            $sTypes = (isset($parser->iNodeAttributes["types"])) ? $parser->iNodeAttributes["types"] : '';

            $containerinf[$idlay][$idcontainer]["name"] = $parser->iNodeAttributes["name"];
            $containerinf[$idlay][$idcontainer]["mode"] = $sMode;
            $containerinf[$idlay][$idcontainer]["default"] = $sDefault;
            $containerinf[$idlay][$idcontainer]["types"] = $sTypes;
            $containerinf[$idlay][$idcontainer]["is_body"] = $bIsBody;
        }
    }
}

/**
 * Duplicate a template
 *
 * @param int $idtpl ID of the template to duplicate
 *
 * @return $new_idtpl ID of the duplicated template
 *
 * @author Jan Lengowski <Jan.Lengowski@4fb.de>
 * @copyright four for business AG <www.4fb.>
 */
function tplDuplicateTemplate($idtpl)
{

    global $db, $client, $lang, $cfg, $sess, $auth;

    $db2 = new DB_ConLite;

    $sql = "SELECT
                *
            FROM
                " . $cfg["tab"]["tpl"] . "
            WHERE
                idtpl = '" . Contenido_Security::toInteger($idtpl) . "'";

    $db->query($sql);
    $db->next_record();

    $idclient = $db->f("idclient");
    $idlay = $db->f("idlay");
    $new_idtpl = $db->nextid($cfg["tab"]["tpl"]);
    //modified (added) 2008-06-30 timo.trautmann added fix module settings were also copied
    $idtpl_conf = $db->f("idtplcfg");
    if ($idtpl_conf) {
        $new_idtpl_conf = $db->nextid($cfg["tab"]["tpl_conf"]);
    }
    //modified (added) 2008-06-30 end
    $name = sprintf(i18n("%s (Copy)"), $db->f("name"));
    $descr = $db->f("description");
    $author = $auth->auth["uname"];
    $created = time();
    $lastmod = time();

    //modified (added) 2008-06-30 : idtplcfg ->  $new_idtpl 
    $sql = "INSERT INTO
                " . $cfg["tab"]["tpl"] . "
                (idclient, idlay, idtpl, " . ($idtpl_conf ? 'idtplcfg,' : '') . " name, description, deletable,author, created, lastmodified)
            VALUES
                ('" . Contenido_Security::toInteger($idclient) . "', '" . Contenido_Security::toInteger($idlay) . "', '" . Contenido_Security::toInteger($new_idtpl) . "',  " . ($idtpl_conf ? "'" . Contenido_Security::toInteger($new_idtpl_conf) . "', " : '') . " '" . Contenido_Security::escapeDB($name, $db) . "',
                 '" . Contenido_Security::escapeDB($descr, $db) . "', '1', '" . Contenido_Security::escapeDB($author, $db) . "', '" . Contenido_Security::escapeDB($created, $db) . "', '" . Contenido_Security::escapeDB($lastmod, $db) . "')";
    $db->query($sql);

    $a_containers = array();

    $sql = "SELECT
                *
            FROM
                " . $cfg["tab"]["container"] . "
            WHERE
                idtpl = '" . Contenido_Security::toInteger($idtpl) . "'
            ORDER BY
                number";

    $db->query($sql);

    while ($db->next_record()) {
        $a_containers[$db->f("number")] = $db->f("idmod");
    }

    foreach ($a_containers as $key => $value) {

        $nextid = $db->nextid($cfg["tab"]["container"]);

        $sql = "INSERT INTO " . $cfg["tab"]["container"] . "
                (idcontainer, idtpl, number, idmod) VALUES ('" . Contenido_Security::toInteger($nextid) . "', '" . Contenido_Security::toInteger($new_idtpl) . "', '" . Contenido_Security::toInteger($key) . "', '" . Contenido_Security::toInteger($value) . "')";

        $db->query($sql);
    }

    //modified (added) 2008-06-30 timo.trautmann added fix module settings were also copied
    if ($idtpl_conf) {
        $a_container_cfg = array();
        $sql = "SELECT
					   *
				 FROM
					   " . $cfg["tab"]["container_conf"] . "
				 WHERE
					   idtplcfg = '" . Contenido_Security::toInteger($idtpl_conf) . "'
				 ORDER BY
					   number";

        $db->query($sql);

        while ($db->next_record()) {
            $a_container_cfg[$db->f("number")] = $db->f("container");
        }

        foreach ($a_container_cfg as $key => $value) {

            $nextid = $db->nextid($cfg["tab"]["container_conf"]);

            $sql = "INSERT INTO " . $cfg["tab"]["container_conf"] . "
					   (idcontainerc, idtplcfg, number, container) VALUES ('" . Contenido_Security::toInteger($nextid) . "', '" . Contenido_Security::toInteger($new_idtpl_conf) . "', '" . Contenido_Security::escapeDB($key, $db) . "', '" . Contenido_Security::escapeDB($value, $db) . "')";

            $db->query($sql);
        }
    }
    //modified (added) 2008-06-30 end

    cInclude("includes", "functions.rights.php");
    copyRightsForElement("tpl", $idtpl, $new_idtpl);

    return $new_idtpl;
}

/**
 * Checks if a template is in use
 *
 * @param int $idTpl id of template
 * @return bool
 */
function tplIsTemplateInUse(int $idTpl): bool
{

    $db = cRegistry::getDb();
    // Check categorys 
    $sql = "SELECT
               	b.idcatlang, b.name, b.idlang, b.idcat   
            FROM
                " . cRegistry::getConfigValue('tab', 'cat') . " AS a,
            	" . cRegistry::getConfigValue('tab', 'cat_lang') . " AS b
            WHERE
                a.idclient  = '" . cRegistry::getClientId() . "' AND
                a.idcat     = b.idcat AND
                b.idtplcfg  IN (SELECT idtplcfg FROM " . cRegistry::getConfigValue('tab', 'tpl_conf') . " WHERE idtpl = '" . $idTpl . "')  
            ORDER BY b.idlang ASC, b.name ASC ";
    $db->query($sql);
    if ($db->getErrno() == 0 && $db->num_rows() > 0) {
        return true;
    }

    // Check articles 
    $sql = "SELECT
           		b.idartlang, b.title, b.idlang, b.idart   
            FROM
                " . cRegistry::getConfigValue('tab', 'art') . " AS a,
                " . cRegistry::getConfigValue('tab', 'art_lang') . " AS b
            WHERE
                a.idclient  = '" . cRegistry::getClientId() . "' AND
                a.idart     = b.idart AND
                b.idtplcfg IN (SELECT idtplcfg FROM " . cRegistry::getConfigValue('tab', 'tpl_conf') . " WHERE idtpl = '" . $idTpl . "')  
            ORDER BY b.idlang ASC, b.title ASC ";

    $db->query($sql);

    if ($db->getErrno() == 0 && $db->num_rows() > 0) {
        return true;
    }

    return false;
}


/**
 * Get used data if a template is in use
 *
 * @param int $idTpl id of the template
 * @return array result for category and article
 */
function tplGetInUsedData(int $idTpl): array
{
    $db = new DB_ConLite();
    $aUsedData = [];

    // Check categorys 
    $sql = "SELECT
               	b.idcatlang, b.name, b.idlang, b.idcat   
            FROM
                " . cRegistry::getConfigValue('tab', 'cat') . " AS a,
            	" . cRegistry::getConfigValue('tab', 'cat_lang') . " AS b
            WHERE
                a.idclient  = '" . cRegistry::getClientId() . "' AND
                a.idcat     = b.idcat AND
                b.idtplcfg  IN (SELECT idtplcfg FROM " . cRegistry::getConfigValue('tab', 'tpl_conf') . " WHERE idtpl = '" . $idTpl . "')  
            ORDER BY b.idlang ASC, b.name ASC ";
    $db->query($sql);

    if ($db->getError() == 0 && $db->num_rows() > 0) {
        while ($db->nextRecord()) {
            $aUsedData['cat'][] = array(
                'name' => $db->f('name'),
                'lang' => $db->f('idlang'),
                'idcat' => $db->f('idcat'),
            );
        }
    }

    // Check articles
    $sql = "SELECT
           		b.idartlang, b.title, b.idlang, b.idart   
            FROM
                " . cRegistry::getConfigValue('tab', 'art') . " AS a,
                " . cRegistry::getConfigValue('tab', 'art_lang') . " AS b
            WHERE
                a.idclient  = '" . cRegistry::getClientId() . "' AND
                a.idart     = b.idart AND
                b.idtplcfg IN (SELECT idtplcfg FROM " . cRegistry::getConfigValue('tab', 'tpl_conf') . " WHERE idtpl = '" . $idTpl . "')  
            ORDER BY b.idlang ASC, b.title ASC ";

    $db->query($sql);

    if ($db->getErrno() == 0 && $db->num_rows() > 0) {
        while ($db->nextRecord()) {
            $aUsedData['art'][] = array(
                'title' => $db->f('title'),
                'lang' => $db->f('idlang'),
                'idart' => $db->f('idart'),
            );
        }
    }

    return $aUsedData;
}


/**
 * Copies a complete template configuration
 *
 * @param int $idTplCfg id of tplcfg to duplicate
 * @return int new id of tplcfg or 0
 */
function tplcfgDuplicate(int $idTplCfg): int
{
    $db = new DB_ConLite();
    $db2 = new DB_ConLite();
    $newIdTplCfg = 0;

    $sql = "SELECT
				idtpl, status, author, created, lastmodified
			FROM
				" . cRegistry::getConfigValue('tab', 'tpl_conf') . "
			WHERE
				idtplcfg = " . $idTplCfg;

    $db->query($sql);

    if ($db->nextRecord()) {
        $newIdTplCfg = (int)$db2->nextid(cRegistry::getConfigValue('tab', 'tpl_conf'));
        $idTpl = (int)$db->f("idtpl");
        $status = (int)$db->f("status");
        $author = $db->f("author");
        $created = $db->f("created");
        $lastModified = $db->f("lastmodified");

        $sql = "INSERT INTO
				" . cRegistry::getConfigValue('tab', 'tpl_conf') . "
				(idtplcfg, idtpl, status, author, created, lastmodified)
				VALUES
				(" . $newIdTplCfg . ", " . $idTpl . ", " . $status . ", '" . Contenido_Security::escapeDB($author) . "',
				'" . Contenido_Security::escapeDB($created) . "', '" . Contenido_Security::escapeDB($lastModified) . "')";

        $db2->query($sql);

        /* Copy container configuration */
        $sql = "SELECT 
    				number, container
    			FROM
    				" . cRegistry::getConfigValue('tab', 'container_conf') . "
    			WHERE idtplcfg = " . $idTplCfg;

        $db->query($sql);

        while ($db->nextRecord()) {
            $newIdContainerCfg = (int)$db2->nextid(cRegistry::getConfigValue('tab', 'container_conf'));
            $number = (int)$db->f("number");
            $container = $db->f("container");

            $sql = "INSERT INTO
    				" . cRegistry::getConfigValue('tab', 'container_conf') . "
    				(idcontainerc, idtplcfg, number, container)
    				VALUES
    				(" . $newIdContainerCfg . ", " . $newIdTplCfg . ", " . $number . ", '" . Contenido_Security::escapeDB($container) . "')";
            $db2->query($sql);
        }
    }

    return ($newIdTplCfg);
}

/**
 * tplAutoFillModules
 *
 * This function fills in modules automatically using this logic:
 *
 * - If the container mode is fixed, insert the named module (if exists)
 * - If the container mode is mandatory, insert the "default" module (if exists)
 *
 * @param int $idTpl
 * @return false|void
 * @todo  The default module is only inserted in mandatory mode if the container
 *       is empty. We need a better logic for handling "changes". *
 *
 *
 */
function tplAutoFillModules(int $idTpl)
{
    global $containerinf;
    global $_autoFillcontainerCache;

    $db_autofill = cRegistry::getDb();

    $sql = "SELECT idlay FROM " . cRegistry::getConfigValue('tab', 'tpl') . " WHERE idtpl = " . $idTpl;
    $db_autofill->query($sql);

    if (!$db_autofill->nextRecord()) {
        return false;
    }

    $idLay = (int)$db_autofill->f("idlay");

    if (!(is_array($containerinf) && array_key_exists($idLay, $containerinf) && array_key_exists($idLay, $_autoFillcontainerCache))) {
        tplPreparseLayout($idLay);
        $_autoFillcontainerCache[$idLay] = tplBrowseLayoutForContainers($idLay);
    }

    $a_container = explode("&", $_autoFillcontainerCache[$idLay]);

    foreach ($a_container as $container) {
        $container = (int)$container;

        switch ($containerinf[$idLay][$container]["mode"]) {
            /* Fixed mode */
            case "fixed":
                if ($containerinf[$idLay][$container]["default"] != "") {
                    $sql = "SELECT idmod FROM " . cRegistry::getConfigValue('tab', 'mod')
                        . " WHERE name = '" .
                        Contenido_Security::escapeDB($containerinf[$idLay][$container]["default"]) . "'";

                    $db_autofill->query($sql);

                    if ($db_autofill->nextRecord()) {
                        $idMod = (int)$db_autofill->f("idmod");

                        $sql = "SELECT idcontainer FROM " . cRegistry::getConfigValue('tab', 'container')
                            . " WHERE idtpl = " . $idTpl . " AND number = " . $container;

                        $db_autofill->query($sql);

                        if ($db_autofill->nextRecord()) {
                            $sql = "UPDATE " . cRegistry::getConfigValue('tab', 'container')
                                . " SET idmod = " . $idMod . " WHERE idtpl = " . $idTpl
                                . " AND number = " . $container . " AND "
                                . " idcontainer = " . (int)$db_autofill->f("idcontainer");
                            $db_autofill->query($sql);
                        } else {
                            $sql = "INSERT INTO " . cRegistry::getConfigValue('tab', 'container')
                                . " (idcontainer, idtpl, number, idmod) VALUES (" . (int)$db_autofill->nextid(cRegistry::getConfigValue('tab', 'container'))
                                . ", " . $idTpl . ", " . $container . ", " . $idMod . ")";
                            $db_autofill->query($sql);
                        }
                    }
                }


            case "mandatory":

                if ($containerinf[$idLay][$container]["default"] != "") {
                    $sql = "SELECT idmod FROM " . cRegistry::getConfigValue('tab', 'mod')
                        . " WHERE name = '" .
                        Contenido_Security::escapeDB($containerinf[$idLay][$container]["default"]) . "'";

                    $db_autofill->query($sql);

                    if ($db_autofill->nextRecord()) {
                        $idMod = (int)$db_autofill->f("idmod");

                        $sql = "SELECT idcontainer, idmod FROM " . cRegistry::getConfigValue('tab', 'container')
                            . " WHERE idtpl = " . $idTpl . " AND number = " . $container;

                        $db_autofill->query($sql);

                        if (!$db_autofill->nextRecord()) {
                            $sql = "INSERT INTO " . cRegistry::getConfigValue('tab', 'container')
                                . " (idcontainer, idtpl, number, idmod)  VALUES (" . (int)$db_autofill->nextid(cRegistry::getConfigValue('tab', 'container'))
                                . ", " . $idTpl . ", " . $container . ", " . $idMod . ")";
                            $db_autofill->query($sql);
                        }
                    }
                }
        }
    }
}