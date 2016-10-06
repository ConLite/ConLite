<?php
/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * Login form
 * 
 * Requirements: 
 * @con_php_req 5.0
 * 
 *
 * @package    Contenido Backend
 * @version    1.0.4
 * @author     Jan Lengowski
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.6
 * 
 * {@internal 
 *   created  2003-01-21
 *   modified 2008-06-17, Rudi Bieller, some ugly fix for possible abuse of belang...
 *   modified 2008-07-02, Frederic Schneider, add security fix
 *   modified 2010-05-20, Murat Purc, removed request check during processing ticket [#CON-307]
 *   modified 2010-05-25, Dominik Ziegler, Remove password and username maxlength definitions at backend login [#CON-314]
 *   modified 2010-05-27, Dominik Ziegler, restored maxlength definition for username at backend login [#CON-314]
 *
 *   $Id: main.loginform.php 445 2016-07-08 09:27:16Z oldperl $:
 * }}
 * 
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}


global $cfg, $username;

$aLangs = i18nStripAcceptLanguages($_SERVER['HTTP_ACCEPT_LANGUAGE']);

foreach ($aLangs as $sValue) {
    $sEncoding = i18nMatchBrowserAccept($sValue);
    $GLOBALS['belang'] = $sEncoding;

    if ($sEncoding !== false) {
        break;
    }
}


if (isset($_POST['belang']) && $_POST['belang'] != '') {
    $sSelectedLang = $_POST['belang'];
    $GLOBALS['belang'] = $sSelectedLang;
}

if (!isset($belang) || empty($belang)) {
    $belang = $GLOBALS['belang'];
}


$db = new DB_ConLite();

$noti = "";
if (getenv('CONTENIDO_IGNORE_SETUP') != "true") {
    $aMessages = array();

    if (getSystemProperty('maintenance', 'mode') == 'enabled') {
        $aMessages[] = i18n("Contenido is in maintenance mode. Only sysadmins are allowed to login. Please try again later.");
    }

    if (count($aMessages) > 0) {
        $notification = new Contenido_Notification;
        $noti = $notification->messageBox("warning", implode("<br />", $aMessages), 1) . "<br />";
    }
}
?>
<!doctype html>
<html>
    <head>
        <base href="<?php echo $cfg['path']['contenido_fullhtml'] ?>" />
        <meta charset="utf-8" />
        <title>:: :: :: :: ConLite Login</title>
        <link rel="stylesheet" type="text/css" href="styles/contenido.css" />
        <link rel="stylesheet" type="text/css" href="styles/conlite.css?14122015" />
        <link rel="SHORTCUT ICON" HREF="<?php echo $cfg["path"]["contenido_fullhtml"] . "favicon.ico"; ?>" />    
        <script type="text/javascript" src="scripts/md5.js"></script>
        <script type="text/javascript" src="scripts/str_overview.js"></script>    
        <script type="text/javascript">
            if (top != self)
            {
                top.location = "index.php";
            }

            function doChallengeResponse()
            {
                str = document.login.username.value + ":" +
                        MD5(document.login.password.value) + ":" +
                        document.login.challenge.value;

                document.login.response.value = MD5(str);
                document.login.password.value = "";
                document.login.submit();

            }
        </script>
    </head>
    <body id="mainlogin">
        <header>
            <a id="head_logo" href="http://www.conlite.org">
                <img title="ConLite Portal" alt="ConLite Portal" src="images/cl-logo.gif" />
            </a>
            <div id="head_content">
                <div id="head_info">&nbsp;</div>
                <form name="login" method="post" action="<?php echo $this->url() ?>">
                    <div class="head_row" style="overflow: hidden;line-height: 15px;">
                        <select id="lang" name="belang" tabindex="3" class="text_medium" onchange="document.login.submit();">
                            <?php
                            $aAvailableLangs = i18nGetAvailableLanguages();
                            foreach ($aAvailableLangs as $sCode => $aEntry) {
                                if (isset($cfg["login_languages"])) {
                                    if (in_array($sCode, $cfg["login_languages"])) {
                                        list($sLanguage, $sCountry, $sCodeSet, $sAcceptTag) = $aEntry;
                                        if (isset($sSelectedLang)) {
                                            if ($sSelectedLang == $sCode) {
                                                $sSelected = ' selected="selected"';
                                            } else {
                                                $sSelected = '';
                                            }
                                        } else if ($sCode == $sEncoding) {
                                            $sSelected = ' selected="selected"';
                                        } else {
                                            $sSelected = '';
                                        }
                                        echo '<option value="' . $sCode . '"' . $sSelected . '>' . $sLanguage . ' (' . $sCountry . ')</option>';
                                    }
                                } else {
                                    list($sLanguage, $sCountry, $sCodeSet, $sAcceptTag) = $aEntry;
                                    if ($sSelectedLang) {
                                        if ($sSelectedLang == $sCode) {
                                            $sSelected = ' selected="selected"';
                                        } else {
                                            $sSelected = '';
                                        }
                                    } else if ($sCode == $sEncoding) {
                                        $sSelected = ' selected="selected"';
                                    } else {
                                        $sSelected = '';
                                    }
                                    echo '<option value="' . $sCode . '"' . $sSelected . '>' . $sLanguage . ' (' . $sCountry . ')</option>';
                                }
                            }
                            ?>
                        </select>
                        <label class="head_label" style="float: right;" for="lang"><?php echo i18n('Language'); ?>:</label>
                        <div style="clear:both;display:none;"></div>
                        <div class="text_medium_bold login_title"><?php echo i18n('ConLite Backend'); ?></div>
                        <label class="head_label" for="cl_beuser"><?php echo i18n('Login'); ?>:</label>
                        <input id="cl_beuser" tabindex="1" type="text" name="username" size="25" maxlength="32" value="<?php echo ( isset($this->auth["uname"]) ) ? clHtmlEntities(strip_tags($this->auth["uname"])) : "" ?>" />
                        
                        
                    </div>
                    <div class="head_row">
                        <input id="okbutton" tabindex="4" type="image" title="Login" alt="Login" src="images/but_ok.gif" />
                        <div style="float:right; margin-right:25px;" class="text_error">
                            <?php
                            if (isset($username) && $username != '') {
                                echo i18n('Invalid Login or Password!');
                            }
                            ?>
                        </div>
                        <div style="clear:both;display:none;"></div>
                        <div class="text_medium_bold login_title">&nbsp;</div>
                        <label class="head_label" for="cl_passwd">
                            <?php echo i18n('Password'); ?>:
                        </label>
                        <input id="cl_passwd" tabindex="2" type="password" name="password" size="25" />
                        <input type="hidden" name="vaction" value="login" />
                        <input type="hidden" name="formtimestamp" value="<?php echo time(); ?>" />
                    </div>
                </form>
            </div>
        </header>
        <nav>
            <div id="request_pw">
                <?php
                //class implements passwort recovery, all functionality is implemented there
                $oRequestPassword = new RequestPassword($db, $cfg);
                $oRequestPassword->renderForm();
                ?>
            </div>
        </nav>
        <div id="alertbox">
            <?php echo $noti; ?>
        </div>	

        <script type="text/javascript">
            if (document.login.username.value == '')
            {
                document.login.username.focus();
            }
            else
            {
                document.login.password.focus();
            }
        </script>
        <!-- <?php echo $cfg['datetag']; ?> -->
        <div style="position: absolute; left: 5px; bottom: 5px; color: #ddd;">&copy; 2012 - <?php echo date("Y") ?> <b>ConLite by CL-Community</b>, based on CONTENIDO 4.8</div>
    </body>
</html>