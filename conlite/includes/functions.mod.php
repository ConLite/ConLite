<?php

/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * Defines the "mod" related functions
 * 
 * Requirements: 
 * @con_php_req 5.0
 * 
 *
 * @package    Contenido Backend includes
 * @version    1.0.2
 * @author     Olaf Niemann, Jan Lengowski
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.6
 * 
 * {@internal 
 *   created 2003
 *   modified 2008-06-26, Frederic Schneider, add security fix
 *   modified 2010-08-13, Dominik Ziegler, fixed CON-337 - added update of lastmodified
 *
 *   $Id$:
 * }}
 * 
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

cInclude("includes", "functions.tpl.php");
cInclude("includes", "functions.con.php");

function modEditModule($idmod, $name, $description, $input, $output, $template, $type = "") {
    global $db, $client, $cfgClient, $auth, $cfg, $sess, $area_tree, $perm, $frame;

    $date = date("Y-m-d H:i:s");
    $author = $auth->auth["uname"];

    /**
     * START TRACK VERSION
     * */
    $oVersion = new VersionModule($idmod, $cfg, $cfgClient, $db, $client, $area, $frame);

    // Create new Module Version in cms/version/module/
    $oVersion->createNewVersion();

    /**
     * END TRACK VERSION
     * */
    if (!$idmod) {
        $cApiModuleCollection = new cApiModuleCollection;
        $cApiModule = $cApiModuleCollection->create($name);

        $idmod = $cApiModule->get("idmod");

        cInclude("includes", "functions.rights.php");
        createRightsForElement("mod", $idmod);
    } else {
        $cApiModule = new cApiModule;
        $cApiModule->loadByPrimaryKey($idmod);
    }

    /* dceModFileEdit (c)2009-2011 www.dceonline.de */
    if ($cfg['dceModEdit']['use']
            || $cApiModule->get("name") != stripslashes($name)
            || $cApiModule->get("output") != stripslashes($output)
            || $cApiModule->get("template") != stripslashes($template)
            || $cApiModule->get("description") != stripslashes($description)
            || $cApiModule->get("input") != stripslashes($input)
            || $cApiModule->get("type") != stripslashes($type)) {

        $cApiModule->set("name", $name);
        $cApiModule->set("output", $cApiModule->escape($output));
        $cApiModule->set("template", $template);
        $cApiModule->set("description", $description);
        $cApiModule->set("input", $cApiModule->escape($input));
        $cApiModule->set("type", $type);
        $cApiModule->set("lastmodified", $date);

        $cApiModule->store();
    }
    return $idmod;
}

function modDeleteModule($idmod) {
    # Global vars
    global $db, $sess, $client, $cfg, $area_tree, $perm;

    $sql = "DELETE FROM " . $cfg["tab"]["mod"] . " WHERE idmod = '" . Contenido_Security::toInteger($idmod) . "' AND idclient = '" . Contenido_Security::toInteger($client) . "'";
    $db->query($sql);

    // delete rights for element
    cInclude("includes", "functions.rights.php");
    deleteRightsForElement("mod", $idmod);
}

// $code: Code to evaluate
// $id: Unique ID for the test function
// $mode: true if start in php mode, otherwise false
// Returns true or false

function modTestModule($code, $id, $output = false) {
    global $cfg, $modErrorMessage;

    $db = new DB_ConLite();

    $sql = "SELECT type FROM " . $cfg["tab"]["type"];
    $db->query($sql);

    while ($db->next_record()) {
        $code = str_replace($db->f("type") . '[', '$' . $db->f("type") . '[', $code);
    }

    $code = preg_replace(',\[(\d+)?CMS_VALUE\[(\d+)\](\d+)?\],i', '[\1\2\3]', $code);

    $code = str_replace('CMS_VALUE', '$CMS_VALUE', $code);
    $code = str_replace('CMS_VAR', '$CMS_VAR', $code);

    if ($output == true) {
        $code = "?>\n" . $code . "\n<?php";
    }

    $code = "function foo" . $id . " () {" . $code;
    $code .= "\n}\n";
    if (defined('PHP_MAJOR_VERSION') && PHP_MAJOR_VERSION >= 5) {
        try {
            eval($code);
        } catch (ParseError $err) {
            $modErrorMessage = $err->getMessage() . " (line: " . ($err->getLine() - 1) . ")";
            return false;
        }

        return true;
    } else {
          // To parse the error message, we prepend and  append a phperror tag in front of the output
          $sErs = ini_get("error_prepend_string"); // Save current setting (see below)
          $sEas = ini_get("error_append_string");  // Save current setting (see below)
          @ini_set("error_prepend_string", "<phperror>");
          @ini_set("error_append_string", "</phperror>");

          // Turn off output buffering and error reporting, eval the code
          ob_start();
          $display_errors = ini_get("display_errors");
          @ini_set("display_errors", true);
          $output = eval($code);
          @ini_set("display_errors", $display_errors);

          // Get the buffer contents and turn it on again
          $output = ob_get_contents();
          ob_end_clean();

          @ini_set("error_prepend_string", $sErs); // Restoring settings (see above)
          @ini_set("error_append_string", $sEas); // Restoring settings (see above)

          // Strip out the error message
          $start = strpos($output, "<phperror>");
          $end = strpos($output, "</phperror>");

          // More stripping: Users shouldnt see where the file      is located, but they should see the error line
          if ($start !== false) {
          $start = strpos($output, "eval()");

          $modErrorMessage = substr($output, $start, $end - $start);

          // Kill that HTML formatting
          $modErrorMessage = str_replace("<b>", "", $modErrorMessage);
          $modErrorMessage = str_replace("</b>", "", $modErrorMessage);
          $modErrorMessage = str_replace("<br>", "", $modErrorMessage);
          $modErrorMessage = str_replace("<br />", "", $modErrorMessage);
          }

          // check if there are any php short tags in code, and display error
          $bHasShortTags = false;
          if (preg_match('/<\?\s+/', $code) && $magicvalue == 941) {
          $bHasShortTags = true;
          $modErrorMessage = i18n('Please do not use short open Tags. (Use <?php instead of <?).');
          }

          if ($bHasShortTags) {
          return false;
          } else {
          return true;
          }
    }
}
