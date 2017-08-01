<?php
/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * Contenido i18n Functions
 * 
 * @package     Contenido Backend includes
 * @version     $Rev: 340 $
 * @author      Timo A. Hummel
 * @author      Ortwin Pinke <o.pinke@php-backoffice.de>
 * @copyright   four for business AG <www.4fb.de>
 * @license     http://www.contenido.org/license/LIZENZ.txt
 * @link        http://www.4fb.de
 * @link        http://www.contenido.org
 * @since       file available since contenido release <= 4.6
 * 
 *   $Id: functions.i18n.php 340 2015-08-20 13:31:29Z oldperl $
 */

if(!defined('CON_FRAMEWORK')) {
	die('Illegal call');
}

/**
 * trans($string)
 *
 * gettext wrapper (for future extensions). Usage:
 * trans("Your text which has to be translated");
 *
 * @param $string string The string to translate
 * @return string  Returns the translation
 * 
 * @deprecated since 4.8.16 CL, use i18n instead, function will be deleted in one of next versions
 */
function trans($string) {
    return cI18n::__i18n($string);
}
/**
 * i18n($string)
 *
 * gettext wrapper (for future extensions). Usage:
 * i18n("Your text which has to be translated");
 *
 * @param $string string The string to translate
 * @param $domain string The domain to look up
 * @return string  Returns the translation
 */
function i18n($string, $domain = "conlite") {
    return cI18n::__($string, $domain);
}

/**
 * i18nEmulateGettext()
 *
 * Emulates GNU gettext
 *
 * @param $string string The string to translate
 * @param $domain string The domain to look up
 * @return string  Returns the translation
 */
function i18nEmulateGettext($string, $domain = "conlite") {
    return cI18n::emulateGettext($string, $domain);
}

/**
 * i18nInit()
 *
 * Initializes the i18n stuff.
 *
 * @global string $i18nLanguage
 * @global array $i18nDomains
 * @param string $localePath
 * @param string $langCode 
 */
function i18nInit($localePath, $langCode, $domain = 'conlite') {
    cI18n::init($localePath, $langCode, $domain);
}

/**
 * Registers a new i18n domain.
 * 
 * @global array $i18nDomains
 * @param string $domain
 * @param string $localePath
 */
function i18nRegisterDomain($domain, $localePath) {
    cI18n::registerDomain($domain, $localePath);
}

/**
 * i18nStripAcceptLanguages($accept)
 *
 * Strips all unnecessary information from the $accept string.
 * Example: de,nl;q=0.7,en-us;q=0.3 would become an array with de,nl,en-us
 *
 * @return array Array with the short form of the accept languages  
 */
function i18nStripAcceptLanguages($accept) {
    $languages = explode(',', $accept);
    foreach($languages as $value)	{
        $components = explode(';', $value);
        $shortLanguages[] = $components[0];
    }	
    return ($shortLanguages);
}

/**
 * i18nMatchBrowserAccept($accept)
 *
 * Tries to match the language given by $accept to
 * one of the languages in the system.
 *
 * @return string The locale key for the given accept string 
 */
function i18nMatchBrowserAccept ($accept)
{
	$available_languages = i18nGetAvailableLanguages();
	
	/* Try to match the whole accept string */
	foreach ($available_languages as $key => $value)
	{
		list($country, $lang, $encoding, $shortaccept) = $value;
		
		if ($accept	== $shortaccept)
		{
			return $key;
		}
	}
	
	/* Whoops, we are still here. Let's match the stripped-down string.
       Example: de-ch isn't in the list. Cut it down after the "-" to "de"
       which should be in the list. */
       
    $accept = substr($accept,0,2);
	foreach ($available_languages as $key => $value)
	{
		list($country, $lang, $encoding, $shortaccept) = $value;
		
		if ($accept	== $shortaccept)
		{
			return $key;
		}
	}

	/* Whoops, still here? Seems that we didn't find any language. Return
       the default (german, yikes) */
   return (false);
}

/**
 * i18nGetAvailableLanguages()
 *
 * Returns the available_languages array to prevent globals.
 *
 * @return array All available languages
 */
