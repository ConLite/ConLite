<?php
/**
 * This file is the config file for this plugin
 *
 * @package Plugin
 * @subpackage SmartyWrapper
 * @version $Rev$
 * @since 2.0.2
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright (c) 2018, conlite.org
 * @license http://www.gnu.de/documents/gpl.en.html GPL v3 (english version)
 * @license http://www.gnu.de/documents/gpl.de.html GPL v3 (deutsche Version)
 * @link http://www.conlite.org ConLite.org
 * 
 * $Id$
 */

defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

rereadClients();
$client = (isset($client)) ? $client : $load_client;
// Load smarty
if (!defined('SMARTY_DIR')) {
    define('SMARTY_DIR', cRegistry::getConfigValue('path', 'conlite') 
            .  cRegistry::getConfigValue('path', 'plugins')
            . 'smarty/libs/');
}

require_once(SMARTY_DIR . 'Autoloader.php');
Smarty_Autoloader::register();

try {
    new cSmartyFrontend(cRegistry::getConfig(), cRegistry::getClientConfig(cRegistry::getClientId()), true);
} catch (Exception $e) {
    cWarning($e->getFile(), $e->getLine(), $e->getMessage());
}
?>