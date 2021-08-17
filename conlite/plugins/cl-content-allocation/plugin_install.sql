/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Author:  oldperl
 * Created: 06.05.2021
 */

CREATE TABLE `!PREFIX!ca_alloc` (
  `idpica_alloc` int(10) NOT NULL DEFAULT 0,
  `parentid` int(10) DEFAULT NULL,
  `sortorder` int(10) NOT NULL DEFAULT 0,
PRIMARY KEY (`idpica_alloc`)
);

CREATE TABLE `!PREFIX!ca_alloc_con` (
  `idpica_alloc` int(10) NOT NULL DEFAULT 0,
  `idartlang` int(10) NOT NULL DEFAULT 0,
PRIMARY KEY (`idpica_alloc`)
);

CREATE TABLE `!PREFIX!ca_lang` (
  `idpica_alloc` int(10) NOT NULL DEFAULT 0,
  `idlang` int(10) NOT NULL DEFAULT 0,
  `name` varchar(255) DEFAULT NULL,
  `online` tinyint(1) NOT NULL DEFAULT 0,
PRIMARY KEY (`idpica_alloc`)
);