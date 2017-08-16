<?php
$sAutoloadClassPath = strstr(dirname(dirname(__FILE__)), "conlite/plugins")."/classes/";
return array(
    'cSmartyBackend' => $sAutoloadClassPath.'class.smarty.backend.php',
    'cSmartyFrontend' => $sAutoloadClassPath.'class.smarty.frontend.php',
    'cSmartyWrapper' => $sAutoloadClassPath.'class.smarty.wrapper.php'
);
?>