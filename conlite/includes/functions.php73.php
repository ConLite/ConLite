<?php

/**
 * PHP 7.3 functions for older PHP versions
 * 
 * @package Core
 * @subpackage functions
 * @version $Rev$
 * @since 2.0.3
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright (c) 2019, conlite.org
 * @license http://www.gnu.de/documents/gpl.en.html GPL v3 (english version)
 * @license http://www.gnu.de/documents/gpl.de.html GPL v3 (deutsche Version)
 * @link http://www.conlite.org ConLite.org
 * 
 * $Id$
 */
// security check
defined('CON_FRAMEWORK') or die('Illegal call');


if (!function_exists('is_countable')) {

    /**
     * Verify that the contents of a variable is a countable value
     * <p>Verify that the contents of a variable is an <code>array</code> or an object implementing Countable</p>
     * @param mixed $var <p>The value to check</p>
     * @return bool <p>Returns <b><code>TRUE</code></b> if <code>var</code> is countable, <b><code>FALSE</code></b> otherwise.</p>
     * @link http://php.net/manual/en/function.is-countable.php
     * 
     * @param Countable $var
     * @return boolean
     */
    function is_countable($var) {
        return (is_array($var) || $var instanceof Countable);
    }

}