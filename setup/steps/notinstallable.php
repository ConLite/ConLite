<?php

/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description:
 *
 * @package    Setup
 * @version    $Rev: 137 $
 * @author     unknown
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 *
 *   $Id: notinstallable.php 137 2012-10-02 12:00:00Z oldperl $:
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}


session_unset();

class cSetupNotInstallable extends cSetupMask {

    public function __construct($sReason) {
        parent::__construct("templates/notinstallable.tpl");
        $this->setHeader("Contenido Version " . C_SETUP_VERSION);
        $this->_oStepTemplate->set("s", "TITLE", "Willkommen zu dem Setup von ConLite / Welcome to the ConLite Setup");
        $this->_oStepTemplate->set("s", "ERRORTEXT", "Setup nicht ausf&uuml;hrbar / Setup not runnable");
        if ($sReason === 'session_use_cookies') {
            $this->_oStepTemplate->set("s", "REASONTEXT", "You need to set the PHP configuration directive 'session.use_cookies' to 1 and enable cookies in your browser. This setup won't work without that.");
        } elseif ($sReason === 'database_extension') {
            $this->_oStepTemplate->set("s", "REASONTEXT", "Couldn't detect neither MySQLi extension nor MySQL extension. You need to enable one of them in the PHP configuration (see dynamic extensions section in your php.ini). ConLite won't work without that.");
        } elseif ($sReason === 'php_version') {
            $this->_oStepTemplate->set("s", "REASONTEXT", "Leider erf&uuml;llt Ihr Webserver nicht die Mindestvorraussetzung von PHP " . C_SETUP_MIN_PHP_VERSION . " oder h�her. Bitte installieren Sie PHP " . C_SETUP_MIN_PHP_VERSION . " oder h&ouml;her, um mit dem Setup fortzufahren.<br /><br />Unfortunately your webserver doesn't match the minimum requirement of PHP " . C_SETUP_MIN_PHP_VERSION . " or higher. Please install PHP " . C_SETUP_MIN_PHP_VERSION . " or higher and then run the setup again.");
        } else {
            // this should not happen
            $this->_oStepTemplate->set("s", "REASONTEXT", "Reason unknown");
        }
    }

}

global $sNotInstallableReason;

$cNotInstallable = new cSetupNotInstallable($sNotInstallableReason);
$cNotInstallable->render();

die();
?>