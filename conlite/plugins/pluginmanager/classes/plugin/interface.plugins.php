<?php
/**
 * 
 */

if(!defined('CON_FRAMEWORK')) {
	die('Illegal call');
}

/**
 * 
 */
interface Plugins {
    
    public static function getPath();
    public static function getUrl();
    public static function getIncludesPath();
    public static function getTplPath();
}
