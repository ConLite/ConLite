<?php
$sAutoloadClassPath = strstr(dirname(dirname(__FILE__)), "conlite/plugins")."/classes/";
return array(
    'pimPluginCollection' => $sAutoloadClassPath.'class.pim.plugin.php',
    'pimPlugin' => $sAutoloadClassPath.'class.pim.plugin.php',
    'pimPluginDummy' => $sAutoloadClassPath.'class.pim.plugin.dummy.php',
    'pimPluginRelationCollection' => $sAutoloadClassPath.'class.pim.plugin.relation.php',
    'pimPluginRelation' => $sAutoloadClassPath.'class.pim.plugin.relation.php',
    'pimView' => $sAutoloadClassPath.'class.pim.view.php',
    'pimAjax' => $sAutoloadClassPath.'class.pim.ajax.php',
    'pimSetupBase' => $sAutoloadClassPath.'setup/class.pim.setup.base.php',
    'pimSetupPluginInstall' => $sAutoloadClassPath.'setup/class.pim.setup.plugin.install.php',
    'pimSetupPluginUninstall' => $sAutoloadClassPath.'setup/class.pim.setup.plugin.uninstall.php',
    'PluginmanagerAjax' => $sAutoloadClassPath.'class.pluginmanager.ajax.php',
    'pimPluginHandler' => $sAutoloadClassPath.'class.pim.plugin.handler.php',
    'Plugins' => $sAutoloadClassPath.'plugin/interface.plugins.php',
    'pluginHandlerAbstract' => $sAutoloadClassPath.'plugin/class.plugin.handler.abstract.php',
    'pimExeption' => $sAutoloadClassPath.'exeptions/class.pim.exeption.php',
    'pimXmlStructureException' => $sAutoloadClassPath.'exeptions/class.pim.exeption.php',
    'pimSqlParser' => $sAutoloadClassPath.'Util/class.pim.sql.parser.php'
    // the following entries may be deleted after recode of pim
    //'Contenido_Plugin_Base' => $sAutoloadClassPath.'Contenido_Plugin_Base.class.php',
    //'Contenido_PluginConfig_Settings' => $sAutoloadClassPath.'Config/Contenido_PluginConfig_Settings.class.php',
    //'Contenido_PluginSetup' => $sAutoloadClassPath.'Setup/Contenido_PluginSetup.class.php',
    //'Contenido_VersionCompare_Exception' => $sAutoloadClassPath.'Exceptions/Contenido_VersionCompare_Exception.php',
    //'Contenido_PluginSqlBuilder_Deinstall' => $sAutoloadClassPath.'Sql/Contenido_PluginSqlBuilder_Deinstall.php',
    //'Contenido_PluginSqlBuilder_Install' => $sAutoloadClassPath.'Sql/Contenido_PluginSqlBuilder_Install.php',
    //'Contenido_ArchiveExtractor' => $sAutoloadClassPath.'Util/Zip/Contenido_ArchiveExtractor.class.php'
);
?>