<?php
/**
 * Plugin Advanced Mod Rewrite default settings. This file will be included if
 * mod rewrite settings of an client couldn't loaded.
 *
 * Containing settings are taken over from CONTENIDO-4.6.15mr setup installer
 * template beeing made originally by stese.
 *
 * NOTE:
 * Changes in these Advanced Mod Rewrite settings will affect all clients, as long
 * as they don't have their own configuration.
 * PHP needs write permissions to the folder, where this file resides. Mod Rewrite
 * configuration files will be created in this folder.
 *
 * @package     plugin
 * @subpackage  Mod Rewrite
 * @version     SVN Revision $Rev: 128 $
 * @id          $Id: config.mod_rewrite_default.php 128 2019-07-03 11:58:28Z oldperl $:
 * @author      Murat Purc <murat@purc.de>
 * @copyright   four for business AG <www.4fb.de>
 * @license     http://www.contenido.org/license/LIZENZ.txt
 * @link        http://www.4fb.de
 * @link        http://www.contenido.org
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}


global $cfg;

// Use advanced mod_rewrites  ( 1 = yes, 0 = none )
$cfg['cl-mod-rewrite']['use'] = 0;

// Path to the htaccess file with trailling slash from domain-root!
$cfg['cl-mod-rewrite']['rootdir'] = '/';

// Check path to the htaccess file ( 1 = yes, 0 = none )
$cfg['cl-mod-rewrite']['checkrootdir'] = 1;

// Start TreeLocation from Root Tree (set to 1) or get location from first category (set to 0)
$cfg['cl-mod-rewrite']['startfromroot'] = 0;

// Prevent Duplicated Content, if startfromroot is enabled ( 1 = yes, 0 = none )
$cfg['cl-mod-rewrite']['prevent_duplicated_content'] = 0;

// is multilanguage? ( 1 = yes, 0 = none )
$cfg['cl-mod-rewrite']['use_language'] = 0;

// use language name in url? ( 1 = yes, 0 = none )
$cfg['cl-mod-rewrite']['use_language_name'] = 0;

// is multiclient in only one directory? ( 1 = yes, 0 = none )
$cfg['cl-mod-rewrite']['use_client'] = 0;

// use client name in url? ( 1 = yes, 0 = none )
$cfg['cl-mod-rewrite']['use_client_name'] = 0;

// use lowercase url? ( 1 = yes, 0 = none )
$cfg['cl-mod-rewrite']['use_lowercase_uri'] = 1;

// file extension for article links
$cfg['cl-mod-rewrite']['file_extension'] = '.html';

// The percentage if the category name have to match with database names.
$cfg['cl-mod-rewrite']['category_resolve_min_percentage'] = '75';

// Add start article name to url (1 = yes, 0 = none)
$cfg['cl-mod-rewrite']['add_startart_name_to_url'] = 1;

// Default start article name to use, depends on active add_startart_name_to_url
$cfg['cl-mod-rewrite']['default_startart_name'] = 'index';

// Rewrite urls on generating the code for the page. If active, the responsibility will be
// outsourced to moduleoutputs and you have to adapt the moduleoutputs manually. Each output of
// internal article/category links must be processed by using $sess->url. (1 = yes, 0 = none)
$cfg['cl-mod-rewrite']['rewrite_urls_at_congeneratecode'] = 0;

// Rewrite urls on output of htmlcode at front_content.php. Is the old way, and doesn't require
// adapting of moduleoutputs. On the other hand usage of this way will be slower than rewriting
// option above. (1 = yes, 0 = none)
$cfg['cl-mod-rewrite']['rewrite_urls_at_front_content_output'] = 1;


// Following five settings write urls like this one:
//     www.domain.de/category1-category2.articlename.html
// Changes of these settings causes a reset of all aliases, see Advanced Mod Rewrite settings in
// backend.
// NOTE: category_seperator and article_seperator must contain different character.
// Separator for categories
$cfg['cl-mod-rewrite']['category_seperator'] = '/';

// Separator between category and article
$cfg['cl-mod-rewrite']['article_seperator'] = '/';

// Word seperator in category names
$cfg['cl-mod-rewrite']['category_word_seperator'] = '-';

// Word seperator in article names
$cfg['cl-mod-rewrite']['article_word_seperator'] = '-';


// Routing settings for incomming urls. Here you can define routing rules as follows:
// $cfg['cl-mod-rewrite']['routing'] = array(
//    '/a_incomming/url/foobar.html' => '/new_url/foobar.html',  # route /a_incomming/url/foobar.html to /new_url/foobar.html
//    '/cms/' => '/' # route /cms/ to / (doc root of client)
// );
$cfg['cl-mod-rewrite']['routing'] = array();


// Redirect invalid articles to errorpage (1 = yes, 0 = none)
$cfg['cl-mod-rewrite']['redirect_invalid_article_to_errorsite'] = 0;

