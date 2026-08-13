<?php

/**
 * Project:
 * Contenido Content Management System
 *
 * Description:
 * This object makes contenido more secure
 *
 * @package    Contenido Backend classes
 * @version    $Id$:
 * @author     Frederic Schneider
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release 4.8.7
 *
 * @TODO: Some features are the same as in HttpInputValidator (see contenido/classes/class.httpinputvalidator.php),
 *        merge them...
 *
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

/**
 * Contenido Security exception class
 */
class Contenido_Security_Exception extends Exception {

    /**
     * Logging flag. Set to true for logging invalid calls.
     * @access   protected
     * @static
     * @var      boolean
     */
    protected static $_logging = false;

    /**
     * @see Exception::__construct()
     */
    public function __construct($sMessage, $sParamName) {
        parent::__construct($sMessage);

        // check if logging is enabled
        if (self::$_logging == true) {
            $sLogFile = realpath(dirname(__FILE__) . '/../logs/') . '/security.txt';

            $sFileContent = '---------' . PHP_EOL;
            $sFileContent .= "Invalid call caused by parameter '" . $sParamName . "' at " . date("c") . PHP_EOL;
            $sFileContent .= "Original value was '" . $_REQUEST[$sParamName] . "'" . PHP_EOL;
            $sFileContent .= "URL: " . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . " (Protocol: " . $_SERVER['SERVER_PROTOCOL'] . ")" . PHP_EOL;

            file_put_contents($sLogFile, $sFileContent, FILE_APPEND);
        }

        // strictly die here
        die($sMessage);
    }

}

/**
 * @deprecated since 3.1.0 use (@see cSecurity) instead
 */
class Contenido_Security extends cSecurity {
    
}