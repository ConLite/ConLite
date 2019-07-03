<?php
/**
 * ConLite
 * 
 * @package Core
 * @subpackage BackendIncludes
 * @author Ortwin Pinke <ortwin.pinke@conlite.org>
 * @version $Rev$
 * @since 2.0
 * @link www.conlite.org ConLite Portal
 * 
 * $Id$
 */
/**
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.6
 * 
 */

if(!defined('CON_FRAMEWORK')) {
	die('Illegal call');
}

$oLayout = new cApiLayout(Contenido_Security::toInteger($_GET['idlay']));
if($oLayout->virgin) {
    echo i18n("No such layout");
} else {
    $code = $oLayout->getLayout();
    /* Insert base href */
    $base = '<base href="'.$cfgClient[$client]["path"]["htmlpath"].'">';

    $code = str_replace("<head>", "<head>\n".$base, $code);

    eval("?>\n".Contenido_Security::unescapeDB($code)."\n<?php\n");
}
?>