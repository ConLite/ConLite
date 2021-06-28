<?php
/**
 * AMR Content controller class
 *
 * @package     plugin
 * @subpackage  Mod Rewrite
 * @version     SVN Revision $Rev: 128 $
 * @id          $Id: class.modrewrite_content_controller.php 128 2019-07-03 11:58:28Z oldperl $:
 * @author      Murat Purc <murat@purc.de>
 * @copyright   four for business AG <www.4fb.de>
 * @license     http://www.contenido.org/license/LIZENZ.txt
 * @link        http://www.4fb.de
 * @link        http://www.contenido.org
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}


/**
 * Content controller for general settings.
 *
 * @author      Murat Purc <murat@purc.de>
 * @package     plugin
 * @subpackage  Mod Rewrite
 */
class ModRewrite_ContentController extends ModRewrite_ControllerAbstract {

    /**
     * Index action
     */
    public function indexAction() {
        // donut
        $this->_doChecks();
    }

    /**
     * Save settings action
     */
    public function saveAction() {
        $bDebug = $this->getProperty('bDebug');
        $aSeparator = $this->getProperty('aSeparator');
        $aWordSeparator = $this->getProperty('aWordSeparator');
        $routingSeparator = $this->getProperty('routingSeparator');

        $bError = false;
        $aMR = array();

        $request = (count($_POST) > 0) ? $_POST : $_GET;
        mr_requestCleanup($request);

        // use cl-mod-rewrite
        if (mr_arrayValue($request, 'use') == 1) {
            $this->_oView->use_chk = ' checked="checked"';
            $aMR['cl-mod-rewrite']['use'] = 1;
        } else {
            $this->_oView->use_chk = '';
            $aMR['cl-mod-rewrite']['use'] = 0;
        }

        // root dir
        if (mr_arrayValue($request, 'rootdir', '') !== '') {
            if (!preg_match('/^[a-zA-Z0-9\-_\/\.]*$/', $request['rootdir'])) {
                $sMsg = i18n("The root directory has a invalid format, alowed are the chars [a-zA-Z0-9\-_\/\.]", "cl-mod-rewrite");
                $this->_oView->rootdir_error = $this->_notifyBox('error', $sMsg);
                $bError = true;
            } elseif (!is_dir($_SERVER['DOCUMENT_ROOT'] . $request['rootdir'])) {

                if (mr_arrayValue($request, 'checkrootdir') == 1) {
                    // root dir check is enabled, this results in error
                    $sMsg = i18n("The specified directory '%s' does not exists", "cl-mod-rewrite");
                    $sMsg = sprintf($sMsg, $_SERVER['DOCUMENT_ROOT'] . $request['rootdir']);
                    $this->_oView->rootdir_error = $this->_notifyBox('error', $sMsg);
                    $bError = true;
                } else {
                    // root dir check ist disabled, take over the setting and
                    // output a warning.
                    $sMsg = i18n("The specified directory '%s' does not exists in DOCUMENT_ROOT '%s'. This could happen, if clients DOCUMENT_ROOT differs from CONTENIDO backends DOCUMENT_ROOT. However, the setting will be taken over because of disabled check.", "cl-mod-rewrite");
                    $sMsg = sprintf($sMsg, $request['rootdir'], $_SERVER['DOCUMENT_ROOT']);
                    $this->_oView->rootdir_error = $this->_notifyBox('warning', $sMsg);
                }
            }
            $this->_oView->rootdir = conHtmlentities($request['rootdir']);
            $aMR['cl-mod-rewrite']['rootdir'] = $request['rootdir'];
        }

        // root dir check
        if (mr_arrayValue($request, 'checkrootdir') == 1) {
            $this->_oView->checkrootdir_chk = ' checked="checked"';
            $aMR['cl-mod-rewrite']['checkrootdir'] = 1;
        } else {
            $this->_oView->checkrootdir_chk = '';
            $aMR['cl-mod-rewrite']['checkrootdir'] = 0;
        }

        // start from root
        if (mr_arrayValue($request, 'startfromroot') == 1) {
            $this->_oView->startfromroot_chk = ' checked="checked"';
            $aMR['cl-mod-rewrite']['startfromroot'] = 1;
        } else {
            $this->_oView->startfromroot_chk = '';
            $aMR['cl-mod-rewrite']['startfromroot'] = 0;
        }

        // prevent duplicated content
        if (mr_arrayValue($request, 'prevent_duplicated_content') == 1) {
            $this->_oView->prevent_duplicated_content_chk = ' checked="checked"';
            $aMR['cl-mod-rewrite']['prevent_duplicated_content'] = 1;
        } else {
            $this->_oView->prevent_duplicated_content_chk = '';
            $aMR['cl-mod-rewrite']['prevent_duplicated_content'] = 0;
        }

        // language settings
        if (mr_arrayValue($request, 'use_language') == 1) {
            $this->_oView->use_language_chk = ' checked="checked"';
            $this->_oView->use_language_name_disabled = '';
            $aMR['cl-mod-rewrite']['use_language'] = 1;
            if (mr_arrayValue($request, 'use_language_name') == 1) {
                $this->_oView->use_language_name_chk = ' checked="checked"';
                $aMR['cl-mod-rewrite']['use_language_name'] = 1;
            } else {
                $this->_oView->use_language_name_chk = '';
                $aMR['cl-mod-rewrite']['use_language_name'] = 0;
            }
        } else {
            $this->_oView->use_language_chk = '';
            $this->_oView->use_language_name_chk = '';
            $this->_oView->use_language_name_disabled = ' disabled="disabled"';
            $aMR['cl-mod-rewrite']['use_language'] = 0;
            $aMR['cl-mod-rewrite']['use_language_name'] = 0;
        }

        // client settings
        if (mr_arrayValue($request, 'use_client') == 1) {
            $this->_oView->use_client_chk = ' checked="checked"';
            $this->_oView->use_client_name_disabled = '';
            $aMR['cl-mod-rewrite']['use_client'] = 1;
            if (mr_arrayValue($request, 'use_client_name') == 1) {
                $this->_oView->use_client_name_chk = ' checked="checked"';
                $aMR['cl-mod-rewrite']['use_client_name'] = 1;
            } else {
                $this->_oView->use_client_name_chk = '';
                $aMR['cl-mod-rewrite']['use_client_name'] = 0;
            }
        } else {
            $this->_oView->use_client_chk = '';
            $this->_oView->use_client_name_chk = '';
            $this->_oView->use_client_name_disabled = ' disabled="disabled"';
            $aMR['cl-mod-rewrite']['use_client'] = 0;
            $aMR['cl-mod-rewrite']['use_client_name'] = 0;
        }

        // use lowercase uri
        if (mr_arrayValue($request, 'use_lowercase_uri') == 1) {
            $this->_oView->use_lowercase_uri_chk = ' checked="checked"';
            $aMR['cl-mod-rewrite']['use_lowercase_uri'] = 1;
        } else {
            $this->_oView->use_lowercase_uri_chk = '';
            $aMR['cl-mod-rewrite']['use_lowercase_uri'] = 0;
        }

        $this->_oView->category_separator_attrib = '';
        $this->_oView->category_word_separator_attrib = '';
        $this->_oView->article_separator_attrib = '';
        $this->_oView->article_word_separator_attrib = '';

        $separatorPattern = $aSeparator['pattern'];
        $separatorInfo = $aSeparator['info'];

        $wordSeparatorPattern = $aSeparator['pattern'];
        $wordSeparatorInfo = $aSeparator['info'];

        $categorySeperator = mr_arrayValue($request, 'category_seperator', '');
        $categoryWordSeperator = mr_arrayValue($request, 'category_word_seperator', '');
        $articleSeperator = mr_arrayValue($request, 'article_seperator', '');
        $articleWordSeperator = mr_arrayValue($request, 'article_word_seperator', '');

        // category seperator
        if ($categorySeperator == '') {
            $sMsg = i18n("Please specify separator (%s) for category", "cl-mod-rewrite");
            $sMsg = sprintf($sMsg, $separatorInfo);
            $this->_oView->category_separator_error = $this->_notifyBox('error', $sMsg);
            $bError = true;
        } elseif (!preg_match($separatorPattern, $categorySeperator)) {
            $sMsg = i18n("Invalid separator for category, allowed one of following characters: %s", "cl-mod-rewrite");
            $sMsg = sprintf($sMsg, $separatorInfo);
            $this->_oView->category_separator_error = $this->_notifyBox('error', $sMsg);
            $bError = true;

            // category word seperator
        } elseif ($categoryWordSeperator == '') {
            $sMsg = i18n("Please specify separator (%s) for category words", "cl-mod-rewrite");
            $sMsg = sprintf($sMsg, $wordSeparatorInfo);
            $this->_oView->category_word_separator_error = $this->_notifyBox('error', $sMsg);
            $bError = true;
        } elseif (!preg_match($wordSeparatorPattern, $categoryWordSeperator)) {
            $sMsg = i18n("Invalid separator for category words, allowed one of following characters: %s", "cl-mod-rewrite");
            $sMsg = sprintf($sMsg, $wordSeparatorInfo);
            $this->_oView->category_word_separator_error = $this->_notifyBox('error', $sMsg);
            $bError = true;

            // article seperator
        } elseif ($articleSeperator == '') {
            $sMsg = i18n("Please specify separator (%s) for article", "cl-mod-rewrite");
            $sMsg = sprintf($sMsg, $separatorInfo);
            $this->_oView->article_separator_error = $this->_notifyBox('error', $sMsg);
            $bError = true;
        } elseif (!preg_match($separatorPattern, $articleSeperator)) {
            $sMsg = i18n("Invalid separator for article, allowed is one of following characters: %s", "cl-mod-rewrite");
            $sMsg = sprintf($sMsg, $separatorInfo);
            $this->_oView->article_separator_error = $this->_notifyBox('error', $sMsg);
            $bError = true;

            // article word seperator
        } elseif ($articleWordSeperator == '') {
            $sMsg = i18n("Please specify separator (%s) for article words", "cl-mod-rewrite");
            $sMsg = sprintf($sMsg, $wordSeparatorInfo);
            $this->_oView->article_word_separator_error = $this->_notifyBox('error', $sMsg);
            $bError = true;
        } elseif (!preg_match($wordSeparatorPattern, $articleWordSeperator)) {
            $sMsg = i18n("Invalid separator for article words, allowed is one of following characters: %s", "cl-mod-rewrite");
            $sMsg = sprintf($sMsg, $wordSeparatorInfo);
            $this->_oView->article_word_separator_error = $this->_notifyBox('error', $sMsg);
            $bError = true;

            // category_seperator - category_word_seperator
        } elseif ($categorySeperator == $categoryWordSeperator) {
            $sMsg = i18n("Separator for category and category words must not be identical", "cl-mod-rewrite");
            $this->_oView->category_separator_error = $this->_notifyBox('error', $sMsg);
            $bError = true;
            // category_seperator - article_word_seperator
        } elseif ($categorySeperator == $articleWordSeperator) {
            $sMsg = i18n("Separator for category and article words must not be identical", "cl-mod-rewrite");
            $this->_oView->category_separator_error = $this->_notifyBox('error', $sMsg);
            $bError = true;
            // article_seperator - article_word_seperator
        } elseif ($articleSeperator == $articleWordSeperator) {
            $sMsg = i18n("Separator for category-article and article words must not be identical", "cl-mod-rewrite");
            $this->_oView->article_separator_error = $this->_notifyBox('error', $sMsg);
            $bError = true;
        }

        $this->_oView->category_separator = conHtmlentities($categorySeperator);
        $aMR['cl-mod-rewrite']['category_seperator'] = $categorySeperator;
        $this->_oView->category_word_separator = conHtmlentities($categoryWordSeperator);
        $aMR['cl-mod-rewrite']['category_word_seperator'] = $categoryWordSeperator;
        $this->_oView->article_separator = conHtmlentities($articleSeperator);
        $aMR['cl-mod-rewrite']['article_seperator'] = $articleSeperator;
        $this->_oView->article_word_separator = conHtmlentities($articleWordSeperator);
        $aMR['cl-mod-rewrite']['article_word_seperator'] = $articleWordSeperator;

        // file extension
        if (mr_arrayValue($request, 'file_extension', '') !== '') {
            if (!preg_match('/^\.([a-zA-Z0-9\-_\/])*$/', $request['file_extension'])) {
                $sMsg = i18n("The file extension has a invalid format, allowed are the chars \.([a-zA-Z0-9\-_\/])", "cl-mod-rewrite");
                $this->_oView->file_extension_error = $this->_notifyBox('error', $sMsg);
                $bError = true;
            }
            $this->_oView->file_extension = conHtmlentities($request['file_extension']);
            $aMR['cl-mod-rewrite']['file_extension'] = $request['file_extension'];
        } else {
            $this->_oView->file_extension = '.html';
            $aMR['cl-mod-rewrite']['file_extension'] = '.html';
        }

        // category resolve min percentage
        if (isset($request['category_resolve_min_percentage'])) {
            if (!is_numeric($request['category_resolve_min_percentage'])) {
                $sMsg = i18n("Value has to be numeric.", "cl-mod-rewrite");
                $this->_oView->category_resolve_min_percentage_error = $this->_notifyBox('error', $sMsg);
                $bError = true;
            } elseif ($request['category_resolve_min_percentage'] < 0 || $request['category_resolve_min_percentage'] > 100) {
                $sMsg = i18n("Value has to be between 0 an 100.", "cl-mod-rewrite");
                $this->_oView->category_resolve_min_percentage_error = $this->_notifyBox('error', $sMsg);
                $bError = true;
            }
            $this->_oView->category_resolve_min_percentage = $request['category_resolve_min_percentage'];
            $aMR['cl-mod-rewrite']['category_resolve_min_percentage'] = $request['category_resolve_min_percentage'];
        } else {
            $this->_oView->category_resolve_min_percentage = '75';
            $aMR['cl-mod-rewrite']['category_resolve_min_percentage'] = '75';
        }

        // add start article name to url
        if (mr_arrayValue($request, 'add_startart_name_to_url') == 1) {
            $this->_oView->add_startart_name_to_url_chk = ' checked="checked"';
            $aMR['cl-mod-rewrite']['add_startart_name_to_url'] = 1;
            if (mr_arrayValue($request, 'add_startart_name_to_url', '') !== '') {
                if (!preg_match('/^[a-zA-Z0-9\-_\/\.]*$/', $request['default_startart_name'])) {
                    $sMsg = i18n("The article name has a invalid format, allowed are the chars /^[a-zA-Z0-9\-_\/\.]*$/", "cl-mod-rewrite");
                    $this->_oView->add_startart_name_to_url_error = $this->_notifyBox('error', $sMsg);
                    $bError = true;
                }
                $this->_oView->default_startart_name = conHtmlentities($request['default_startart_name']);
                $aMR['cl-mod-rewrite']['default_startart_name'] = $request['default_startart_name'];
            } else {
                $this->_oView->default_startart_name = '';
                $aMR['cl-mod-rewrite']['default_startart_name'] = '';
            }
        } else {
            $this->_oView->add_startart_name_to_url_chk = '';
            $aMR['cl-mod-rewrite']['add_startart_name_to_url'] = 0;
            $this->_oView->default_startart_name = '';
            $aMR['cl-mod-rewrite']['default_startart_name'] = '';
        }

        // rewrite urls at
        if (mr_arrayValue($request, 'rewrite_urls_at') == 'congeneratecode') {
            $this->_oView->rewrite_urls_at_congeneratecode_chk = ' checked="checked"';
            $this->_oView->rewrite_urls_at_front_content_output_chk = '';
            $aMR['cl-mod-rewrite']['rewrite_urls_at_congeneratecode'] = 1;
            $aMR['cl-mod-rewrite']['rewrite_urls_at_front_content_output'] = 0;
        } else {
            $this->_oView->rewrite_urls_at_congeneratecode_chk = '';
            $this->_oView->rewrite_urls_at_front_content_output_chk = ' checked="checked"';
            $aMR['cl-mod-rewrite']['rewrite_urls_at_congeneratecode'] = 0;
            $aMR['cl-mod-rewrite']['rewrite_urls_at_front_content_output'] = 1;
        }

        // routing
        if (isset($request['rewrite_routing'])) {
            $aRouting = array();
            $items = explode("\n", $request['rewrite_routing']);
            foreach ($items as $p => $v) {
                $routingDef = explode($routingSeparator, $v);
                if (count($routingDef) !== 2) {
                    continue;
                }
                $routingDef[0] = trim($routingDef[0]);
                $routingDef[1] = trim($routingDef[1]);
                if ($routingDef[0] == '') {
                    continue;
                }
                $aRouting[$routingDef[0]] = $routingDef[1];
            }
            $this->_oView->rewrite_routing = conHtmlentities($request['rewrite_routing']);
            $aMR['cl-mod-rewrite']['routing'] = $aRouting;
        } else {
            $this->_oView->rewrite_routing = '';
            $aMR['cl-mod-rewrite']['routing'] = array();
        }

        // redirect invalid article to errorsite
        if (isset($request['redirect_invalid_article_to_errorsite'])) {
            $this->_oView->redirect_invalid_article_to_errorsite_chk = ' checked="checked"';
            $aMR['cl-mod-rewrite']['redirect_invalid_article_to_errorsite'] = 1;
        } else {
            $this->_oView->redirect_invalid_article_to_errorsite_chk = '';
            $aMR['cl-mod-rewrite']['redirect_invalid_article_to_errorsite'] = 0;
        }

        if ($bError) {
            $sMsg = i18n("Please check your input", "cl-mod-rewrite");
            $this->_oView->content_before .= $this->_notifyBox('error', $sMsg);
            return;
        }

        if ($bDebug == true) {
            echo $this->_notifyBox('info', 'Debug');
            echo '<pre class="example">';
            print_r($aMR['cl-mod-rewrite']);
            echo '</pre>';
            $sMsg = i18n("Configuration has <b>not</b> been saved, because of enabled debugging", "cl-mod-rewrite");
            echo $this->_notifyBox('info', $sMsg);
            return;
        }

        $bSeparatorModified = $this->_separatorModified($aMR['cl-mod-rewrite']);

        if (mr_setConfiguration($this->_client, $aMR)) {
            $sMsg = i18n("Configuration has been saved", "cl-mod-rewrite");
            if ($bSeparatorModified) {
                mr_loadConfiguration($this->_client, true);
            }
            $this->_oView->content_before .= $this->_notifyBox('info', $sMsg);
        } else {
            $sMsg = i18n("Configuration could not saved. Please check write permissions for %s ", "cl-mod-rewrite");
            $sMsg = sprintf($sMsg, $options['key']);
            $this->_oView->content_before .= $this->_notifyBox('error', $sMsg);
        }
    }

