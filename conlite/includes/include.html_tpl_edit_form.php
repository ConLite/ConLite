<?php
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

$editor = new cGuiSourceEditor($_REQUEST['tmp_file']);

// Show notice message if backend_file_extension filter is active
if (empty($_REQUEST['tmp_file'])) {

    // Get system properties for extension filter
    $backend_file_extensions = getSystemProperty('backend', 'backend_file_extensions');

    if($backend_file_extensions == "enabled") {
        $editor->displayInfo(sprintf(i18n("Currently only files with the extension %s are displayed in the menu. If you create files with a different extension, they will not be shown on the left side!"), "html/tpl"));
    }

}

// Render source editor
$editor->render();