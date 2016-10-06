<?php
/**
 * class.clAbstractTemplateParser.php
 * 
 * clAbstractTemplateParser class
 * Abstract Superclass for Template parser classes
 * 
 * @package ConLite
 * @subpackage CoreClasses
 * @version $Rev: 147 $
 * 
 * $Id: class.clAbstractTemplateParser.php 147 2012-11-12 17:37:33Z Mansveld $
 */
/**
 * @package     ConLite Backend classes
 * @version     1.1
 * @author      Stefan Welpot
 * @modified    René Mansveld
 * @copyright   ConLite <www.conlite.org>
 * @license     http://www.conlite.org/license/LIZENZ.txt
 * @link        http://www.conlite.org
 * @since       file available since ConLite release 2.0.0
 */

abstract class clAbstractTemplateParser {
    /**
     * Parst das übergeben Template
     *
     * @param $template string das zu parsende Template
     *
     * @return string das geparste Template
     */
    abstract public function parse($template);
}
?>