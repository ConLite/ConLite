<?php
/**
 * defines.php
 * 
 * define needed var for conlite setup
 * 
 * 
 * @package ConLite
 * @subpackage Setup
 * @license https://www.gnu.de/documents/gpl-3.0.de.html GNU General Public License (GPL)
 * @link https://conlite.org ConLite Portal
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

if (array_key_exists('setuptype', $_SESSION)) {
    switch ($_SESSION['setuptype']) {
        case 'setup':
            define('C_SETUP_STEPS', 8);
            break;
        case 'upgrade':
            define('C_SETUP_STEPS', 7);
            break;
        case 'migration':
            define('C_SETUP_STEPS', 8);
            break;
    }
}

define('C_SETUP_STEPFILE', 'images/steps/s%d.png');
define('C_SETUP_STEPFILE_ACTIVE', 'images/steps/s%da.png');
define('C_SETUP_STEPWIDTH', 28);
define('C_SETUP_STEPHEIGHT', 28);
define('C_SETUP_MIN_PHP_VERSION', '7.0.0');
define('C_SETUP_MAX_PHP_VERSION', '8.0.0');
define('C_SETUP_VERSION', '2.1.2');