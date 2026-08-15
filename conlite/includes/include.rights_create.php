<?php
/**
 * Project:
 * Contenido Content Management System
 *
 * Description:
 * Display languages
 *
 * Requirements:
 * @con_php_req 5.0
 *
 *
 * @package    Contenido Backend includes
 * @version    1.0.2
 * @author     Timo A. Hummel
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.6
 */

use ConLite\Conlite\User;
use ConLite\Conlite\UserCollection;
use ConLite\Exceptions\Exception;

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

/**
 * @var Contenido_Notification $notification
 * @var User $user
 */

cInclude('includes', 'functions.rights.php');

$action = cRegistry::getAction();
$area = cRegistry::getArea();
$perm = cRegistry::getPerm();


if (!$perm->have_perm_area_action($area, $action)) {
    $notification->displayNotification("error", i18n("Permission denied"));
    return;
}

$auth = cRegistry::getAuth();
$frame = cRegistry::getFrame();
$lang = cRegistry::getLanguageId();
$perm = cRegistry::getPerm();
$sess = cRegistry::getSession();
$belang = cRegistry::getBackendLanguage();

$postArray = filter_input_array(INPUT_POST, [
    'username' => FILTER_SANITIZE_STRING,
    'realname' => FILTER_SANITIZE_STRING,
    'password' => FILTER_SANITIZE_STRING,
    'passwordagain' => FILTER_SANITIZE_STRING,
    'email' => [FILTER_SANITIZE_EMAIL, FILTER_VALIDATE_EMAIL],
    'telephone' => FILTER_SANITIZE_STRING,
    'address_street' => FILTER_SANITIZE_STRING,
    'address_zip' => FILTER_SANITIZE_STRING,
    'address_city' => FILTER_SANITIZE_STRING,
    'address_country' => FILTER_SANITIZE_STRING,
    'mclient' => [
        'filter' => FILTER_VALIDATE_INT,
        'flags' => FILTER_REQUIRE_ARRAY
    ],
    'mlang' => [
        'filter' => FILTER_VALIDATE_INT,
        'flags' => FILTER_REQUIRE_ARRAY
    ],
    'wysi' => FILTER_VALIDATE_BOOL,
    'valid_from' => FILTER_SANITIZE_STRING,
    'valid_to' => FILTER_SANITIZE_STRING,
]);

$aPerms = [];
$sNotification = '';
$bError = false;

