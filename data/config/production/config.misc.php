<?php

/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * Contenido Misc Configurations
 *
 * @package    Contenido Backend includes
 * @version    $Rev$
 * @author     Holger Librenz
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

/* Misc settings
 * ----------------------------------
 *
 * Actually no variables, but important settings
 * for error handling and logging.
 */

/* Current Version. You shouldn't change this
  value unless you know what you are doing. */
$cfg['version'] = CL_VERSION;

/* Login languages. Temporarly hardcoded due to recode of language selection */
$cfg["login_languages"] = array("de_DE", "en_US");

/* SVN Date tag */
$cfg['datetag'] = '$Date$';

/* Backend timeout */
$cfg["backend"]["timeout"] = 60;

$cfg["develop"]["show_errors"] = false;
$cfg["develop"]["show_deprecated"] = false;

/* Use Pseudo-Cron? */
$cfg["use_pseudocron"] = TRUE;

/* Config Pseudo-Cron */
$cfg["pseudocron"]["logging"] = TRUE;

/* If you want to measure function timing set this to true */
$cfg["debug"]["functiontiming"] = false;

/* If you want to measure backend page rendering times, set this
  to true */

$cfg["debug"]["rendering"] = false;

/* To output the code when editing and browsing the frontend, set
  this to true */
$cfg["debug"]["codeoutput"] = false;

/**
 * due to the still missing pluginmanager turned this to true to prevent errors in includePluginConf
 * @todo change to false if pim is integrated
 */
$cfg['debug']['disable_plugins'] = false;

/**
 * set to true to send no-cache headers in backend
 */
$cfg['debug']['sendnocacheheader'] = false;

/* If true, use the field "urlname" for resolving. "name" otherwise */
$cfg["urlpathresolve"] = false;

/* E-Mail-Address where bug reports will be sent to */
$cfg['bugreport']['targetemail'] = 'bugs@conlite.org';

/* The available charsets */
$cfg['AvailableCharsets'] = array(
    'iso-8859-1',
    'iso-8859-2',
    'iso-8859-3',
    'iso-8859-4',
    'iso-8859-5',
    'iso-8859-6',
    'iso-8859-7',
    'iso-8859-8',
    'iso-8859-8-i',
    'iso-8859-9',
    'iso-8859-10',
    'iso-8859-11',
    'iso-8859-12',
    'iso-8859-13',
    'iso-8859-14',
    'iso-8859-15',
    'iso-8859-16',
    'windows-1250',
    'windows-1251',
    'windows-1252',
    'windows-1253',
    'windows-1254',
    'windows-1255',
    'windows-1256',
    'windows-1257',
    'windows-1258',
    'koi8-r',
    'big5',
    'gb2312',
    'utf-8',
    'utf-7',
    'x-user-defined',
    'euc-jp',
    'ks_c_5601-1987',
    'tis-620',
    'SHIFT_JIS'
);

// (bool) Flag to use native i18n.
//        Note: Enabling this could create unwanted side effects, because of
//        native gettext() behavior.
$cfg['native_i18n'] = false;


/* Error handling settings
 * ----------------------------------
 *
 * Actually no variables, but important settings
 * for error handling and logging.
 */

/* Don't display errors */
ini_set("display_errors", false);

/* Log errors to a file */
ini_set("log_errors", true);

/* The file in which we write the error log */
ini_set("error_log", $cfg['path']['conlite_logs'] . "errorlog.txt");

/**
 *  Report all errors except warnings
 **/
if ($cfg["develop"]["show_errors"]) {
    error_reporting(E_ALL);
} else {
    if ($cfg["develop"]["show_deprecated"]) {
        error_reporting(E_ALL ^ E_NOTICE);
    } else {
         if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
            error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_USER_DEPRECATED ^ E_WARNING);
        } else {
            error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_USER_DEPRECATED);
        }
    }
}


/* Session data storage container (PHPLIB)
 * ----------------------------------
 *
 * Different session data storage containers are available.
 * file	= session data will be stored in a file on the file system
 * sql	= session data will be stored in a database table - as it is
 */

/* default container is sql */
$cfg["session_container"] = 'sql';

/* Use heap table to accelerate statitics (off by default) */
$cfg["statistics_heap_table"] = false;

/* HTTP parameter check
 *
 * This feature checks GET and POST parameters against a whitelist defined in
 * $cfg['http_params_check']['config']. Depending on mode administrated in the
 * same config as the whitelist contenido will stop processing in case of unknown
 * or invalid GET parameter.
 *
 * For further informations and initial discussion see  http://contenido.org/forum/viewtopic.php?p=113492!
 *
 * Special thx to kummer!
 */
// turns parameter checking on or off
$cfg['http_params_check']['enabled'] = false;

