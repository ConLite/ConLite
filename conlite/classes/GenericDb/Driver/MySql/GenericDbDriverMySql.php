<?php

namespace ConLite\GenericDb\Driver\MySql;

use ConLite\GenericDb\Driver\GenericDbDriver;

class GenericDbDriverMySql extends GenericDbDriver
{


    public $_oItemClassInstance;
    public $_sEncoding;
    function buildJoinQuery($destinationTable, $destinationClass, $destinationPrimaryKey, $sourceClass, $primaryKey)
    {
        // Build a regular LEFT JOIN
        $field = "$destinationClass.$destinationPrimaryKey";
        $tables = "";
        $join = "LEFT JOIN $destinationTable AS $destinationClass ON " .
            Contenido_Security::toString($sourceClass . "." . $primaryKey) . " = " .
            Contenido_Security::toString($destinationClass . "." . $primaryKey);
        $where = "";

        return ["field" => $field, "table" => $tables, "join" => $join, "where" => $where];
    }

    function buildOperator($sField, $sOperator, $sRestriction)
    {
        $sOperator = strtolower($sOperator);

        $sWhereStatement = "";

        switch ($sOperator) {
            case "matchbool":
                $sqlStatement = "MATCH (%s) AGAINST ('%s' IN BOOLEAN MODE)";
                $sWhereStatement = sprintf($sqlStatement, $sField, $this->_oItemClassInstance->_inFilter($sRestriction));
                break;
            case "match":
                $sqlStatement = "MATCH (%s) AGAINST ('%s')";
                $sWhereStatement = sprintf($sqlStatement, $sField, $this->_oItemClassInstance->_inFilter($sRestriction));
                break;
            case "like":
                $sqlStatement = "%s LIKE '%%%s%%'";
                $sWhereStatement = sprintf($sqlStatement, Contenido_Security::toString($sField), $this->_oItemClassInstance->_inFilter($sRestriction));
                break;
            case "likeleft":
                $sqlStatement = "%s LIKE '%s%%'";
                $sWhereStatement = sprintf($sqlStatement, Contenido_Security::toString($sField), $this->_oItemClassInstance->_inFilter($sRestriction));
                break;
            case "likeright":
                $sqlStatement = "%s LIKE '%%%s'";
                $sWhereStatement = sprintf($sqlStatement, Contenido_Security::toString($sField), $this->_oItemClassInstance->_inFilter($sRestriction));
                break;
            case "notlike":
                $sqlStatement = "%s NOT LIKE '%%%s%%'";
                $sWhereStatement = sprintf($sqlStatement, Contenido_Security::toString($sField), $this->_oItemClassInstance->_inFilter($sRestriction));
                break;
            case "notlikeleft":
                $sqlStatement = "%s NOT LIKE '%s%%'";
                $sWhereStatement = sprintf($sqlStatement, Contenido_Security::toString($sField), $this->_oItemClassInstance->_inFilter($sRestriction));
                break;
            case "notlikeright":
                $sqlStatement = "%s NOT LIKE '%%%s'";
                $sWhereStatement = sprintf($sqlStatement, Contenido_Security::toString($sField), $this->_oItemClassInstance->_inFilter($sRestriction));
                break;
            case "diacritics":
                if (!is_object($GLOBALS["_cCharTable"])) {
                    $GLOBALS["_cCharTable"] = new cCharacterConverter;
                }

                $aliasSearch = [];

                $metaCharacters = ["*", "[", "]", "^", '$', "\\", "*", "'", '"', '+'];

                for ($i = 0; $i < strlen($sRestriction); $i++) {
                    $char = substr($sRestriction, $i, 1);

                    $aliases = [];

                    $aliases = array_merge($aliases, $GLOBALS["_cCharTable"]->fetchDiacriticCharactersForNormalizedChar($this->_sEncoding, $char));
                    $normalizedChars = $GLOBALS["_cCharTable"]->fetchNormalizedCharsForDiacriticCharacter($this->_sEncoding, $char);

                    foreach ($normalizedChars as $normalizedChar) {
                        $aliases = array_merge($aliases, $GLOBALS["_cCharTable"]->fetchDiacriticCharactersForNormalizedChar($this->_sEncoding, $normalizedChar));
                    }

                    $aliases = array_merge($aliases, $normalizedChars);

                    if ($aliases !== []) {
                        $aliases[] = $char;
                        $allAliases = [];

                        foreach ($aliases as $alias) {
                            $alias1 = $this->_oItemClassInstance->_inFilter($alias);
                            $allAliases[] = $alias1;
                            $allAliases[] = $alias;
                        }

                        $allAliases = array_unique($allAliases);
                        $aliasSearch[] = "(" . implode("|", $allAliases) . ")";
                    } else {
                        $addChars = [];


                        if (in_array($char, $metaCharacters)) {
                            $addChars[] = "\\\\" . $char;
                        } else {
                            $addChars[] = $char;

                            $vChar = $this->_oItemClassInstance->_inFilter($char);

                            if ($char != $vChar) {
                                $addChars[] = in_array($vChar, $metaCharacters) ? "\\\\" . $vChar : $vChar;
                            }
                        }

                        $aliasSearch[] = "(" . implode("|", $addChars) . ")";
                    }
                }

                $restriction = "'" . implode("", $aliasSearch) . "'";
                $sWhereStatement = implode(" ", [$sField, "REGEXP", $restriction]);

                break;
            case "fulltext":

                break;
            case "in":
                if (is_array($sRestriction)) {
                    $items = [];

                    foreach ($sRestriction as $sRestrictionItem) {
                        $items[] = "'" . $this->_oItemClassInstance->_inFilter($sRestrictionItem) . "'";
                    }

                    $sRestriction = implode(", ", $items);
                } else {
                    $sRestriction = "'" . $sRestriction . "'";
                }

                $sWhereStatement = implode(" ", [$sField, "IN (", $sRestriction, ")"]);
                break;
            case "notin":
                if (is_array($sRestriction)) {
                    $items = [];

                    foreach ($sRestriction as $Restriction) {
                        $items[] = "'" . $this->_oItemClassInstance->_inFilter($Restriction) . "'";
                    }

                    $sRestriction = implode(", ", $items);
                } else {
                    $sRestriction = "'" . $sRestriction . "'";
                }

                $sWhereStatement = implode(" ", [$sField, "NOT IN (", $sRestriction, ")"]);
                break;
            default :
                $sRestriction = "'" . $this->_oItemClassInstance->_inFilter($sRestriction) . "'";

                $sWhereStatement = implode(" ", [$sField, $sOperator, $sRestriction]);
        }

        return $sWhereStatement;
    }

}