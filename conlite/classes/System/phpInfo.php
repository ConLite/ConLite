<?php

namespace ConLite\System;

/**
 * info from
 * @link https://github.com/chak10/Phpinfo-arrayzer/blob/master/phpinfo.php
 */
class phpInfo
{
    //const CONSTANT = 'constant value';

    protected static array $info;

    static public function getInfoModule($which = INFO_ALL): array
    {
        $module = self::parseModule();

        if ($which == INFO_ALL) {
            if(is_array(self::$info)) {
                return self::$info;
            } else {
                return self::$info = $module;
            }
        }

        if(isset($module[$which])) {
            return $module[$which];
        }
        return [];
    }

    static private function parse($flags): array
    {
        $info_arr = [];
        ob_start();
        phpinfo($flags);
        $info_lines = explode("\n", strip_tags(ob_get_clean(), "<tr><td><h2>"));
        foreach ($info_lines as $line) {
            if (
                preg_match("~<tr><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td></tr>~", $line, $val)
                || preg_match("~<tr><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td></tr>~", $line, $val)
            ) {
                $info_arr[trim($val[1])] = trim(str_replace(';', '; ', $val[2]));
            }
        }
        return $info_arr;
    }

    static private function parseModule()
    {
        $cat = "None";
        $info_arr = [];
        ob_start();
        phpinfo(INFO_MODULES);
        $info_lines = explode("\n", strip_tags(ob_get_clean(), "<tr><td><h2>"));
        foreach ($info_lines as $line) {
            if (preg_match("~<h2>(.*)</h2>~", $line, $title)) $cat = $title[1];
            if
            (
                preg_match("~<tr><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td></tr>~", $line, $val)
                OR
                preg_match("~<tr><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td></tr>~", $line, $val)
            ) {
                $info_arr[$cat][trim($val[1])] = trim(str_replace(';', '; ', $val[2]));
            }
        }
        return $info_arr;
    }
}