// configuration file (whitelist and mode)
$cfg['http_params_check']['config'] = $cfg["path"]["contenido"] . $cfg["path"]["includes"] . '/config.http_check.php';

/* max file size for one session file */
$cfg['session_line_length'] = 99999;


/**
 * Cache settings
 * ----------------------------------
 *
 * Following cache settings don't affect the caching behaviour at frontend.
 * 
 * Only enabling the caching ($cfg["cache"]["disable"] = false) will activate processing of
 * caching at frontend.
 * Everything else has to be configured in a client caching specific file which is available 
 * in clients frontend path, see cms/includes/concache.php.
 *
 * So, if you want do enable frontend caching, set $cfg["cache"]["disable"] to false and configure 
 * the rest in cms/includes/concache.php!
 *
 * @TODO: Need a caching solution with better integration in Contenido core
 */
// (bool)  Enable/Disable caching
$cfg['cache']['disable'] = true;

// (string)  Directory, where to store cache files.
//           NOTE: This setting doesn't affects frontend caching
$cfg['cache']['dir'] = 'cache/';

// (int)  Lifetime of cached files in seconds.
//        NOTE: This setting doesn't affects frontend caching
$cfg['cache']['lifetime'] = 3600;


/**
 * GenericDB settings
 * ----------------------------------
 */
// (string) The GenericDB driver to use, at the moment only "mysql" is supported
$cfg['sql']['gdb_driver'] = 'mysql';

// (int) Number of GenericDB items per table to cache
$cfg['sql']['cache']['max_items_to_cache'] = 10;

// (bool) Enable GenericDB item cache
$cfg['sql']['cache']['enable'] = true;

// (bool) Enable mode to select all fields in GenericDB item collections.
$cfg['sql']['select_all_mode'] = true;



/* Help system, currently not used */
$cfg['help'] = false;

$cfg["help_url"] = "http://help.conlite.org/";

/* Configure page if Contenido is unable to run (e.g. no database connection)
 * It is wise to create a maintenance HTML page for redirection, so you won't
 * confuse your customers.
 *
 * Note: The URL should be absolute with http:// in front of it.
 */
$cfg["contenido"]["errorpage"] = "";

/* Configure an email address to alert when Contenido is unable to run. */
$cfg["contenido"]["notifyonerror"] = "";

/* Configure how often the notification email is sent, in minutes */
$cfg["contenido"]["notifyinterval"] = 20;


/**
 * UrlBuilder settings
 * ----------------------------------
 * 
 * Configuration of UrlBuilder to use.
 *
 * Example setting for UrlBuilder 'front_content' (generates URLs like '/cms/front_content.php?idcat=2&lang=1'):
 * $cfg['url_builder']['name']   = 'front_content';
 * $cfg['url_builder']['config'] = array();
 *
 * Example setting for UrlBuilder 'custom_path' (generates URLs like '/cms/Was-ist-Contenido/rocknroll,a,2.4fb'):
 * $cfg['url_builder']['name']   = 'custom_path';
 * $cfg['url_builder']['config'] = array('prefix' => 'rocknroll', 'suffix' => '.4fb', 'separator' => ',');
 *
 * See also http://forum.contenido.org/viewtopic.php?f=64&t=23280
 */
// (string)  Name of UrlBuilder to use.
//           Feasible values are 'front_content', 'custom', 'custom_path' or a user defined name. 
//           Check out Contenido_UrlBuilderFactory::getUrlBuilder() in
//           contenido/classes/UrlBuilder/Contenido_UrlBuilderFactory.class.php for more details 
//           about this setting.
$cfg['url_builder']['name'] = 'front_content';

// (array)  Default UrlBuilder configuration.
//          An associative configuration array which will be passed to the UrlBuilder instance.
//          Values depend on used UrlBuilder.
$cfg['url_builder']['config'] = array();


/**
 * Password Settings
 * For more comments please look in class.conuser.php file
 */
// Enable or disable checking password (true or false)
$cfg['password']['check_password_mask'] = false;

// Minimum length of password (num characters). Default is 8.
$cfg['password']['min_length'] = 6;

// If set to a value greater than 0 so many lower and upper case character must appear in the password.
// (e.g.: if set to 2, 2 upper and 2 lower case characters must appear)
$cfg['password']['mixed_case_mandatory'] = 3;

// If 'symbols_mandatory' set to a value greater than 0, at least so many symbols has to appear in given password.
$cfg['password']['symbols_mandatory'] = 3;

// If set to a value greater than 0, at least $cfg['password']['numbers_mandatory'] numbers must be in password
$cfg['password']['numbers_mandatory'] = 3;

/**
 * Content Type Settings
 */
// Define here all content types which includes special module translations (dont forget the prefix "CMS_"!)
$cfg['translatable_content_types'] = array('CMS_TEASER', 'CMS_FILELIST');