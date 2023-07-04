<?php

/* @var $feuser FrontendUser */
/* @var $db DB_ConLite */
function frontendusers_valid_to_getTitle ()
{
	return i18n("Valid to");	
}

/**
 * @throws Exception
 */
function frontendusers_valid_to_display ()
{
	global $feuser;
	
	$template  = '%s';
    
	$currentValue = $feuser->get("valid_to");

	if($currentValue == "0000-00-00 00:00:00" || $currentValue == "") {
		$currentValue = '';
	} else {
		$datetime = new DateTime($currentValue);
		$currentValue = $datetime->format('Y-m-d\TH:i');
	}

	$sValidFrom = '<input 
	id="valid_to" 
	type="datetime-local" 
	name="valid_to" 
	pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}" 
	value="'.$currentValue.'" />';
	return sprintf($template,$sValidFrom);
}

function frontendusers_valid_to_wantedVariables ()
{
	return (array("valid_to"));	
}

/**
 * check and store valid_to date/datetime
 *
 * @global FrontendUser $feuser
 * @param array $variables 
 */
function frontendusers_valid_to_store ($variables) { 
    global $feuser;

    if(!is_null($variables["valid_to"])) {
        $feuser->set("valid_to", $variables["valid_to"], false);
    }
}