<?php
/**
 * ConLite Configuration File
 * 
 * This file was generated by ConLite setup!
 * If you want to modify any configuration value please create a local
 * configuration file "config.local.php" in the configuration folder and
 * modify or define settings within.
 * 
 * @package Core
 * @subpackage Setup
 * @version 1.1.0
 * @author Ortwin Pinke
 * @copyright (c) 2017, ConLite Team
 * @license 
 * @link http://conlite.org
 */

defined('CON_FRAMEWORK') or die('Illegal call');

global $cfg;

/* Section 1: Path settings
 * ------------------------
 *
 * Path settings which will vary along different ConLite settings.
 *
 * A little note about web and server path settings:
 * - A Web Path can be imagined as web addresses. Example:
 *   http://192.168.1.1/test/
 * - A Server Path is the path on the server's hard disk. Example:
 *   /var/www/html/conlite    for Unix systems OR
 *   c:/htdocs/conlite        for Windows systems
 */

/**
 * @var string The root server path to the conlite backend
 */
$cfg['path']['conlite']               = '{CONTENIDO_ROOT}/conlite/';
$cfg['path']['contenido']               = $cfg['path']['conlite'];

/**
 * @var string The web server path to the conlite backend
 */
$cfg['path']['conlite_fullhtml']      = '{CONTENIDO_WEB}/conlite/';
$cfg['path']['contenido_fullhtml']      = $cfg['path']['conlite_fullhtml'];

/**
 * @var string The root server path where all frontends reside
 */
$cfg['path']['frontend']                = '{CONTENIDO_ROOT}';

/**
 * @var string The root server path to the data directory
 */
$cfg['path']['data']                  = '{CONTENIDO_ROOT}/data/';

/**
 * @var string The root server path to the conlib directory
 */
$cfg['path']['phplib']                  = '{CONTENIDO_ROOT}/conlib/';

/**
 * @var string The root server path to the pear directory
 */
$cfg['path']['pear']                    = '{CONTENIDO_ROOT}/pear/';

/**
 * @var string The server path to the desired WYSIWYG-Editor
 */
$cfg['path']['wysiwyg']                 = '{CONTENIDO_ROOT}/conlite/external/wysiwyg/tinymce3/';

/**
 * @var string The web path to the desired WYSIWYG-Editor
 */
$cfg['path']['wysiwyg_html']            = '{CONTENIDO_WEB}/conlite/external/wysiwyg/tinymce3/';

/**
 * @var string The server path to all WYSIWYG-Editors
 */
$cfg['path']['all_wysiwyg']             = '{CONTENIDO_ROOT}/conlite/external/wysiwyg/';

/**
 * @var string The web path to all WYSIWYG-Editors
 */
$cfg['path']['all_wysiwyg_html']        = '{CONTENIDO_WEB}/conlite/external/wysiwyg/';



/* Section 2: Database settings
 * ----------------------------
 */

/**
 * @var string The prefix for all contenido system tables, usually "cl"
 */
$cfg['sql']['sqlprefix'] = '{MYSQL_PREFIX}';

/**
 * @var stringDatabase extension/driver to use, feasible values are 'mysqli' or 'mysql'
 */
$cfg["database_extension"] = '{DB_EXTENSION}';

/**
 * The host where your database runs on
 * @deprecated since version 2.0.0
 * @see $cfg['db']
 */
$contenido_host = '{MYSQL_HOST}';

/**
 * The database name which you use
 * @deprecated since version 2.0.0
 * @see $cfg['db']
 */
$contenido_database = '{MYSQL_DB}';

/**
 * The username to access the database
 * @deprecated since version 2.0.0
 * @see $cfg['db']
 */
$contenido_user = '{MYSQL_USER}';

/**
 * The password to access the database
 * @deprecated since version 2.0.0
 * @see $cfg['db']
 */
$contenido_password = '{MYSQL_PASS}';

/**
 * @deprecated since version 2.0.0
 */
$cfg["nolock"] = '{NOLOCK}';

/**
 * @deprecated since version 2.0.0
 */
$cfg["is_start_compatible"] = {START_COMPATIBLE};


/**
 * Extended database settings. This settings will be used since Contenido 4.8.15.
 *
 * NOTE: Configuration from above ($contenido_host, $contenido_database, etc.)
 *       is still available because of downwards compatibility.
 *
 * @since  Contenido version 4.8.15
 */
$cfg['db'] = array(
    'connection' => array(
        'host'     => '{MYSQL_HOST}', // (string) The host where your database runs on
        'database' => '{MYSQL_DB}',   // (string) The database name which you use
        'user'     => '{MYSQL_USER}', // (string) The username to access the database
        'password' => '{MYSQL_PASS}', // (string) The password to access the database
        'charset'  => '{MYSQL_CHARSET}', // (string) The charset of connection to database
    ),
    'nolock'          => {NOLOCK}, // (bool) Flag to not lock tables
    'sequenceTable'   => '',       // (string) will be set later in startup!
    'haltBehavior'    => 'report', // (string) Feasible values are 'yes', 'no' or 'report'
    'haltMsgPrefix'   => (isset($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] . ' ' : '',
    'enableProfiling' => false,    // (bool) Flag to enable profiling
);
    
/* Section 3: UTF-8 flag
 * ----------------------------
 * @since ConLite version 2.0.0
 */
{CON_UTF8}
?>