function i18nGetAvailableLanguages ()
{
	/* Array notes: 
		First field: Language 
		Second field: Country 
		Third field: ISO-Encoding 
		Fourth field: Browser accept mapping 
		Fifth field: SPAW language 
	*/ 
	$aLanguages = array( 
		'ar_AA' => array('Arabic','Arabic Countries', 'ISO8859-6', 'ar','en'), 
		'be_BY' => array('Byelorussian', 'Belarus', 'ISO8859-5', 'be', 'en'), 
		'bg_BG' => array('Bulgarian','Bulgaria', 'ISO8859-5', 'bg', 'en'), 
		'cs_CZ' => array('Czech', 'Czech Republic', 'ISO8859-2', 'cs', 'cz'), 
		'da_DK' => array('Danish', 'Denmark', 'ISO8859-1', 'da', 'dk'), 
		'de_CH' => array('German', 'Switzerland', 'ISO8859-1', 'de-ch', 'de'), 
		'de_DE' => array('German', 'Germany', 'ISO8859-1', 'de', 'de'), 
		'el_GR' => array('Greek', 'Greece', 'ISO8859-7', 'el', 'en'), 
		'en_GB' => array('English', 'Great Britain', 'ISO8859-1', 'en-gb', 'en'), 
		'en_US' => array('English', 'United States', 'ISO8859-1', 'en', 'en'), 
		'es_ES' => array('Spanish', 'Spain', 'ISO8859-1', 'es', 'es'), 
		'fi_FI' => array('Finnish', 'Finland', 'ISO8859-1', 'fi', 'en'), 
		'fr_BE' => array('French', 'Belgium', 'ISO8859-1', 'fr-be', 'fr'), 
		'fr_CA' => array('French', 'Canada', 'ISO8859-1', 'fr-ca', 'fr'), 
		'fr_FR' => array('French', 'France', 'ISO8859-1', 'fr', 'fr'), 
		'fr_CH' => array('French', 'Switzerland', 'ISO8859-1', 'fr-ch', 'fr'), 
		'hr_HR' => array('Croatian', 'Croatia', 'ISO8859-2', 'hr', 'en'), 
		'hu_HU' => array('Hungarian', 'Hungary', 'ISO8859-2', 'hu', 'hu'), 
		'is_IS' => array('Icelandic', 'Iceland', 'ISO8859-1', 'is', 'en'), 
		'it_IT' => array('Italian', 'Italy', 'ISO8859-1', 'it', 'it'), 
		'iw_IL' => array('Hebrew', 'Israel', 'ISO8859-8', 'he', 'he'), 
		'nl_BE' => array('Dutch', 'Belgium', 'ISO8859-1', 'nl-be', 'nl'), 
		'nl_NL' => array('Dutch', 'Netherlands', 'ISO8859-1', 'nl', 'nl'), 
		'no_NO' => array('Norwegian', 'Norway', 'ISO8859-1', 'no', 'en'), 
		'pl_PL' => array('Polish', 'Poland', 'ISO8859-2', 'pl', 'en'), 
		'pt_BR' => array('Brazillian', 'Brazil', 'ISO8859-1', 'pt-br', 'br'), 
		'pt_PT' => array('Portuguese', 'Portugal', 'ISO8859-1', 'pt', 'en'), 
		'ro_RO' => array('Romanian', 'Romania', 'ISO8859-2', 'ro', 'en'), 
		'ru_RU' => array('Russian', 'Russia', 'ISO8859-5', 'ru', 'ru'), 
		'sh_SP' => array('Serbian Latin', 'Yugoslavia', 'ISO8859-2', 'sr', 'en'), 
		'sl_SI' => array('Slovene', 'Slovenia', 'ISO8859-2', 'sl', 'en'), 
		'sk_SK' => array('Slovak', 'Slovakia', 'ISO8859-2', 'sk', 'en'), 
		'sq_AL' => array('Albanian', 'Albania', 'ISO8859-1', 'sq', 'en'), 
		'sr_SP' => array('Serbian Cyrillic', 'Yugoslavia', 'ISO8859-5', 'sr-cy', 'en'), 
		'sv_SE' => array('Swedish', 'Sweden', 'ISO8859-1', 'sv', 'se'),
		'tr_TR' => array('Turkisch', 'Turkey', 'ISO8859-9', 'tr', 'tr') 
	);

	return ($aLanguages); 
}

/**
 * translate strings in modules
 * 
 * @global int $cCurrentModule
 * @global string $lang
 * @global cApiModuleTranslationCollection $mi18nTranslator
 * @param string $string string to translate
 * @return string translated string 
 */
function mi18n($string) {
    global $cCurrentModule, $lang, $mi18nTranslator;
    
    if(!is_object($mi18nTranslator)) {
        $mi18nTranslator = new cApiModuleTranslationCollection;
    }
    return $mi18nTranslator->fetchTranslation($cCurrentModule, $lang, $string);
}	
?>
