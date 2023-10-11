<?php

namespace ConLite\GenericDb\Driver;

class GenericDbDriver
{
    public $_sEncoding;
    public $_oItemClassInstance;

    public function setEncoding($sEncoding)
    {
        $this->_sEncoding = $sEncoding;
    }

    public function setItemClassInstance($oInstance)
    {
        $this->_oItemClassInstance = $oInstance;
    }

    public function buildJoinQuery($destinationTable, $destinationClass, $destinationPrimaryKey, $sourceClass, $primaryKey)
    {

    }

    public function buildOperator($sField, $sOperator, $sRestriction)
    {

    }
}