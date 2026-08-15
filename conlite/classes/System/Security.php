<?php

namespace ConLite\System;

use Contenido_Security_Exception;

class Security
{


    /**
     * Accepted backend languages
     * @var  array
     */
    protected static $_acceptedBelangValues = array('de_DE', 'en_US', 'fr_FR', 'it_IT', 'nl_NL');

    /**
     * Request paramaters, which must be numeric
     * @var  array
     */
    protected static $_mustbeNumericParameters = array(
        'client', 'changeclient', 'lang', 'changelang', 'idcat', 'idcatlang', 'idart', 'idartlang',
        'idcatart'
    );

    /**
     * Request paramaters, which are strictly forbidden
     * @var  array
     */
    protected static $_forbiddenParameters = array('cfg', 'cfgClient', 'contenido_path', '_PHPLIB', 'db', 'sess');

    /**
     * Returns accepted backend language values
     *
     * @return  array
     */
    public static function getAcceptedBelangValues()
    {
        return self::$_acceptedBelangValues;
    }

    /**
     * Returns must be numeric request parameters
     *
     * @return  array
     */
    public static function getMustbeNumericParameters()
    {
        return self::$_mustbeNumericParameters;
    }

    /**
     * Returns forbidden request parameters
     *
     * @return  array
     */
    public static function getForbiddenParameters()
    {
        return self::$_forbiddenParameters;
    }

    /**
     * Escapes string using contenido urlencoding method and escapes string for inserting
     * @static
     *
     * @param string $sString Input string
     * @param DB_ConLite $oDb Contenido database object
     * @return  string   Filtered string
     */
    public static function filter($sString, $oDb)
    {
        $sString = self::toString($sString);
        if (defined('CONTENIDO_STRIPSLASHES')) {
            $sString = stripslashes($sString);
        }
        return self::escapeDB(clHtmlSpecialChars(urlencode($sString)), $oDb, false);
    }

    /**
     * Reverts effect of method filter()
     * @static
     *
     * @param string $sString Input string
     * @return  string  Unfiltered string
     */
    public static function unFilter($sString)
    {
        $sString = self::toString($sString);
        return urldecode(htmldecode(self::unEscapeDB($sString)));
    }

    /**
     * Check: Has the variable an boolean value?
     * @static
     *
     * @param string $sVar Input string
     * @return  boolean  Check state
     */
    public static function isBoolean($sVar)
    {
        $sTempVar = $sVar;
        $sTemp2Var = self::toBoolean($sVar);
        return ($sTempVar === $sTemp2Var);
    }

    /**
     * Check: Is the variable an integer?
     * @static
     *
     * @param string $sVar Input string
     * @return  boolean  Check state
     */
    public static function isInteger($sVar)
    {
        return (preg_match('/^[0-9]+$/', $sVar));
    }

    /**
     * Check: Is the variable an string?
     * @static
     *
     * @param string $sVar Input string
     * @return  boolean  Check state
     */
    public static function isString($sVar)
    {
        return (is_string($sVar));
    }

    /**
     * Check: Is the variable formatted as MySQL DATE 'YYYY-MM-DD'
     * @static
     *
     * @param string $sVar given date/string
     * @param boolean $bCheckValid additional use of checkdate for validation
     * @return boolean true|false
     * @since ConLite 0.1.0
     *
     * @author Ortwin Pinke
     */
    public static function isMySQLDate($sVar, $bCheckValid = false)
    {
        $sVar = trim($sVar);
        $bFormatOk = preg_match("/^\d{4}-\d{2}-\d{2}$/", $sVar);
        if ($bCheckValid && $bFormatOk) {
            $aDateParts = explode("-", $sVar);
            return checkdate($aDateParts[1], $aDateParts[2], $aDateParts[0]);
        } elseif ($bFormatOk) {
            return true;
        }
        return false;
    }

    /**
     * Check: Is the variable formatted as MySQL DATETIME 'YYYY-MM-DD HH:MM:SS'
     * @static
     *
     * @param string $Var given datetime/string
     * @param boolean $bCheckValid additional use of checkdate for validation
     * @return boolean true|false
     * @since ConLite 0.1.0
     *
     * @author Ortwin Pinke
     */
    public static function isMySQLDateTime($sVar, $bCheckValid = false)
    {
        $sVar = trim($sVar);
        $bFormatOk = preg_match("/^\d{4}-\d{2}-\d{2} [0-2][0-3]:[0-5][0-9]:[0-5][0-9]$/", $sVar);
        if ($bCheckValid && $bFormatOk) {
            $aDateTimeParts = explode(" ", $sVar);
            $aDateParts = explode("-", $aDateTimeParts[0]);
            return checkdate($aDateParts[1], $aDateParts[2], $aDateParts[0]);
        } elseif ($bFormatOk) {
            return true;
        }
        return false;
    }

    /**
     * Convert an string to an boolean
     * @static
     *
     * @param string $sString Input string
     * @return  boolean  Type casted input string
     * @deprecated since ConLite 0.1.0, this function will be deleted in future versions, use buildin PHP-functions
     *
     */
    public static function toBoolean($sString)
    {
        return (bool)$sString;
    }

