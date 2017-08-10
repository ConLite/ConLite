<?php
/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * Contenido Start Screen
 * 
 * Requirements: 
 * @con_php_req 5.0
 * 
 *
 * @package    Contenido Backend includes
 * @version    1.0.4
 * @author     Jan Lengowski
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.6
 * 
 * {@internal 
 *   created 2003-01-21
 *   modified 2008-06-26, Dominik Ziegler, update notifier class added
 *   modified 2008-06-27, Frederic Schneider, add security fix
 *   modified 2009-12-14, Dominik Ziegler, use User::getRealname() for user name output and provide username fallback
 *   modified 2010-05-20, Oliver Lohkemper, add param true for get active admins
 *   modified 2011-01-28, Dominik Ziegler, added missing notice in backend home when no clients are available [#CON-379]
 *
 *   $Id: main.login.php 309 2014-05-19 11:21:16Z oldperl $:
 * }}
 * 
 */

if(!defined('CON_FRAMEWORK')) {
	die('Illegal call');
}

cInclude('pear', 'XML/Parser.php');
cInclude('pear', 'XML/RSS.php');

if(!isset($oTpl) || !is_object($oTpl)) {
    $oTpl = new Template();
}
$oTpl->reset();

if ($saveLoginTime == true) {
	$sess->register("saveLoginTime");
	$saveLoginTime= 0;

	$vuser= new User();

	$vuser->loadUserByUserID($auth->auth["uid"]);

	$lastTime= $vuser->getUserProperty("system", "currentlogintime");
	$timestamp= date("Y-m-d H:i:s");
	$vuser->setUserProperty("system", "currentlogintime", $timestamp);
	$vuser->setUserProperty("system", "lastlogintime", $lastTime);

}

$vuser= new User();
$vuser->loadUserByUserID($auth->auth["uid"]);
$lastlogin= $vuser->getUserProperty("system", "lastlogintime");

if ($lastlogin == "") {
	$lastlogin= i18n("No Login Information available.");
}

$aNotifications = array();
// notification for requested password
if($vuser->getField('using_pw_request') == 1) {
    //$sPwNoti = $notification->returnNotification("warning", i18n("You're logged in with a temporary password. Please change your password."));
    $aNotifications[] = i18n("You're logged in with a temporary password. Please change your password.");
}

// Check, if setup folder is still available
if (file_exists(dirname(dirname(dirname(__FILE__)))."/setup")) {
    $aNotifications[] = i18n("The setup directory still exists. Please remove the setup directory before you continue.");	 
}

// Check, if sysadmin and/or admin accounts are still using well-known default passwords
$sDate = date('Y-m-d');
$sSQL = "SELECT * FROM ".$cfg["tab"]["phplib_auth_user_md5"]." 
                 WHERE (username = 'sysadmin' AND password = '48a365b4ce1e322a55ae9017f3daf0c0'
            AND (valid_from <= '".Contenido_Security::escapeDB($sDate, $db)."' OR valid_from = '0000-00-00' OR valid_from = '1000-01-01' OR valid_from is NULL) AND 
           (valid_to >= '".Contenido_Security::escapeDB($sDate, $db)."' OR valid_to = '0000-00-00' OR valid_to is NULL)) 
                         OR (username = 'admin' AND password = '21232f297a57a5a743894a0e4a801fc3'
             AND (valid_from <= '".Contenido_Security::escapeDB($sDate, $db)."' OR valid_from = '0000-00-00' OR valid_from = '1000-01-01' OR valid_from is NULL) AND 
            (valid_to >= '".Contenido_Security::escapeDB($sDate, $db)."' OR valid_to = '0000-00-00' OR valid_to = '1000-01-01' OR valid_to is NULL))
           ";
$db->query($sSQL);

if ($db->num_rows() > 0) {
    $aNotifications[] = i18n("The sysadmin and/or the admin account still contains a well-known default password. Please change immediately.");
}

if (count($aNotifications) > 0) {
    $oNotification = new Contenido_Notification();
    $sNotification = $oNotification->messageBox("warning", implode("<br />", $aNotifications), 1). "<br />";
} else {
    $sNotification = "";
}

