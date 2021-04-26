<?php

class pimSqlParser {

    public static function removeComments($sSqlData) {
        $sRegEx = '@(([\'"]).*?[^\\\]\2)|((?:\#|--).*?$|/\*(?:[^/*]|/(?!\*)|\*(?!/)|(?R))*\*\/)\s*|(?<=;)\s+@ms';

        $sCleanSqlData = trim(preg_replace($sRegEx, '$1', $sSqlData));

        //Eventually remove the last ;
        if(strrpos($sCleanSqlData, ";") === strlen($sCleanSqlData) - 1) {
            $sCleanSqlData = substr($sCleanSqlData, 0, strlen($sCleanSqlData) - 1);
        }

        return $sCleanSqlData;
    }
    
    public static function replacePlaceholder($sData) {
        return str_replace(pimSetupBase::PIM_SQL_PLACEHOLDER, cRegistry::getConfigValue('sql', 'sqlprefix')."_pi", $sData);
    }

    public static function parse($sSqlData) {
        $aSingleQueries = array();
        $sSqlData = pimSqlParser::replacePlaceholder($sSqlData);
         // Processing the SQL file content	 		
        $lines = explode("\n", $sSqlData);

        $sQuery = "";
        
        // Parsing the SQL file content			 
        foreach ($lines as $sql_line):
            $sql_line = trim($sql_line);
            if($sql_line === "") continue;
            else if(strpos($sql_line, "--") === 0) continue;
            else if(strpos($sql_line, "#") === 0) continue;
                
            $sQuery .= $sql_line;
            // Checking whether the line is a valid statement
            if (preg_match("/(.*);/", $sql_line)) {
                $sQuery = trim($sQuery);
                $sQuery = substr($sQuery, 0, strlen($sQuery) - 1);

                $sQuery = pimSqlParser::removeComments($sQuery);
                
                //store this query
                $aSingleQueries[] = $sQuery;
                //reset the variable
                $sQuery = "";
            }
            
        endforeach;

        return $aSingleQueries;
    }
}