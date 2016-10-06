<?php
defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

abstract class cContentTypeAbstract {
    
    const SETTINGS_TYPE_PLAINTEXT = 'plaintext';
    const SETTINGS_TYPE_XML = 'xml';
    
    protected $_sSettingsType = self::SETTINGS_TYPE_PLAINTEXT;
    
    public function __construct($sRawSettings, $iId, $aContentTypes) {
        ;
    }
    
    public abstract function generateViewCode();
    public abstract function generateEditCode();
    
    public function isWysiwygCompatible() {
        return false;
    }
}