$oTpl->set('s', 'NOTIFICATION', $sNotification);

$userid = $auth->auth["uid"];

$oTpl->set('s', 'WELCOME', "<b>" . i18n("Welcome") . " </b>" . $vuser->getRealname($userid, true) . ".");
$oTpl->set('s', 'LASTLOGIN', i18n("Last login") . ": " . $lastlogin);

$clients= $classclient->getAccessibleClients();

$cApiClient= new cApiClient;
$warnings= array ();

if (count($clients) > 1) {
	$clientform= '<form style="margin: 0px" name="clientselect" method="post" target="_top" action="' . $sess->url("index.php") . '">';
	$select= new cHTMLSelectElement("changeclient");
	$choices= array ();
	foreach ($clients as $key => $v_client) {
		if ($perm->hasClientPermission($key)) {

			$cApiClient->loadByPrimaryKey($key);
			if ($cApiClient->hasLanguages()) {
				$choices[$key]= $v_client['name'] . " (" . $key . ')';
			} else {
				$warnings[]= sprintf(i18n("Client %s (%s) has no languages"), $v_client['name'], $key);
			}

		}
	}

	$select->autoFill($choices);
	$select->setDefault($client);

	$clientselect= $select->render();

	$oTpl->set('s', 'CLIENTFORM', $clientform);
	$oTpl->set('s', 'CLIENTFORMCLOSE', "</form>");
	$oTpl->set('s', 'CLIENTSDROPDOWN', $clientselect);

	if ($perm->have_perm() && count($warnings) > 0) {
		$oTpl->set('s', 'WARNINGS', "<br>" . $notification->messageBox("warning", implode("<br>", $warnings), 0));
	} else {
		$oTpl->set('s', 'WARNINGS', '');
	}
	$oTpl->set('s', 'OKBUTTON', '<input type="image" src="images/but_ok.gif" alt="' . i18n("Change client") . '" title="' . i18n("Change client") . '" border="0">');
} else {
	$oTpl->set('s', 'OKBUTTON', '');
	$sClientForm = '';
	if ( count($clients) == 0 ) {
		$sClientForm = i18n('No clients available!');
	}
	$oTpl->set('s', 'CLIENTFORM', $sClientForm);
	$oTpl->set('s', 'CLIENTFORMCLOSE', '');

        
	foreach ($clients as $key => $v_client) {
            if ($perm->hasClientPermission($key)) {
                $cApiClient->loadByPrimaryKey($key);
                if ($cApiClient->hasLanguages()) {
                    $name= $v_client['name'] . " (" . $key . ')';
                } else {
                    $warnings[]= sprintf(i18n("Client %s (%s) has no languages"), $v_client['name'], $key);
                }
            }
	}
    
    if ($perm->have_perm() && count($warnings) > 0) {
		$oTpl->set('s', 'WARNINGS', "<br>" . $notification->messageBox("warning", implode("<br>", $warnings), 0));
	} else {
		$oTpl->set('s', 'WARNINGS', '');
	}
    
	$oTpl->set('s', 'CLIENTSDROPDOWN', $name);
}

$props= new PropertyCollection;
$props->select("itemtype = 'idcommunication' AND idclient='$client' AND type = 'todo' AND name = 'status' AND value != 'done'");

$todoitems= array ();

while ($prop= $props->next()) {
	$todoitems[]= $prop->get("itemid");
}

if (count($todoitems) > 0) {
	$in= "idcommunication IN (" . implode(",", $todoitems) . ")";
} else {
	$in= 1;
}
$todoitems= new TODOCollection;
$recipient= $auth->auth["uid"];
$todoitems->select("recipient = '$recipient' AND idclient='$client' AND $in");

while ($todo= $todoitems->next()) {
	if ($todo->getProperty("todo", "status") != "done") {
		$todoitems++;
	}
}

$sTaskTranslation = '';
if ($todoitems->count() == 1) {
  $sTaskTranslation = i18n("Reminder list: %d Task open");
} else {
  $sTaskTranslation = i18n("Reminder list: %d Tasks open");
}

