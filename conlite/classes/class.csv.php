<?php

/**
 * Project: 
 * Contenido Content Management System
 * 
 * @package    Contenido Backend classes
 * @version    1.0.4
 * @author     Timo A. Hummel
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.6
 * 
 * $Id: class.csv.php 214 2013-01-25 15:50:04Z oldperl $:
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

class CSV {

    var $_data = array();
    var $_delimiter;

    public function __construct() {
        $this->_delimiter = ";";
    }

    public function setRow($row) {
        $args = func_num_args();

        for ($arg = 1; $arg < $args; $arg++) {
            $ma = func_get_arg($arg);
            $this->setCell($row, $arg, $ma);
        }
    }

    public function setCell($row, $cell, $data) {
        $row = Contenido_Security::escapeDB($row);
        $cell = Contenido_Security::escapeDB($cell);
        $data = Contenido_Security::escapeDB($data);

        $data = str_replace('"', '""', $data);
        $this->_data[$row][$cell] = '"' . $data . '"';
    }

    public function setDelimiter($delimiter) {
        $this->_delimiter = $delimiter;
    }

    public function make() {
        $out = '';
        foreach ($this->_data as $row => $line) {
            $out .= implode($this->_delimiter, $line);
            $out .= "\r\n";
        }
        return $out;
    }
}
?>