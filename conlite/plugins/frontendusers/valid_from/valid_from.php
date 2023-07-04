<?php


/* @var $feuser FrontendUser */
/* @var $db DB_ConLite */
function frontendusers_valid_from_getTitle ()
{
	return i18n("Valid from");	
}

function frontendusers_valid_from_display ()
{
	global $feuser;
	
	$template  = '%s';

    $currentValue = $feuser->get("valid_from");

	if($currentValue == "0000-00-00 00:00:00" || $currentValue == "") {
		$currentValue = null;
	} else {
		$datetime = new DateTime($currentValue);
		$currentValue = $datetime->format('Y-m-d\TH:i');
	}

	$sValidFrom = '<input 
	id="valid_from" 
	type="datetime-local" 
	name="valid_from" 
	 pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}" 
	value="'.$currentValue.'" />';

	return sprintf($template,$sValidFrom);
}

function frontendusers_valid_from_wantedVariables ()
{
	return (array("valid_from"));	
}

/**
 * check and store valid_from date/datetime
 * 
 * @global FrontendUser $feuser
 * @param array $variables 
 */
function frontendusers_valid_from_store ($variables) {
    global $feuser;
	if(!is_null($variables["valid_from"])) {
		$feuser->set("valid_from", $variables["valid_from"], false);
	}
}