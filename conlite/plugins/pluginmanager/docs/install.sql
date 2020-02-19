INSERT INTO `cl_area` (`idarea`, `parent_id`, `name`, `relevant`, `online`, `menuless`) VALUES ('10000', '0', 'pluginmanager', '1', '1', '1');
INSERT INTO `cl_files` (`idfile`, `idarea`, `filename`, `filetype`) VALUES ('10000', '10000', 'pluginmanager/includes/include.right_bottom.php', 'main');
INSERT INTO `cl_frame_files` (`idframefile`, `idarea`, `idframe`, `idfile`) VALUES ('10000', '10000', '4', '10000');
INSERT INTO `cl_actions` (`idaction`, `idarea`, `alt_name`, `name`, `code`, `location`, `relevant`) VALUES ('10000', '10000', '', 'pluginmanager', '', '', '1');
INSERT INTO `cl_nav_sub` (`idnavs`, `idnavm`, `idarea`, `level`, `location`, `online`) VALUES ('10000', '5', '10000', '0', 'pluginmanager/xml/plugin.xml;navigation/administration/pluginmanager/main', '1');


--
-- Table structure for table `cl_plugins`
--

CREATE TABLE IF NOT EXISTS `cl_plugins` (
  `idplugin` int(11) NOT NULL AUTO_INCREMENT,
  `idclient` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `author` varchar(255) NOT NULL,
  `copyright` varchar(255) NOT NULL,
  `mail` varchar(255) NOT NULL,
  `website` varchar(255) NOT NULL,
  `version` varchar(10) NOT NULL,
  `folder` varchar(255) NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `executionorder` int(11) NOT NULL DEFAULT '0',
  `installed` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `active` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`idplugin`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cl_plugins_rel`
--

CREATE TABLE IF NOT EXISTS `cl_plugins_rel` (
  `idpluginrelation` int(11) NOT NULL AUTO_INCREMENT,
  `iditem` int(11) NOT NULL,
  `idplugin` int(11) NOT NULL,
  `type` varchar(8) NOT NULL DEFAULT 'area',
  PRIMARY KEY (`idpluginrelation`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;