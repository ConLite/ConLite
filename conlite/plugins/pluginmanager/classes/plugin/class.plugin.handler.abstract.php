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
abstract class pluginHandlerAbstract implements Plugins {
    
    /**
     * Holds path to plugin dir
     * @var string
     */
    protected static $_piPath;
    
    /**
     *
     * @var string 
     */
    protected static $_piUrl;
    
    /**
     * Holds template name
     * @var string
     */
    protected static $_piTemplate = "default";
    
    /**
     * Returns the name of the plugin directory
     * @return string
     */
    public static function getName() {
        return basename(self::_getDir());
    }
    /**
     * Returns the absolute server path
     * @return string
     */
    public static function getPath() {
        return self::_getDir().DIRECTORY_SEPARATOR;
    }
    
    public static function getIncludesPath() {
        return self::_getDir().DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR;
    }
    
    public static function getTplPath() {
        return self::_getDir().DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR.self::$_piTemplate.DIRECTORY_SEPARATOR;
    }
    
    public static function getUrl() {
        ;
    }
    
    protected static function _getDir() {
        if(empty(self::$_piPath)) {
            $oReflector = new ReflectionClass(get_called_class());
            self::$_piPath = dirname(dirname($oReflector->getFileName()));
        }
        return self::$_piPath;
    }
}
