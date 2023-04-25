<?php

/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * Stringlist for module translation
 * 
 * Requirements: 
 * @con_php_req 5.0
 * 
 *
 * @package    Contenido Backend includes
 * @version    1.0.1
 * @author     unknown
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.6
 * 
 * {@internal 
 *   created unknown
 *   modified 2008-06-27, Frederic Schneider, add security fix
 *
 *   $Id$:
 * }}
 * 
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}


$translations = new cApiModuleTranslationCollection;
$translations->select("idmod = '$idmod' AND idlang='$lang'");

$page = new cPage;
$page->setHtml5();
$page->setEncoding('UTF-8');
$page->setMargin(0);

$v = '<table cellspacing="0" cellpadding="0" width="600">';

$link = new cHTMLLink;
$link->setCLink("mod_translate", 4, "");

$mylink = new cHTMLLink;

while ($translation = $translations->next()) {

    $string = utf8_encode($translation->get("original"));
    $tstring = utf8_encode($translation->get("translation"));

    $link->setCustom("idmod", $idmod);
    $link->setCustom("idmodtranslation", $translation->get("idmodtranslation"));
    $href = $link->getHREF();

    $mylink->setLink('javascript:parent.location="' . $href . '"');
    $mylink->setContent($string);

    $dark = !$dark;

    if ($dark) {
        $bgcol = $cfg["color"]["table_dark"];
    } else {
        $bgcol = $cfg["color"]["table_light"];
    }

    if ($idmodtranslation == $translation->get("idmodtranslation")) {
        $bgcol = $cfg["color"]["table_active"];
    }
    $v .= '<tr bgcolor="' . $bgcol . '">'."\n"
            . '<td style="padding-left: 2px; padding-top:2px; padding-bottom: 2px;" width="50%">'."\n"
            . '<a name="' . $translation->get("idmodtranslation") . '"></a>'."\n"
            . $mylink->render() . '</td>'."\n"
            . '<td style="padding-left: 2px;">' . $tstring . '</td>'."\n"
            . '</tr>'."\n";
}

$v .= '</table>';

$page->setContent($v);

$clang = new cApiLanguage($lang);
$page->setEncoding($clang->get("encoding"));

$page->render();
?>