    /**
     * Checks, if any sseparators setting is modified or not
     * @param   array  $aNewCfg  New configuration send by requests.
     * @return  bool
     */
    protected function _separatorModified($aNewCfg) {
        $aCfg = ModRewrite::getConfig();

        if ($aCfg['category_seperator'] != $aNewCfg['category_seperator']) {
            return true;
        } elseif ($aCfg['category_word_seperator'] != $aNewCfg['category_word_seperator']) {
            return true;
        } elseif ($aCfg['article_seperator'] != $aNewCfg['article_seperator']) {
            return true;
        } elseif ($aCfg['article_word_seperator'] != $aNewCfg['article_word_seperator']) {
            return true;
        }
        return false;
    }

    /**
     * Does some checks like 'is_start_compatible' check.
     * Adds notifications, if something will went wrong...
     */
    protected function _doChecks() {
        // Check for not supported '$cfg["is_start_compatible"] = true;' mode
        if (!empty($this->_cfg['is_start_compatible']) && true === $this->_cfg['is_start_compatible']) {
            $sMsg = i18n("Your Contenido installation runs with the setting 'is_start_compatible'. This plugin will not work properly in this mode.<br />Please check following topic in Contenido forum to change this:<br /><br /><a href='http://forum.contenido.org/viewtopic.php?t=32530' class='blue' target='_blank'>is_start_compatible auf neue Version umstellen</a>", "cl-mod-rewrite");
            $this->_oView->content_before .= $this->_notifyBox('warning', $sMsg);
        }

        // Check for empty urlpath entries in cat_lang table
        $db = new DB_Contenido();
        $sql = "SELECT idcatlang FROM " . $this->_cfg['tab']['cat_lang'] . " WHERE urlpath = ''";
        if ($db->query($sql) && $db->next_record()) {
            $sMsg = i18n("It seems as if some categories don't have a set 'urlpath' entry in the database. Please reset empty aliases in %sFunctions%s area.", "cl-mod-rewrite");
            $sMsg = sprintf($sMsg, '<a href="main.php?area=mod_rewrite_expert&frame=4&contenido=' . $this->_oView->sessid . '&idclient=' . $this->_client . '" onclick="parent.right_top.sub.clicked(parent.right_top.document.getElementById(\'c_1\').firstChild);">', '</a>');
            $this->_oView->content_before .= $this->_notifyBox('warning', $sMsg);
        }

    }
}