if ($action == 'user_createuser') {
    $cleanUsername = preg_replace('/["\'\/\§$%&]/i', '', $postArray['username']);
    $cleanRealname = preg_replace('/["\'\/\§$%&]/i', '', $postArray['realname']);

    if (empty($postArray['username'])) {
        $sNotification = $notification->returnNotification("warning", i18n("Username can't be empty"));
        $bError = true;
    } elseif ($postArray['username'] !== $cleanUsername || $postArray['realname'] !== $cleanRealname) {
        $sNotification = $notification->returnNotification("warning", i18n("Special characters in username and name are not allowed."));
        $bError = true;
    } elseif (empty($postArray['password']) || empty($postArray['passwordagain'])) {
        $sNotification = $notification->returnNotification("warning", i18n("Password can't be empty"));
        $bError = true;
    } else {

        if (is_array($postArray['mclient']) && count($postArray['mclient']) > 0) {
            // Prevent setting the permissions for a client without a language of that client
            foreach ($postArray['mclient'] as $selectedClient) {
                // Get all available languages for selected client
                $clientLanguageCollection = new cApiClientLanguageCollection();
                $availablelanguages = $clientLanguageCollection->getLanguagesByClient($selectedClient);

                if (count($postArray['mlang']) == 0) {
                    // User has no selected language
                    $sNotification = $notification->returnNotification("warning", i18n("Please select a language for your selected client."));
                    $bError = true;
                } elseif (!$availablelanguages) {
                    // Client has no assigned language(s)
                    $sNotification = $notification->returnNotification("warning", i18n("You can only assign users to a client with languages."));
                    $bError = true;
                } else {
                    // Client has one or more assigned language(s)
                    foreach ($postArray['mlang'] as $selectedlanguage) {
                        if (!$clientLanguageCollection->hasLanguageInClients($selectedlanguage, $postArray['mclient'])) {
                            // Selected language are not assigned to selected client
                            $sNotification = $notification->returnNotification("warning", i18n("You have to select a client with a language of that client."));
                            $bError = true;
                        }
                        if ($bError) {
                            break;
                        }
                    }
                }
            }
        }

        if (!$bError) {
            $aPerms = buildUserOrGroupPermsFromRequest(true);

            if (User::usernameExists($postArray['username'])) {
                // username already exists
                $sNotification = $notification->returnNotification("warning", i18n("Username already exists"));
                $bError = true;
            } elseif (($passCheck = User::checkPasswordMask($postArray['password'])) !== User::PASS_OK) {
                $sNotification = $notification->returnNotification("warning", User::getErrorString($passCheck));
                $bError = true;
            } elseif (strcmp($postArray['password'], $postArray['passwordagain']) == 0) {
                $userCollection = new UserCollection();
                try {
                    $user = $userCollection->create($postArray['username']);
                } catch (Exception $e) {
                    $sNotification = $notification->returnNotification("error", $e->getMessage());
                }
                
                if($user instanceof User && $user->isLoaded()) {
                    // fill in all user settings
                    $pwNotOk = $user->setPassword($postArray['password']);
                    $user->setRealName($postArray['realname']);
                    $user->setMail($postArray['email']);
                    $user->setTelNumber($postArray['telephone']);
                    $user->setStreet($postArray['address_street']);
                    $user->setCity($postArray['address_city']);
                    $user->setZip($postArray['address_zip']);
                    $user->setCountry($postArray['address_country']);
                    $user->setUseWysi($postArray['wysi']);
                    $user->setValidDateFrom($postArray['valid_from']);
                    $user->setValidDateTo($postArray['valid_to']);
                    $user->setPerms($aPerms);
                    
                    if(!$pwNotOk && $user->store()) {
                        $sNotification = $notification->returnNotification("ok", i18n("User created"));
                        unset($postArray);
                        $aPerms = [];
                        $userId = $user->getUserId();
                    }  else {
                        if($pwNotOk) {
                            $sNotification = $notification->returnNotification("error", User::getErrorString($pwNotOk));
                        } else {
                            $sNotification = $notification->returnNotification("error", "Error saving the user to the database.");
                        }
                    }
                }
            } else {
                $sNotification = $notification->returnNotification("warning", i18n("Passwords don't match"));
            }
        }
    }
}

$cfgColor = cRegistry::getConfigValue('color');

/**
 * @var Template $tpl
 */
$tpl->reset();
$tpl->set('s', 'NOTIFICATION', $sNotification);

$form = '<form name="user_properties" method="post" action="' . $sess->url("main.php?") . '">
                 ' . $sess->hidden_session(true) . '
                 <input type="hidden" name="area" value="' . $area . '">
                 <input type="hidden" name="action" value="user_createuser">
                 <input type="hidden" name="frame" value="' . $frame . '">
                 <input type="hidden" name="idlang" value="' . $lang . '">';

$tpl->set('s', 'FORM', $form);
$tpl->set('s', 'BORDERCOLOR', $cfgColor["table_border"]);
$tpl->set('s', 'BGCOLOR', $cfgColor["table_dark"]);
$tpl->set('s', 'SUBMITTEXT', i18n("Save changes"));

$tpl->set('d', 'CATNAME', i18n("Property"));
$tpl->set('d', 'BGCOLOR', $cfgColor["table_header"]);
$tpl->set('d', 'BORDERCOLOR', $cfgColor["table_border"]);
$tpl->set('d', 'CATFIELD', i18n("Value"));
$tpl->next();

$tpl->set('d', 'CATNAME', i18n("Username"));
$tpl->set('d', 'BGCOLOR', $cfgColor["table_light"]);
$tpl->set('d', 'BORDERCOLOR', $cfgColor["table_border"]);
$tpl->set('d', 'CATFIELD', formGenerateField('text', 'username', $postArray['username'], 40, 32));
$tpl->next();

$tpl->set('d', 'CATNAME', i18n("Name"));
$tpl->set('d', 'BGCOLOR', $cfgColor["table_dark"]);
$tpl->set('d', 'BORDERCOLOR', $cfgColor["table_border"]);
$tpl->set('d', 'CATFIELD', formGenerateField('text', 'realname', $postArray['realname'], 40, 255));
$tpl->next();

