<?php

/**
 * Project:
 * Contenido Content Management System
 *
 * Description:
 * Display rights
 *
 * Requirements:
 * @con_php_req 5.0
 *
 *
 * @package    Contenido Backend includes
 * @version    1.0.3
 * @author     Timo A. Hummel
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.6
 *
 * {@internal
 *   created 2003-04-30
 *   modified 2008-06-24, Timo Trautmann, storage for valid from valid to added
 *   modified 2008-06-27, Frederic Schneider, add security fix
 *   modified 2008-08-26, Timo Trautmann - fixed CON-200 - User can only get lang rights, if he has client access
 *   modified 2008-10-??, Bilal Arslan - direct DB user modifications are now encapsulated in new ConUser class
 *   modified 2008-11-17, Holger Librenz - method calls for new user object modified, comments updated
 *   modified 2009-11-06, Murat Purc, replaced deprecated functions (PHP 5.3 ready)
 *   modified 2011-02-07, Murat Purc, Cleanup, optimization and formatting
 *
 *   $Id$:
 * }}
 *
 * TODO error handling!!!
 * TODO export functions to new ConUser object!
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}


cInclude('includes', 'functions.rights.php');

if (!($perm->have_perm_area_action($area, $action) || $perm->have_perm_area_action('user', $action))) {
    // access denied
    $notification->displayNotification("error", i18n("Permission denied"));
    return;
}

if (!isset($userid)) {
    // no user id, get out here
    return;
}

$aPerms = array();
$bError = false;
$sNotification = '';

// delete user
if ($action == 'user_delete') {
    $oUsers = new Users();
    $oUsers->deleteUserByID($userid);

    $sql = "DELETE FROM " . $cfg["tab"]["groupmembers"]
            . " WHERE user_id = '" . Contenido_Security::escapeDB($userid, $db) . "'";
    $db->query($sql);

    $sql = "DELETE FROM " . $cfg["tab"]["rights"]
            . " WHERE user_id = '" . Contenido_Security::escapeDB($userid, $db) . "'";
    $db->query($sql);

    $sNotification = $notification->displayNotification("info", i18n("User deleted"));
    $sTemplate = '
<html>
<head>
    <title></title>
    <link rel="stylesheet" type="text/css" href="styles/contenido.css">
    <script type="text/javascript">
        parent.parent.frames["left"].frames["left_bottom"].location.reload();
    </script>
</head>
<body style="margin:10px">
{NOTIFICATION}
</body>
</html>
    ';

    $tpl->reset();
    $tpl->set('s', 'NOTIFICATION', $sNotification);
    $tpl->generate($sTemplate);
    return;
}

// edit user
if ($action == 'user_edit') {
    $aPerms = buildUserOrGroupPermsFromRequest();

    // update user values
    // New Class User, update password and other values
    $oConUser = new ConUser($cfg, $db);
    $oConUser->setUserId($userid);
    $oConUser->setRealName($realname);
    $oConUser->setMail($email);
    $oConUser->setTelNumber($telephone);
    $oConUser->setAddressData($address_street, $address_city, $address_zip, $address_country);
    $oConUser->setUseTiny($wysi);
    $oConUser->setValidDateFrom($valid_from);
    $oConUser->setValidDateTo($valid_to);
    $oConUser->setPerms($aPerms);

    // is a password set?
    $bPassOk = false;
    if (strlen($password) > 0) {
        // yes --> check it...
        if (strcmp($password, $passwordagain) == 0) {
            // set password....
            $iPasswordSaveResult = $oConUser->setPassword($password);

            // fine, passwords are the same, but is the password valid?
            if ($iPasswordSaveResult != iConUser::PASS_OK) {
                // oh oh, password is NOT valid. check it...
                $sPassError = ConUser::getErrorString($iPasswordSaveResult, $cfg);
                $sNotification = $notification->returnNotification("error", $sPassError);
                $bError = true;
            } else {
                $bPassOk = true;
            }
        } else {
            $sNotification = $notification->returnNotification("error", i18n("Passwords don't match"));
            $bError = true;
        }
    }

    if (strlen($password) == 0 || $bPassOk == true) {
        try {
            // save, if no error occured..
            if ($oConUser->save()) {
                $sNotification = $notification->returnNotification("info", i18n("Changes saved"));
                $bError = true;
            } else {
                $sNotification = $notification->returnNotification("error", i18n("An error occured while saving user info."));
                $bError = true;
            }
        } catch (ConUserException $cue) {
            // TODO make check and info ouput better!
            $sNotification = $notification->returnNotification("error", i18n("An error occured while saving user info."));
            $bError = true;
        }
    }
}


// TODO port this to new ConUser class!
$oUser = new User();
$oUser->loadUserByUserID(Contenido_Security::escapeDB($userid, $db));

// delete user property
if (!empty($del_userprop_type) 
        && !empty($del_userprop_name)
        && is_string($del_userprop_type) 
        && is_string($del_userprop_name)) {
    $oUser->deleteUserProperty($del_userprop_type, $del_userprop_name);
}

// edit user property
if (!empty($userprop_type) 
        && !empty($userprop_name)
        && is_string($userprop_type)
        && is_string($userprop_name)
        && is_string($userprop_value)) {
    $oUser->setUserProperty($userprop_type, $userprop_name, $userprop_value);
}

if (count($aPerms) == 0 || $action == '' || !isset($action)) {
    $aPerms = explode(',', $oUser->getField('perms'));
}


$tpl->reset();
$tpl->set('s', 'SID', $sess->id);
$tpl->set('s', 'NOTIFICATION', $sNotification);

$form = '<form name="user_properties" method="post" action="' . $sess->url("main.php?") . '">
             ' . $sess->hidden_session(true) . '
             <input type="hidden" name="area" value="' . $area . '">
             <input type="hidden" name="action" value="user_edit">
             <input type="hidden" name="frame" value="' . $frame . '">
             <input type="hidden" name="userid" value="' . $userid . '">
             <input type="hidden" name="idlang" value="' . $lang . '">';

$tpl->set('s', 'FORM', $form);
$tpl->set('s', 'GET_USERID', $userid);
$tpl->set('s', 'BORDERCOLOR', $cfg["color"]["table_border"]);
$tpl->set('s', 'BGCOLOR', $cfg["color"]["table_dark"]);
$tpl->set('s', 'SUBMITTEXT', i18n("Save changes"));
$tpl->set('s', 'CANCELTEXT', i18n("Discard changes"));
$tpl->set('s', 'CANCELLINK', $sess->url("main.php?area=$area&frame=4&userid=$userid"));

$tpl->set('d', 'CATNAME', i18n("Property"));
$tpl->set('d', 'BGCOLOR', $cfg["color"]["table_header"]);
$tpl->set('d', 'BORDERCOLOR', $cfg["color"]["table_border"]);
$tpl->set('d', 'CATFIELD', i18n("Value"));
$tpl->next();

$tpl->set('d', 'CATNAME', i18n("Username"));
$tpl->set('d', 'BGCOLOR', $cfg["color"]["table_light"]);
$tpl->set('d', 'BORDERCOLOR', $cfg["color"]["table_border"]);
$tpl->set('d', 'CATFIELD', $oUser->getField('username') . '<img align="top" src="images/spacer.gif" height="20">');
$tpl->next();

$tpl->set('d', 'CATNAME', i18n("Name"));
$tpl->set('d', 'BGCOLOR', $cfg["color"]["table_dark"]);
$tpl->set('d', 'BORDERCOLOR', $cfg["color"]["table_border"]);
$tpl->set('d', 'CATFIELD', formGenerateField("text", "realname", $oUser->getField('realname'), 40, 255));
$tpl->next();

// @since 2006-07-04 Display password fields only if not authenticated via LDAP/AD
if ((isset($msysadmin) && $msysadmin) || $oUser->getField('password') != 'active_directory_auth') {
    $tpl->set('d', 'CATNAME', i18n("New password"));
    $tpl->set('d', 'BGCOLOR', $cfg["color"]["table_light"]);
    $tpl->set('d', 'BORDERCOLOR', $cfg["color"]["table_border"]);
    $tpl->set('d', 'CATFIELD', formGenerateField('password', 'password', '', 40, 255));
    $tpl->next();

    $tpl->set('d', 'CATNAME', i18n("Confirm new password"));
    $tpl->set('d', 'BGCOLOR', $cfg["color"]["table_dark"]);
    $tpl->set('d', 'BORDERCOLOR', $cfg["color"]["table_border"]);
    $tpl->set('d', 'CATFIELD', formGenerateField('password', 'passwordagain', '', 40, 255));
    $tpl->next();
}

$tpl->set('d', 'CATNAME', i18n("E-Mail"));
$tpl->set('d', 'BGCOLOR', $cfg["color"]["table_light"]);
$tpl->set('d', 'BORDERCOLOR', $cfg["color"]["table_border"]);
$tpl->set('d', 'CATFIELD', formGenerateField('text', 'email', $oUser->getField('email'), 40, 255));
$tpl->next();

$tpl->set('d', 'CATNAME', i18n("Phone number"));
$tpl->set('d', 'BGCOLOR', $cfg["color"]["table_dark"]);
$tpl->set('d', 'BORDERCOLOR', $cfg["color"]["table_border"]);
$tpl->set('d', 'CATFIELD', formGenerateField('text', 'telephone', $oUser->getField('telephone'), 40, 255));
$tpl->next();

$tpl->set('d', 'CATNAME', i18n("Street"));
$tpl->set('d', 'BGCOLOR', $cfg["color"]["table_light"]);
$tpl->set('d', 'BORDERCOLOR', $cfg["color"]["table_border"]);
$tpl->set('d', 'CATFIELD', formGenerateField('text', 'address_street', $oUser->getField('address_street'), 40, 255));
$tpl->next();

$tpl->set('d', 'CATNAME', i18n("ZIP code"));
$tpl->set('d', 'BGCOLOR', $cfg["color"]["table_dark"]);
$tpl->set('d', 'BORDERCOLOR', $cfg["color"]["table_border"]);
$tpl->set('d', 'CATFIELD', formGenerateField('text', 'address_zip', $oUser->getField('address_zip'), 10, 10));
$tpl->next();

$tpl->set('d', 'CATNAME', i18n("City"));
$tpl->set('d', 'BORDERCOLOR', $cfg["color"]["table_border"]);
$tpl->set('d', 'BGCOLOR', $cfg["color"]["table_light"]);
$tpl->set('d', 'CATFIELD', formGenerateField('text', 'address_city', $oUser->getField('address_city'), 40, 255));
$tpl->next();

$tpl->set('d', 'CATNAME', i18n("Country"));
$tpl->set('d', 'BORDERCOLOR', $cfg["color"]["table_border"]);
$tpl->set('d', 'BGCOLOR', $cfg["color"]["table_dark"]);
$tpl->set('d', 'CATFIELD', formGenerateField('text', 'address_country', $oUser->getField('address_country'), 40, 255));
$tpl->next();

// permissions of current logged in user
$aAuthPerms = explode(',', $auth->auth['perm']);

// sysadmin perm
if (in_array('sysadmin', $aAuthPerms)) {
    $tpl->set('d', 'CATNAME', i18n("System administrator"));
    $tpl->set('d', 'BORDERCOLOR', $cfg["color"]["table_border"]);
    $tpl->set('d', 'BGCOLOR', $cfg["color"]["table_light"]);
    $tpl->set('d', 'CATFIELD', formGenerateCheckbox('msysadmin', '1', in_array('sysadmin', $aPerms)));
    $tpl->next();
}

// clients admin perms
$oClientsCollection = new cApiClientCollection();
$aClients = $oClientsCollection->getAvailableClients();
$sClientCheckboxes = '';
foreach ($aClients as $idclient => $item) {
    if (in_array("admin[" . $idclient . "]", $aAuthPerms) || in_array('sysadmin', $aAuthPerms)) {
        $sClientCheckboxes .= formGenerateCheckbox("madmin[" . $idclient . "]", $idclient, in_array("admin[" . $idclient . "]", $aPerms), $item['name'] . " (" . $idclient . ")") . "<br>";
    }
}

if ($sClientCheckboxes !== '' && !in_array('sysadmin', $aPerms)) {
    $tpl->set('d', 'CATNAME', i18n("Administrator"));
    $tpl->set('d', 'BORDERCOLOR', $cfg["color"]["table_border"]);
    $tpl->set('d', 'BGCOLOR', $cfg["color"]["table_dark"]);
    $tpl->set('d', 'CATFIELD', $sClientCheckboxes);
    $tpl->next();
}

// clients perms
$sClientCheckboxes = '';
foreach ($aClients as $idclient => $item) {
    if ((in_array("client[" . $idclient . "]", $aAuthPerms) || in_array('sysadmin', $aAuthPerms) || in_array("admin[" . $idclient . "]", $aAuthPerms)) && !in_array("admin[" . $idclient . "]", $aPerms)) {
        $sClientCheckboxes .= formGenerateCheckbox("mclient[" . $idclient . "]", $idclient, in_array("client[" . $idclient . "]", $aPerms), $item['name'] . " (" . $idclient . ")") . "<br>";
    }
}

if ($sClientCheckboxes !== '' && !in_array('sysadmin', $aPerms)) {
    $tpl->set('d', 'CATNAME', i18n("Access clients"));
    $tpl->set('d', 'BORDERCOLOR', $cfg["color"]["table_border"]);
    $tpl->set('d', 'BGCOLOR', $cfg["color"]["table_light"]);
    $tpl->set('d', 'CATFIELD', $sClientCheckboxes);
    $tpl->next();
}

// languages perms
$aClientsLanguages = getAllClientsAndLanguages();
$sClientCheckboxes = '';
foreach ($aClientsLanguages as $item) {
    if (($perm->have_perm_client("lang[" . $item['idlang'] . "]") || $perm->have_perm_client("admin[" . $item['idclient'] . "]")) && !in_array("admin[" . $item['idclient'] . "]", $aPerms)) {
        $sClientCheckboxes .= formGenerateCheckbox("mlang[" . $item['idlang'] . "]", $item['idlang'], in_array("lang[" . $item['idlang'] . "]", $aPerms), $item['langname'] . " (" . $item['clientname'] . ")") . "<br>";
    }
}

if ($sClientCheckboxes != '' && !in_array('sysadmin', $aPerms)) {
    $tpl->set('d', 'CATNAME', i18n("Access languages"));
    $tpl->set('d', 'BORDERCOLOR', $cfg["color"]["table_border"]);
    $tpl->set('d', 'BGCOLOR', $cfg["color"]["table_dark"]);
    $tpl->set('d', 'CATFIELD', $sClientCheckboxes);
    $tpl->next();
}


// user properties
$aProperties = $oUser->getUserProperties();
$sPropRows = '';
foreach ($aProperties as $entry) {
    $type = $entry['type'];
    if ($type != 'system') {
        $name = $entry['name'];
        $value = $entry['value'];
        $sPropRows .= '
        <tr class="text_medium">
            <td>' . $type . '</td>
            <td>' . $name . '</td>
            <td>' . $value . '</td>
            <td>
                <a href="' . $sess->url("main.php?area=$area&frame=4&userid=$userid&del_userprop_type=$type&del_userprop_name=$name") . '"><img src="images/delete.gif" border="0" alt="Eigenschaft l�schen" title="Eigenschaft l�schen"></a>
            </td>
        </tr>';
    }
}
$table = '
    <table width="100%" cellspacing="0" cellpadding="2" style="border:1px solid ' . $cfg["color"]["table_border"] . ';">
    <tr style="background-color:' . $cfg["color"]["table_header"] . '" class="text_medium">
        <td>' . i18n("Area/Type") . '</td>
        <td>' . i18n("Property") . '</td>
        <td>' . i18n("Value") . '</td>
        <td>&nbsp;</td>
    </tr>
    ' . $sPropRows . '
    <tr class="text_medium">
        <td><input class="text_medium" type="text" size="16" maxlen="32" name="userprop_type"></td>
        <td><input class="text_medium" type="text" size="16" maxlen="32" name="userprop_name"></td>
        <td><input class="text_medium" type="text" size="32" name="userprop_value"></td>
        <td>&nbsp;</td>
    </tr>
    </table>';

$tpl->set('d', 'CATNAME', i18n("User-defined properties"));
$tpl->set('d', 'BORDERCOLOR', $cfg["color"]["table_border"]);
$tpl->set('d', 'BGCOLOR', $cfg["color"]["table_light"]);
$tpl->set('d', 'CATFIELD', $table);
$tpl->next();

// wysiwyg
$tpl->set('d', 'CATNAME', i18n("Use WYSIWYG-Editor"));
$tpl->set('d', 'BORDERCOLOR', $cfg["color"]["table_border"]);
$tpl->set('d', 'BGCOLOR', $cfg["color"]["table_dark"]);
$tpl->set('d', 'CATFIELD', formGenerateCheckbox('wysi', '1', $oUser->getField('wysi')));
$tpl->next();

// account active data (from-to)
$sCurrentValueFrom = str_replace('00:00:00', '', $oUser->getField('valid_from'));
$sCurrentValueFrom = trim(str_replace('0000-00-00', '', $sCurrentValueFrom));
$sCurrentValueFrom = trim(str_replace('1000-01-01', '', $sCurrentValueFrom));

$sInputValidFrom = '<style type="text/css">@import url(./scripts/jscalendar/calendar-contenido.css);</style>
                <script type="text/javascript" src="./scripts/jscalendar/calendar.js"></script>
                <script type="text/javascript" src="./scripts/jscalendar/lang/calendar-' . substr(strtolower($belang), 0, 2) . '.js"></script>
                <script type="text/javascript" src="./scripts/jscalendar/calendar-setup.js"></script>';
$sInputValidFrom .= '<input type="text" id="valid_from" name="valid_from" value="' . $sCurrentValueFrom . '" />&nbsp;<img src="images/calendar.gif" id="trigger" /">';
$sInputValidFrom .= '<script type="text/javascript">
                     Calendar.setup({
                         inputField:  "valid_from",
                         ifFormat:    "%Y-%m-%d",
                         button:      "trigger",
                         weekNumbers: true,
                         firstDay:    1
                     });
                     </script>';

$tpl->set('d', 'CATNAME', i18n("Valid from"));
$tpl->set('d', 'BORDERCOLOR', $cfg["color"]["table_border"]);
$tpl->set('d', 'BGCOLOR', $cfg["color"]["table_dark"]);
$tpl->set('d', 'CATFIELD', $sInputValidFrom);
$tpl->next();

$sCurrentValueTo = str_replace('00:00:00', '', $oUser->getField('valid_to'));
$sCurrentValueTo = trim(str_replace('0000-00-00', '', $sCurrentValueTo));
$sCurrentValueTo = trim(str_replace('1000-01-01', '', $sCurrentValueTo));

$sInputValidTo = '<input type="text" id="valid_to" name="valid_to" value="' . $sCurrentValueTo . '" />&nbsp;<img src="images/calendar.gif" id="trigger_to" /">';
$sInputValidTo .= '<script type="text/javascript">
                   Calendar.setup({
                       inputField:  "valid_to",
                       ifFormat:    "%Y-%m-%d",
                       button:      "trigger_to",
                       weekNumbers: true,
                       firstDay:    1
                   });
                   </script>';

$tpl->set('d', 'CATNAME', i18n("Valid to"));
$tpl->set('d', 'BORDERCOLOR', $cfg["color"]["table_border"]);
$tpl->set('d', 'BGCOLOR', $cfg["color"]["table_light"]);
$tpl->set('d', 'CATFIELD', $sInputValidTo);
$tpl->next();

// account active or not
if ($sCurrentValueFrom == '') {
    $sCurrentValueFrom = '0000-00-00';
}

if (($sCurrentValueTo == '') || ($sCurrentValueTo == '0000-00-00')) {
    $sCurrentValueTo = '9999-99-99';
}

$sCurrentDate = date('Y-m-d');

if (($sCurrentValueFrom > $sCurrentDate) || ($sCurrentValueTo < $sCurrentDate)) {
    $sAccountState = i18n("This account is currently inactive.");
    $sAccountColor = 'red';
} else {
    $sAccountState = i18n("This account is currently active.");
    $sAccountColor = 'green';
}

$tpl->set('d', 'CATNAME', '&nbsp;');
$tpl->set('d', 'BORDERCOLOR', $cfg["color"]["table_border"]);
$tpl->set('d', 'BGCOLOR', $cfg["color"]["table_dark"]);
$tpl->set('d', 'CATFIELD', '<span style="color:' . $sAccountColor . ';">' . $sAccountState . '</span>');
$tpl->next();

// Show backend user's group memberships
$aGroups = $oUser->getGroupsByUserID($userid);
if (count($aGroups) > 0) {
    asort($aGroups);
    $sGroups = implode("<br/>", $aGroups);
} else {
    $sGroups = i18n("none");
}

$tpl->set('d', 'CATNAME', i18n("Group membership"));
$tpl->set('d', 'BORDERCOLOR', $cfg["color"]["table_border"]);
$tpl->set('d', 'BGCOLOR', $cfg["color"]["table_dark"]);
$tpl->set('d', 'CATFIELD', $sGroups);
$tpl->next();

// Generate template
$tpl->generate($cfg['path']['templates'] . $cfg['templates']['rights_overview']);
?>