$mycontenido_overview= '<a class="blue" href="' . $sess->url("main.php?area=mycontenido&frame=4") . '">' . i18n("Overview") . '</a>';
$mycontenido_lastarticles= '<a class="blue" href="' . $sess->url("main.php?area=mycontenido_recent&frame=4") . '">' . i18n("Recently edited articles") . '</a>';
$mycontenido_tasks= '<a class="blue" href="' . $sess->url("main.php?area=mycontenido_tasks&frame=4") . '">' . sprintf($sTaskTranslation, $todoitems->count()) . '</a>';
$mycontenido_settings= '<a class="blue" href="' . $sess->url("main.php?area=mycontenido_settings&frame=4") . '">' . i18n("Settings") . '</a>';

$oTpl->set('s', 'MYCONTENIDO_OVERVIEW', $mycontenido_overview);
$oTpl->set('s', 'MYCONTENIDO_LASTARTICLES', $mycontenido_lastarticles);
$oTpl->set('s', 'MYCONTENIDO_TASKS', $mycontenido_tasks);
$oTpl->set('s', 'MYCONTENIDO_SETTINGS', $mycontenido_settings);
$admins= $classuser->getSystemAdmins(true);

$sAdminTemplate = '<li class="welcome">%s, %s</li>';

$sAdminName= "";
$sAdminEmail = "";
$sOutputAdmin = "";


foreach ($admins as $key => $value) {
	if ($value["email"] != "") {
		$sAdminEmail= '<a class="blue" href="mailto:' . $value["email"] . '">' . $value["email"] . '</a>';
		$sAdminName= $value['realname'];
		$sOutputAdmin .= sprintf($sAdminTemplate, $sAdminName, $sAdminEmail);
	}
}

$oTpl->set('s', 'ADMIN_EMAIL', $sOutputAdmin);

$oTpl->set('s', 'SYMBOLHELP', '<a href="' . $sess->url("frameset.php?area=symbolhelp&frame=4") . '">' . i18n("Symbol help") . '</a>');

if (isset($cfg["contenido"]["handbook_path"]) && file_exists($cfg["contenido"]["handbook_path"])) {
	$oTpl->set('s', 'CONTENIDOMANUAL', '<a href="' . $cfg["contenido"]["handbook_url"] . '" target="_blank">' . i18n("Contenido Manual") . '</a>');
} else {
	$oTpl->set('s', 'CONTENIDOMANUAL', '');
}

// For display current online user in Contenido-Backend
$aMemberList= array ();
$oActiveUsers= new ActiveUsers($db, $cfg, $auth);
$iNumberOfUsers = 0;

// Start()
$oActiveUsers->startUsersTracking();

//Currently User Online
$iNumberOfUsers = $oActiveUsers->getNumberOfUsers();

// Find all User who is online
$aMemberList= $oActiveUsers->findAllUser();

// Template for display current user
$sTemplate = "";
$sOutput = "";	
$sTemplate= '<li class="welcome">%s, %s</li>';

foreach ($aMemberList as $key) {
	$sRealName= $key['realname'];
	$aPerms['0']= $key['perms'];
	$sOutput .= sprintf($sTemplate,  $sRealName, $aPerms['0']);
}

// set template welcome
$oTpl->set('s', 'USER_ONLINE', $sOutput);
$oTpl->set('s', 'Anzahl', $iNumberOfUsers);

// rss feed
if($perm->isSysadmin($vuser) && isset($cfg["backend"]["newsfeed"]) && $cfg["backend"]["newsfeed"] == true){
	$newsfeed = 'some news';
	$oTpl->set('s', 'CONTENIDO_NEWS', $newsfeed);
}
else{
	$oTpl->set('s', 'CONTENIDO_NEWS', '');
}

// check for new updates
$oUpdateNotifier = new Contenido_UpdateNotifier($cfg, $vuser, $perm, $sess, $belang);
$sUpdateNotifierOutput = $oUpdateNotifier->displayOutput();
$oTpl->set('s', 'UPDATENOTIFICATION', $sUpdateNotifierOutput);

$oTpl->generate($cfg["path"]["templates"] . $cfg["templates"]["welcome"]);

?>