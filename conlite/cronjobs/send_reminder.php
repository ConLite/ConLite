<?php
/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * Cronjob to send reminder items
 * 
 * @package    Backend
 * @subpackage Cronjobs
 * @version    $Rev$
 * @author     Timo A. Hummel
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.6
 *
 *   $Id$:
 */

if (!defined("CON_FRAMEWORK")) {
    define("CON_FRAMEWORK", true);
}

// Contenido startup process
include_once ('../includes/startup.php');

global $cfg, $client;

$oldclient = $client;

if(!isRunningFromWeb() || function_exists("runJob") || $area == "cronjobs")
{
	$db = new DB_ConLite;

	$sql = "SELECT idclient FROM ".$cfg["tab"]["clients"];
	$db->query($sql);

	$clients = array();

	while ($db->next_record())
	{
		$clients[] = $db->f("idclient");
	}

	foreach ($clients as $client)
	{
		$mydate = time();

    	$props = new PropertyCollection;
    	$props->select("itemtype = 'idcommunication' AND type = 'todo' AND name = 'reminderdate' AND value < $mydate AND value != 0 AND idclient=$client");
		$pastreminders = array();

    	while ($prop = $props->next())
    	{
    		$pastreminders[] = $prop->get("itemid");
    	}

    	$todoitem = new TODOItem;

    	foreach ($pastreminders as $reminder)
    	{

    		$todoitem->loadByPrimaryKey($reminder);

    		if ($todoitem->get("idclient") == $client)
    		{
        		/* Check if email noti is active */
        		if ($todoitem->getProperty("todo", "emailnoti") == 1 && $todoitem->getProperty("todo", "emailnoti-sent") == 0)
        		{
					//modified : 2008-07-03 - use php mailer class instead of mail()
					$sMailhost = getSystemProperty('system', 'mail_host');
					if ($sMailhost == '') {
						$sMailhost = 'localhost';
					} 
					
					$oMail = new PHPMailer();
					$oMail->Host = $sMailhost;
					$oMail->IsHTML(0);
					$oMail->WordWrap = 1000;
					$oMail->IsMail();
				
        			$user = new User;
        			$user->loadUserByUserID($todoitem->get("recipient"));

					$oMail->AddAddress($user->getField("email"), "");
        			$realname = $user->getField("realname");
        			$oMail->Subject = $todoitem->get("subject");

        			$client = $todoitem->get("idclient");
        			$clientname = getClientName($client);

        			$todoitem->setProperty("todo", "emailnoti-sent", "1");
        			$todoitem->setProperty("todo", "emailnoti", "0");

        			$message = i18n("Hello %s,\n\nyou've got a new reminder for the client '%s' at\n%s:\n\n%s");

        			$path = $cfg["path"]["contenido_fullhtml"];

        			$message = sprintf($message, $realname, $clientname, $path, $todoitem->get("message"));
					$oMail->Body = $message;
        			$oMail->Send();
        		}

        		$todoitem->setProperty("todo", "reminderdate", "0");
    		}
    	}

	}

}

$client = $oldclient;
?>
