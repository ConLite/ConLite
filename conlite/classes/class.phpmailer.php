<?php
/**
 * File:
 * class.phpmailer.php
 *
 * Project:
 * Contenido Content Management System
 *
 * Description:
 *  New wrapper for PHPMailer5 lib in external folder
 *
 * @package     PHPMailer5
 * @version     $Rev: 91 $
 * @author      Ortwin Pinke
 * @link        http://www.contenido.org
 *
 * $Id: class.phpmailer.php 91 2012-06-05 14:05:48Z oldperl $
 */

// security check
defined('CON_FRAMEWORK') or die('Illegal call');

cInclude('external', 'PHPMailer/class.phpmailer.php');


class PHPMailer extends PHPMailer5 {
    
    public function __construct($exceptions = false) {
        parent::__construct($exceptions);
    }
}
?>