$tpl->set('d', 'CATNAME', i18n("New password"));
$tpl->set('d', 'BGCOLOR', $cfgColor["table_light"]);
$tpl->set('d', 'BORDERCOLOR', $cfgColor["table_border"]);
$tpl->set('d', 'CATFIELD', formGenerateField('password', 'password', '', 40, 255));
$tpl->next();

$tpl->set('d', 'CATNAME', i18n("Confirm new password"));
$tpl->set('d', 'BGCOLOR', $cfgColor["table_dark"]);
$tpl->set('d', 'BORDERCOLOR', $cfgColor["table_border"]);
$tpl->set('d', 'CATFIELD', formGenerateField('password', 'passwordagain', '', 40, 255));
$tpl->next();

$tpl->set('d', 'CATNAME', i18n("E-Mail"));
$tpl->set('d', 'BGCOLOR', $cfgColor["table_light"]);
$tpl->set('d', 'BORDERCOLOR', $cfgColor["table_border"]);
$tpl->set('d', 'CATFIELD', formGenerateField('text', 'email', $postArray['email'], 40, 255));
$tpl->next();

$tpl->set('d', 'CATNAME', i18n("Phone number"));
$tpl->set('d', 'BGCOLOR', $cfgColor["table_dark"]);
$tpl->set('d', 'BORDERCOLOR', $cfgColor["table_border"]);
$tpl->set('d', 'CATFIELD', formGenerateField('text', 'telephone', $postArray['telephone'], 40, 255));
$tpl->next();

$tpl->set('d', 'CATNAME', i18n("Street"));
$tpl->set('d', 'BGCOLOR', $cfgColor["table_light"]);
$tpl->set('d', 'BORDERCOLOR', $cfgColor["table_border"]);
$tpl->set('d', 'CATFIELD', formGenerateField('text', 'address_street', $postArray['address_street'], 40, 255));
$tpl->next();

$tpl->set('d', 'CATNAME', i18n("ZIP code"));
$tpl->set('d', 'BGCOLOR', $cfgColor["table_dark"]);
$tpl->set('d', 'BORDERCOLOR', $cfgColor["table_border"]);
$tpl->set('d', 'CATFIELD', formGenerateField('text', 'address_zip', $postArray['address_zip'], 10, 10));
$tpl->next();

$tpl->set('d', 'CATNAME', i18n("City"));
$tpl->set('d', 'BORDERCOLOR', $cfgColor["table_border"]);
$tpl->set('d', 'BGCOLOR', $cfgColor["table_light"]);
$tpl->set('d', 'CATFIELD', formGenerateField('text', 'address_city', $postArray['address_city'], 40, 255));
$tpl->next();

$tpl->set('d', 'CATNAME', i18n("Country"));
$tpl->set('d', 'BORDERCOLOR', $cfgColor["table_border"]);
$tpl->set('d', 'BGCOLOR', $cfgColor["table_dark"]);
$tpl->set('d', 'CATFIELD', formGenerateField('text', 'address_country', $postArray['address_country'], 40, 255));
$tpl->next();

// permissions of current logged in user
$aAuthPerms = explode(',', $auth->auth['perm']);

// sysadmin perm
if (in_array('sysadmin', $aAuthPerms)) {
    $tpl->set('d', 'CATNAME', i18n("System administrator"));
    $tpl->set('d', 'BORDERCOLOR', $cfgColor["table_border"]);
    $tpl->set('d', 'BGCOLOR', $cfgColor["table_light"]);
    $tpl->set('d', 'CATFIELD', formGenerateCheckbox('msysadmin', '1', in_array('sysadmin', $aPerms)));
    $tpl->next();
}

// clients admin perms
$oClientsCollection = new cApiClientCollection();
$aClients = $oClientsCollection->getAvailableClients();
$sClientCheckboxes = '';
foreach ($aClients as $idclient => $item) {
    if (in_array("admin[" . $idclient . "]", $aAuthPerms) || in_array('sysadmin', $aAuthPerms)) {
        $sClientCheckboxes .= formGenerateCheckbox("madmin[" . $idclient . "]", $idclient, in_array("admin[" . $idclient . "]", $aPerms), $item['name'] . "(" . $idclient . ")") . "<br>";
    }
}

