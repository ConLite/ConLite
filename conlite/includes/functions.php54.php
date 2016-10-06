<?php
/** 
 * Description:
 * Fix functions for PHP 5.4
 * 
 * @package Core
 * @subpackage CL-Includes
 * @author Ortwin Pinke <ortwin.pinke@conlite.org>
 * @copyright (c) 2014, www.conlite.org
 * @version $Rev: 362 $
 * 
 * $Id: functions.php54.php 362 2015-10-05 16:31:26Z oldperl $
 */

// security
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

/**
 * 
 * @return string
 */
function clCheckForPhp54() {
    if(!defined("CL_PHP54")) {
        /**
         * PHP-version equal or greater PHP 5.4
         * @constant CL_PHP54 phpversion >= 5.4
         */
        define('CL_PHP54', version_compare(PHP_VERSION, '5.4.0', '>=') ? 1:0);
    }
    return CL_PHP54;
}

function clPhp54FixedFunc($funcname, $value, $flags = '', $encoding = '') {
    if(clCheckForPhp54()) {
        if($funcname == "get_html_translation_table") {
            $value = ($value == '') ? HTML_SPECIALCHARS : $value;
        }
        $flags = (empty($flags))?ENT_COMPAT|ENT_HTML401:$flags;
        $encoding = (empty($encoding))?'ISO-8859-1':$encoding;
    } else {
        $flags = (empty($flags))?ENT_COMPAT:$flags;
    }
    
    if($funcname == "get_html_translation_table") {
        return $funcname($value, $flags);
    } else {
        return $funcname($value, $flags, $encoding);
    }
}

/**
 *
 * @uses clPhp54FixedFunc multi fix func for PHP5.4
 * @author Ortwin Pinke <ortwinpinke@conlite.org>
 * 
 * @param string $value
 * @param mixed $flags
 * @param string $encoding
 * @return string
 */
function clHtmlSpecialChars($value, $flags = '', $encoding = '') {    
    return clPhp54FixedFunc("htmlspecialchars", $value, $flags, $encoding);
}

/**
 * 
 * @uses clPhp54FixedFunc multi fix func for PHP5.4
 * @author Ortwin Pinke <ortwinpinke@conlite.org>
 * 
 * @param string $value
 * @param mixed $flags
 * @param string $encoding
 * @return string
 */
function clHtmlEntityDecode($value, $flags = '', $encoding = '') {
    return clPhp54FixedFunc("html_entity_decode", $value, $flags, $encoding);
}

/**
 *
 * @uses clPhp54FixedFunc multi fix func for PHP5.4
 * @author Ortwin Pinke <ortwinpinke@conlite.org>
 * 
 * @param string $value
 * @param mixed $flags
 * @param string $encoding
 * @return string
 */
function clHtmlEntities($value, $flags = '', $encoding = '') {
    return clPhp54FixedFunc("htmlentities", $value, $flags, $encoding);
}

/**
 * 
 * @uses clPhp54FixedFunc multi fix func for PHP5.4
 * @author Ortwin Pinke <ortwinpinke@conlite.org>
 * 
 * @param string $table
 * @param mixed $flags
 * @return string
 */
function clGetHtmlTranslationTable($table = '', $flags = '') {
    return clPhp54FixedFunc("get_html_translation_table", $table, $flags);
}


// hold old functions from con 4.8 but use new ConLite functions, mark them as deprecated

/**
 * Use compatible clHtmlSpecialChars instead
 * @deprecated since version 2.0
 */
if (function_exists('conHtmlSpecialChars') == false) {
	function conHtmlSpecialChars($value, $flags = '', $encoding = '') {
		return clHtmlSpecialChars($value, $flags, $encoding);
	}
}

/**
 * Use compatible clHtmlEntityDecode instead
 * @deprecated since version 2.0
 */
if (function_exists('conHtmlEntityDecode') == false) {
	function conHtmlEntityDecode($value, $flags = '', $encoding = '') {
		return clHtmlEntityDecode($value, $flags, $encoding);
	}
}

/**
 * Use compatible clHtmlEntities instead
 * @deprecated since version 2.0
 */
if (function_exists('conHtmlentities') == false) {
	function conHtmlentities($value, $flags = '', $encoding = '') {
            return clHtmlEntities($value, $flags, $encoding);
	}
}

/**
 * Use compatible clGetHtmlTranslationTable instead
 * @deprecated since version 2.0
 */
if (function_exists('conGetHtmlTranslationTable') == false) {
	function conGetHtmlTranslationTable($table = '', $flags = '') {
		return clGetHtmlTranslationTable($table, $flags);
	}
}