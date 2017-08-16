<?php

defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

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
    new cSmartyFrontend($cfg, $cfgClient[$client], true);
} catch (Exception $e) {
    cWarning($e->getFile(), $e->getLine(), $e->getMessage());
}
?>