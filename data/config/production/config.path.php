<?php
/**
 * Project:
 * Contenido Content Management System
 *
 * Description:
 * Contenido Path Configurations
 *
 * @package    Contenido Backend includes
 * @version    1.9.1
 * @author     Timo A. Hummel
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.6
 *
 *   $Id$:
 */

if (!defined('CON_FRAMEWORK')) {
	die('Illegal call');
}

global $cfg;

/* IMPORTANT! Put your modifications into the file "config.local.php"
   to prevent that your changes are overwritten during a system update. */

$cfg['path']['conlite_html']          = '../conlite/';
$cfg['path']['contenido_html']          = $cfg['path']['conlite_html'];

$cfg['path']['statfile']                = 'statfile/';
$cfg['path']['includes']                = 'includes/';

$cfg['path']['xml']                     = 'xml/';
$cfg['path']['images']                  = 'images/';
$cfg['path']['classes']                 = 'classes/';

$cfg["path"]["cronjobs"]                = 'cronjobs/';
$cfg['path']['scripts']                 = 'scripts/';
$cfg['path']['styles']                  = 'styles/';
$cfg["path"]['plugins']                 = 'plugins/';

$cfg['path']['external']                = 'external/';
$cfg['path']['locale']                  = 'locale/';

$cfg['path']['frontendtemplate']        = 'external/frontend/';
$cfg['path']['templates']               = 'templates/standard/';
$cfg['path']['xml']                     = 'xml/';

$cfg['path']['temp']                    = $cfg['path']['data'].'temp/';
$cfg['path']['cache']                   = $cfg['path']['data'].'cache/';
$cfg['path']['config']                  = $cfg['path']['data'].'config/';
$cfg['path']['logs']                    = $cfg['path']['data'].'logs/';
$cfg['path']['backup']                  = $cfg['path']['data'].'backup/';
$cfg['path']['cronlog']                 = $cfg['path']['data'].'cronlog/';
//$cfg['path']['locale']                  = $cfg['path']['data'].'locale/';

$cfg['path']['conlite_temp']            = $cfg['path']['temp'];
$cfg['path']['conlite_cache']           = $cfg['path']['cache'];
$cfg['path']['conlite_logs']            = $cfg['path']['logs'];
$cfg['path']['conlite_backup']          = $cfg['path']['backup'];
$cfg['path']['conlite_cronlog']         = $cfg['path']['cronlog'];
//$cfg['path']['conlite_locale']          = $cfg['path']['frontend']. '/' .$cfg['path']['locale'];

$cfg['path']['repository']              = $cfg["path"]['plugins'] . 'repository/';
$cfg['path']['modules']                 = 'modules/';

$cfg['path']['interfaces']              = $cfg['path']['classes'] . 'interfaces/';
$cfg['path']['exceptions']              = $cfg['path']['classes'] . 'exceptions/';
?>