if ($sClientCheckboxes !== '') {
    $tpl->set('d', 'CATNAME', i18n("Administrator"));
    $tpl->set('d', 'BORDERCOLOR', $cfgColor["table_border"]);
    $tpl->set('d', 'BGCOLOR', $cfgColor["table_dark"]);
    $tpl->set('d', 'CATFIELD', $sClientCheckboxes);
    $tpl->next();
}

// clients perms
$sClientCheckboxes = '';
foreach ($aClients as $idclient => $item) {
    if (in_array("client[" . $idclient . "]", $aAuthPerms) || in_array('sysadmin', $aAuthPerms) || in_array("admin[" . $idclient . "]", $aAuthPerms)) {
        $sClientCheckboxes .= formGenerateCheckbox("mclient[" . $idclient . "]", $idclient, in_array("client[" . $idclient . "]", $aPerms), $item['name'] . "(" . $idclient . ")") . "<br>";
    }
}

$tpl->set('d', 'CATNAME', i18n("Access clients"));
$tpl->set('d', 'BORDERCOLOR', $cfgColor["table_border"]);
$tpl->set('d', 'BGCOLOR', $cfgColor["table_light"]);
$tpl->set('d', 'CATFIELD', $sClientCheckboxes);
$tpl->next();

// languages perms
$aClientsLanguages = getAllClientsAndLanguages();
$sClientCheckboxes = '';
foreach ($aClientsLanguages as $item) {
    if ($perm->have_perm_client("lang[" . $item['idlang'] . "]") || $perm->have_perm_client("admin[" . $item['idclient'] . "]")) {
        $sClientCheckboxes .= formGenerateCheckbox("mlang[" . $item['idlang'] . "]", $item['idlang'], in_array("lang[" . $item['idlang'] . "]", $aPerms), $item['langname'] . "(" . $item['clientname'] . ")") . "<br>";
    }
}

$tpl->set('d', 'CATNAME', i18n("Access languages"));
$tpl->set('d', 'BORDERCOLOR', $cfgColor["table_border"]);
$tpl->set('d', 'BGCOLOR', $cfgColor["table_dark"]);
$tpl->set('d', 'CATFIELD', $sClientCheckboxes);
$tpl->next();

$tpl->set('d', 'CATNAME', i18n("Use WYSIWYG-Editor"));
$tpl->set('d', 'BORDERCOLOR', $cfgColor["table_border"]);
$tpl->set('d', 'BGCOLOR', $cfgColor["table_light"]);
$tpl->set('d', 'CATFIELD', formGenerateCheckbox('wysi', '1', $postArray['wysi']));
$tpl->next();

$sInputValidFrom = '<style>@import url(./scripts/jscalendar/calendar-contenido.css);</style>
                <script type="text/javascript" src="./scripts/jscalendar/calendar.js"></script>
                <script type="text/javascript" src="./scripts/jscalendar/lang/calendar-' . substr(strtolower($belang), 0, 2) . '.js"></script>
                <script type="text/javascript" src="./scripts/jscalendar/calendar-setup.js"></script>';
$sInputValidFrom .= '<input type="text" id="valid_from" name="valid_from" value="' . $postArray['valid_from'] . '" />&nbsp;<img src="images/calendar.gif" id="trigger" /">';
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
$tpl->set('d', 'BORDERCOLOR', $cfgColor["table_border"]);
$tpl->set('d', 'BGCOLOR', $cfgColor["table_dark"]);
$tpl->set('d', 'CATFIELD', $sInputValidFrom);
$tpl->next();

$sInputValidTo = '<input type="text" id="valid_to" name="valid_to" value="' . $postArray['valid_to'] . '" />&nbsp;<img src="images/calendar.gif" id="trigger_to" /">';
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
$tpl->set('d', 'BORDERCOLOR', $cfgColor["table_border"]);
$tpl->set('d', 'BGCOLOR', $cfgColor["table_light"]);
$tpl->set('d', 'CATFIELD', $sInputValidTo);
$tpl->next();

// Generate template
$tpl->generate(cRegistry::getConfigValue('path','templates') . cRegistry::getConfigValue('templates', 'rights_create'));