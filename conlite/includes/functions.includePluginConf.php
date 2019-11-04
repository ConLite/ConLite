<?php

/**
 *
 * @package	Includes
 * @subpackage  Plugins
 * @version	$Rev$
 * @since 2.0
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright (c) 2015, conlite.org
 * @license http://www.gnu.de/documents/gpl.en.html GPL v3 (english version)
 * @license http://www.gnu.de/documents/gpl.de.html GPL v3 (deutsche Version)
 * @link http://www.conlite.org ConLite.org
 *
 *   $Id$:
 */
defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

$bDebug = FALSE;

// first check all plugins managed by pim and init them if active
$aPlugins = array();
$sPluginsPath = cRegistry::getPluginsPath();

if (file_exists($sPluginsPath."pluginmanager") && cRegistry::getConfigValue('debug', 'disable_plugins') === FALSE) {
    i18nRegisterDomain('pluginmanager', $sPluginsPath . 'pluginmanager/locale/');
    cAutoload::addClassmapConfigFile($sPluginsPath . 'pluginmanager/includes/config.autoloader.php');
    include_once($sPluginsPath . 'pluginmanager/includes/config.plugin.php');
    $oPluginColl = new pimPluginCollection();
    $oPluginColl->setWhere('active', 1);
    $oPluginColl->setOrder('executionorder ASC');
    $oPluginColl->query();

    while (($oPlugin = $oPluginColl->next()) !== false) {
        $sPluginName = $oPlugin->get('folder');
        $sPluginFolder = $sPluginsPath . $sPluginName . DIRECTORY_SEPARATOR;
        if (is_dir($sPluginFolder) && file_exists($sPluginFolder . "cl_plugin.xml")) {
            $aPlugins[] = $sPluginName;
        }
    }
}

if ($bDebug && $frame == 4) {
    echo $sPluginName . "\n";
    print_r($aPlugins);
}

// Include all active plugins
foreach ($aPlugins as $sPluginName) {
    $sPluginIgnoreFile = $sPluginsPath . $sPluginName . DIRECTORY_SEPARATOR . 'plugin.ignore';
    if (file_exists($sPluginIgnoreFile)) {
        continue;
    }
    
    $sPluginLocaleDir = $sPluginsPath . $sPluginName . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR;
    $sPluginConfigFile = $sPluginsPath . $sPluginName . DIRECTORY_SEPARATOR . 'includes/config.plugin.php';
    $sPluginAutoloaderFile = $sPluginsPath . $sPluginName . DIRECTORY_SEPARATOR . 'includes/config.autoloader.php';
    // language support
    if (file_exists($sPluginLocaleDir)) {
        i18nRegisterDomain($sPluginName, $sPluginLocaleDir);
    }// autoloader config
    if (file_exists($sPluginAutoloaderFile)) {
        cAutoload::addClassmapConfigFile($sPluginAutoloaderFile);
    }
    // config file
    if (file_exists($sPluginConfigFile)) {
        include_once($sPluginConfigFile);
    }
}

$pluginorder = getSystemProperty("system", "plugin-order");

$plugins = explode(",", $pluginorder);

$ipc_conpluginpath = cRegistry::getPluginsPath();

/**
 * Scan and save only by the BE 
 * will be removed if all core plugins using pluginmanager
 * 
 * @deprecated since version 2.0
 */
if ($contenido) {
    $lastscantime = getSystemProperty("system", "plugin-lastscantime");

    /* Clean up: Fetch and trim the plugin order */
    $plugins = array();

    if ($pluginorder != "") {
        $plugins = explode(",", $pluginorder);

        foreach ($plugins as $key => $plugin) {
            $plugins[$key] = trim($plugin);
        }
    }

    /* Don't scan all the time, but each 60 seconds */
    if ($lastscantime + 60 < time()) {

        // Directories which are to exclude from scanning process
        $dirsToExclude = trim(getSystemProperty('system', 'plugin-dirstoexclude'));
        if ($dirsToExclude === '') {
            $dirsToExclude = '.,..,.svn,.cvs,includes';
            setSystemProperty('system', 'plugin-dirstoexclude', $dirsToExclude);
        }
        $dirsToExclude = explode(',', $dirsToExclude);
        foreach ($dirsToExclude as $pos => $item) {
            $dirsToExclude[$pos] = trim($item);
        }

        /* scan for new Plugins */
        $dh = opendir($ipc_conpluginpath);
        while (($file = readdir($dh)) !== false) {
            if (is_dir($ipc_conpluginpath . $file) &&
                    !in_array(strtolower($file), $dirsToExclude) &&
                    !in_array($file, $plugins)) {
                $plugins[] = $file;
            }
        }
        closedir($dh);
        setSystemProperty("system", "plugin-lastscantime", time());


        /* Remove plugins do not exist */
        foreach ($plugins as $key => $ipc_plugin) {
            if (!is_dir($ipc_conpluginpath . $ipc_plugin . "/") 
                    || in_array($ipc_plugin, $dirsToExclude)
                    || file_exists($ipc_conpluginpath . $ipc_plugin . "/cl_plugin.xml")) {
                unset($plugins[$key]);
            }
        }

        /* Save Scanresult */
        $pluginorder = implode(",", $plugins);
        setSystemProperty("system", "plugin-order", $pluginorder);
    }
}



/**
 * load plugin configuration and localization
 * add classes to contenido autoloader 
 * 
 * @deprecated since version 2.0 will be removed if all core plugins use pluginmanager
 */
foreach ($plugins as $key => $ipc_plugin) {
    if (!is_dir($ipc_conpluginpath . $ipc_plugin . "/")
            || file_exists($ipc_conpluginpath . $ipc_plugin . "/cl_plugin.xml")) {
        unset($plugins[$key]);
    } else {
        $ipc_localedir = $ipc_conpluginpath . $ipc_plugin . "/locale/";
        $ipc_langfile = $ipc_conpluginpath . $ipc_plugin . "/includes/language.plugin.php";
        $ipc_configfile = $ipc_conpluginpath . $ipc_plugin . "/includes/config.plugin.php";
        $ipc_autoloaderfile = $ipc_conpluginpath . $ipc_plugin . "/includes/config.autoloader.php";

        if (file_exists($ipc_localedir)) {
            i18nRegisterDomain($ipc_plugin, $ipc_localedir);
        }

        if (file_exists($ipc_langfile)) {
            include_once($ipc_langfile);
        }

        if (file_exists($ipc_configfile)) {
            include_once($ipc_configfile);
        }

        if (file_exists($ipc_autoloaderfile)) {
            cAutoload::addClassmapConfigFile($ipc_autoloaderfile);
        }
    }
}
unset($plugins);
unset($bDebug);
?>