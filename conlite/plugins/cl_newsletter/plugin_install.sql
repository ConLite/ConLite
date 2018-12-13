/**
 * Author:  oldperl
 * Created: 13.12.2018
 * $Id$
 */

CREATE TABLE IF NOT EXISTS `!PREFIX!_news` (
  `idnews` int(10) NOT NULL DEFAULT 0,
  `idclient` int(10) NOT NULL DEFAULT 0,
  `idlang` int(10) NOT NULL DEFAULT 0,
  `idart` int(10) NOT NULL DEFAULT 0,
  `template_idart` int(10) NOT NULL DEFAULT 0,
  `type` varchar(10) NOT NULL DEFAULT 'text',
  `name` varchar(255) NOT NULL,
  `subject` text DEFAULT NULL,
  `message` longtext DEFAULT NULL,
  `newsfrom` varchar(255) NOT NULL,
  `newsfromname` varchar(255) DEFAULT NULL,
  `newsdate` datetime DEFAULT NULL,
  `welcome` tinyint(1) NOT NULL DEFAULT 0,
  `use_cronjob` tinyint(1) NOT NULL DEFAULT 0,
  `send_to` varchar(32) NOT NULL DEFAULT 'all',
  `send_ids` text DEFAULT NULL,
  `dispatch` tinyint(1) NOT NULL DEFAULT 0,
  `dispatch_count` int(5) NOT NULL DEFAULT 50,
  `dispatch_delay` int(5) NOT NULL DEFAULT 5,
  `author` varchar(32) NOT NULL,
  `created` datetime NOT NULL DEFAULT '1970-01-01 00:00:01',
  `modified` datetime NOT NULL DEFAULT '1970-01-01 00:00:01',
  `modifiedby` varchar(32) NOT NULL,
  PRIMARY KEY (`idnews`)
);

CREATE TABLE IF NOT EXISTS `!PREFIX!_news_groupmembers` (
  `idnewsgroupmember` int(10) NOT NULL DEFAULT 0,
  `idnewsgroup` int(10) NOT NULL DEFAULT 0,
  `idnewsrcp` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`idnewsgroupmember`)
);

CREATE TABLE IF NOT EXISTS `!PREFIX!_news_groups` (
  `idnewsgroup` int(10) NOT NULL DEFAULT 0,
  `idclient` int(10) NOT NULL DEFAULT 0,
  `idlang` int(10) NOT NULL DEFAULT 0,
  `groupname` varchar(32) NOT NULL,
  `defaultgroup` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`idnewsgroup`)
);

CREATE TABLE IF NOT EXISTS `!PREFIX!_news_jobs` (
  `idnewsjob` int(10) NOT NULL DEFAULT 0,
  `idclient` int(10) NOT NULL DEFAULT 0,
  `idlang` int(10) NOT NULL DEFAULT 0,
  `idnews` int(10) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `use_cronjob` tinyint(1) NOT NULL DEFAULT 0,
  `started` datetime NOT NULL DEFAULT '1970-01-01 00:00:01',
  `finished` datetime NOT NULL DEFAULT '1970-01-01 00:00:01',
  `name` varchar(255) NOT NULL,
  `type` varchar(10) NOT NULL DEFAULT 'text',
  `encoding` varchar(32) NOT NULL DEFAULT 'iso-8859-1',
  `newsfrom` varchar(255) NOT NULL,
  `newsfromname` varchar(255) NOT NULL,
  `newsdate` datetime DEFAULT '1970-01-01 00:00:01',
  `subject` text DEFAULT NULL,
  `idart` int(10) NOT NULL DEFAULT 0,
  `message_text` longtext NOT NULL,
  `message_html` longtext DEFAULT NULL,
  `send_to` text NOT NULL,
  `dispatch` tinyint(1) NOT NULL DEFAULT 0,
  `dispatch_count` int(5) NOT NULL DEFAULT 50,
  `dispatch_delay` int(5) NOT NULL DEFAULT 5,
  `author` varchar(32) NOT NULL,
  `authorname` varchar(32) NOT NULL,
  `rcpcount` int(10) NOT NULL DEFAULT 0,
  `sendcount` int(10) NOT NULL DEFAULT 0,
  `created` datetime NOT NULL DEFAULT '1970-01-01 00:00:01',
  `modified` datetime NOT NULL DEFAULT '1970-01-01 00:00:01',
  `modifiedby` varchar(32) NOT NULL,
  PRIMARY KEY (`idnewsjob`)
);

CREATE TABLE IF NOT EXISTS `!PREFIX!_news_log` (
  `idnewslog` int(10) NOT NULL DEFAULT 0,
  `idnewsjob` int(10) NOT NULL DEFAULT 0,
  `idnewsrcp` int(10) NOT NULL DEFAULT 0,
  `rcpname` varchar(255) NOT NULL,
  `rcpemail` varchar(255) NOT NULL,
  `rcphash` varchar(32) NOT NULL,
  `rcpnewstype` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL,
  `sent` datetime NOT NULL DEFAULT '1970-01-01 00:00:01',
  `created` datetime NOT NULL DEFAULT '1970-01-01 00:00:01',
  PRIMARY KEY (`idnewslog`)
);

CREATE TABLE IF NOT EXISTS `!PREFIX!_news_rcp` (
  `idnewsrcp` int(10) NOT NULL DEFAULT 0,
  `idclient` int(10) NOT NULL DEFAULT 0,
  `idlang` int(10) NOT NULL DEFAULT 0,
  `email` varchar(255) DEFAULT NULL,
  `confirmed` tinyint(1) NOT NULL DEFAULT 0,
  `confirmeddate` datetime NOT NULL DEFAULT '1970-01-01 00:00:01',
  `lastaction` varchar(32) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `hash` varchar(32) NOT NULL,
  `deactivated` tinyint(1) NOT NULL DEFAULT 0,
  `news_type` tinyint(1) NOT NULL DEFAULT 0,
  `author` varchar(32) NOT NULL,
  `created` datetime NOT NULL DEFAULT '1970-01-01 00:00:01',
  `lastmodified` datetime NOT NULL DEFAULT '1970-01-01 00:00:01',
  `modifiedby` varchar(32) NOT NULL,
  PRIMARY KEY (`idnewsrcp`)
);