    /**
     * Convert a string to an integer.
     *
     * @param mixed $value
     * @return int
     */
    public static function toInteger(mixed $value): int
    {
        return match (gettype($value)) {
            'integer' => $value,
            'boolean', 'double', 'string' => intval($value),
            default => 0,
        };
    }

    /**
     * Convert an string
     * @static
     *
     * @param string $sString Input string
     * @param boolean $bHTML If true check with strip_tags and stripslashes
     * @param string $sAllowableTags Allowable tags if $bHTML is true
     * @return  string  Converted string
     */
    public static function toString($sString, $bHTML = false, $sAllowableTags = '')
    {
        $sString = (string)$sString;
        if ($bHTML == true) {
            $sString = strip_tags(stripslashes($sString), $sAllowableTags);
        }
        return $sString;
    }

    /**
     * Checks some Contenido core related request parameters against XSS
     *
     * @access  public
     * @return  bool|void  True on success otherwhise nothing.
     * @throws  Contenido_Security_Exception if one of the checks fails
     */
    public static function checkRequests()
    {
        // Check backend language
        self::checkRequestBelang();

        // Check for forbidden parameters
        self::checkRequestForbiddenParameter();

        // Check for parameters who must be numeric
        self::checkRequestMustbeNumericParameter();

        // Check session id
        self::checkRequestSession();

        return true;
    }

    /**
     * Checks backend language parameter in request.
     *
     * @return  bool|void  True on success otherwhise nothing.
     * @throws  Contenido_Security_Exception if existing backend language parameter is not valid
     */
    public static function checkRequestBelang()
    {
        if (isset($_REQUEST['belang'])) {
            $_REQUEST['belang'] = strval($_REQUEST['belang']);
            if (!in_array($_REQUEST['belang'], self::$_acceptedBelangValues)) {
                throw new Contenido_Security_Exception('Please use a valid language!', 'belang');
            }
        }
        return true;
    }

    /**
     * Checks for forbidden parameters in request.
     *
     * @return  bool|void  True on success otherwhise nothing.
     * @throws  Contenido_Security_Exception if the request contains one of forbidden parameters.
     */
    public static function checkRequestForbiddenParameter()
    {
        foreach (self::$_forbiddenParameters as $param) {
            if (isset($_REQUEST[$param])) {
                throw new Contenido_Security_Exception('Invalid call!', $param);
            }
        }
        return true;
    }

    /**
     * Checks for parameters in request who must be numeric.
     *
     * Contrary to other request checks, this method don't throws a exception. It just insures that
     * incomming values are really numeric, by type casting them to an integer.
     *
     * @return  bool  Just true
     */
    public static function checkRequestMustbeNumericParameter()
    {
        foreach (self::$_mustbeNumericParameters as $sParamName) {
            if (isset($_REQUEST[$sParamName])) {
                $sValue = $_REQUEST[$sParamName];
                if (strlen($sValue) > 0 && self::isInteger($sValue) == false) {
                    throw new Contenido_Security_Exception('Invalid call', $sParamName);
                }
            }
        }
        return true;
    }

    /**
     * Checks/Validates existing contenido session request parameter.
     *
     * @return  bool|void  True on success otherwhise nothing.
     * @throws  Contenido_Security_Exception if contenido parameter in request don't matches the required format
     */
    public static function checkRequestSession()
    {
        if (isset($_REQUEST['contenido']) && !preg_match('/^[0-9a-f]{32}$/', $_REQUEST['contenido'])) {
            if ($_REQUEST['contenido'] != '') {
                throw new Contenido_Security_Exception('Invalid call', 'contenido');
            }
        }
        return true;
    }

    /**
     * Escaped an query-string with mysql_real_escape_string
     * @static
     *
     * @param string $sString Input string
     * @param DB_ConLite $oDB Contenido database object
     * @param boolean $bUndoAddSlashes Flag for undo addslashes (optional, default: true)
     * @return  string  Converted string
     */
    public static function escapeDB($sString, $oDB = null, $bUndoAddSlashes = true)
    {
        if (!is_object($oDB)) {
            return self::escapeString($sString);
        } else {
            if (defined('CONTENIDO_STRIPSLASHES') && $bUndoAddSlashes == true) {
                $sString = stripslashes($sString);
            }
            return $oDB->Escape($sString);
        }
    }

    /**
     * Escaped an query-string with addslashes
     * @static
     *
     * @param string $sString Input string
     * @return  string  Converted string
     */
    public static function escapeString($sString)
    {
        $sString = (string)$sString;
        if (defined('CONTENIDO_STRIPSLASHES')) {
            $sString = stripslashes($sString);
        }
        return addslashes($sString);
    }

    /**
     * Un-quote string quoted with escapeDB()
     * @static
     *
     * @param string $sString Input string
     * @return  string  Converted string
     */
    public static function unescapeDB($sString)
    {
        return stripslashes($sString);
    }

}