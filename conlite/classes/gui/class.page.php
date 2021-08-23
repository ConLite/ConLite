<?php

defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

class cGuiPage {
    protected $_pageName;
    protected $_pluginName;
    protected $_pageTemplate;
    protected $_pageBase;
    protected $_contentTemplate;
    protected $_scripts;
    protected $_uniqueScripts;
    protected $_styles;
    protected $_subnav;
    protected $_markScript;
    protected $_error;
    protected $_warning;
    protected $_info;
    protected $_ok;
    protected $_abort;
    protected $_objects;
    protected $_metaTags;
    protected $_bodyClassNames;
    protected $_filesDirectory;
    protected $_skipTemplateCheck = false;
    
    protected $_bHTML5 = false;


    public function __construct($pageName, $pluginName = '', $subMenu = '') {
        $this->_pageName = $pageName;
        $this->_pluginName = $pluginName;
        $this->_pageTemplate = new cTemplate();
        $this->_contentTemplate = new cTemplate();
        $this->_scripts = [];
        $this->_uniqueScripts = [];
        $this->_styles = [];
        $this->_subnav = '';
        $this->_markScript = '';
        $this->_error = '';
        $this->_warning = '';
        $this->_info = '';
        $this->_abort = false;
        $this->_objects = [];
        $this->_metaTags = [];
        $this->_bodyClassNames = [];

        $lang = cRegistry::getLanguageId();
        $cfg = cRegistry::getConfig();

        // Try to extract the current CONTENIDO language
        $clang = new cApiLanguage($lang);

        if ($clang->isLoaded()) {
            $this->setEncoding($clang->get('encoding'));
        }

        // use default page base
        $this->setPageBase();

        $this->_pageTemplate->set('s', 'SUBMENU', $subMenu);
        $this->_pageTemplate->set('s', 'PAGENAME', $pageName);
        $pageid = str_replace('.', '_', $pageName);
        $this->_pageTemplate->set('s', 'PAGENAME', $pageName);
        $this->_pageTemplate->set('s', 'PAGEID', $pageid);

        $this->addBodyClassName('page_generic');
        $this->addBodyClassName('page_' . $pageid);

        if ($pluginName != '') {
            $this->_filesDirectory = '';
            $scriptDir = cRegistry::getBackendPath() . $cfg['path']['plugins'] . $pluginName . '/' . $cfg['path']['scripts'];
            $styleDir = cRegistry::getBackendPath() . $cfg['path']['plugins'] . $pluginName . '/' . $cfg['path']['styles'];
        } else {
            $this->_filesDirectory = 'includes/';
            $scriptDir = $cfg['path']['scripts_includes'];
            $styleDir = $cfg['path']['styles_includes'];
        }

        if (cFileHandler::exists($styleDir . $pageName . '.css')) {
            $this->addStyle($this->_filesDirectory . $pageName . '.css');
        }

        /* @var $stylefile SplFileInfo */
        if (cFileHandler::exists($styleDir)) {
            foreach (new DirectoryIterator($styleDir) as $stylefile) {
                if (cString::endsWith($stylefile->getFilename(), '.' . $pageName . '.css')) {
                    $this->addStyle($this->_filesDirectory . $stylefile->getFilename());
                }
            }
        }

        if (cFileHandler::exists($scriptDir . $pageName . '.js')) {
            $this->addScript($this->_filesDirectory . $pageName . '.js');
        }

        /* @var $scriptfile SplFileInfo */
        if (cFileHandler::exists($scriptDir)) {
            foreach (new DirectoryIterator($scriptDir) as $scriptfile) {
                if (cString::endsWith($scriptfile->getFilename(), '.' . $pageName . '.js')) {
                    $this->addScript($this->_filesDirectory . $scriptfile->getFilename());
                }
            }
        }
    }

    public function addScript($script) {
        global $currentuser;

        $script = trim($script);
        if (empty($script)) {
            return;
        }

        $perm = cRegistry::getPerm();
        $cfg = cRegistry::getConfig();
        $backendUrl = cRegistry::getBackendUrl();
        $backendPath = cRegistry::getBackendPath();
        $filePathName = $this->_getRealFilePathName($script);

        // Warning message for not existing resources
        if ($perm->isSysadmin($currentuser) && cString::findFirstPos(trim($script), '<script') === false &&
           ((!empty($this->_pluginName) && !cFileHandler::exists($backendPath . $cfg['path']['plugins'] . $this->_pluginName . '/' . $cfg['path']['scripts'] . $script)) &&
           (!cFileHandler::exists($backendPath . $cfg['path']['scripts'] . $filePathName)))) {
            $this->displayWarning(i18n("The requested resource") . " <strong>" . $filePathName . "</strong> " . i18n("was not found"));
        }

        if (cString::findFirstPos(trim($script), 'http') === 0 || cString::findFirstPos(trim($script), '<script') === 0 || cString::findFirstPos(trim($script), '//') === 0) {
            // the given script path is absolute
            if (!in_array($script, $this->_scripts)) {
                $this->_scripts[] = $script;
            }
        } else if (!empty($this->_pluginName) && cFileHandler::exists($backendPath . $cfg['path']['plugins'] . $this->_pluginName . '/' . $cfg['path']['scripts'] . $filePathName)) {
            // the given script path is relative to the plugin scripts folder
            $fullPath = $backendUrl . $cfg['path']['plugins'] . $this->_pluginName . '/' . $cfg['path']['scripts'] . $script;
            if (!in_array($fullPath, $this->_scripts)) {
                $this->_scripts[] = $fullPath;
            }
        } else if (cFileHandler::exists($backendPath . $cfg['path']['scripts'] . $filePathName)) {
            // the given script path is relative to the CONTENIDO scripts folder
            $fullPath = $backendUrl . $cfg['path']['scripts'] . $script;

            if (!in_array($fullPath, $this->_scripts)) {
                $this->_scripts[] = $fullPath;
            }
        }
    }
    
    public function addStyle($stylesheet) {
        global $currentuser;

        $stylesheet = trim($stylesheet);
        if (empty($stylesheet)) {
            return;
        }

        $perm = cRegistry::getPerm();
        $cfg = cRegistry::getConfig();
        $backendUrl = cRegistry::getBackendUrl();
        $backendPath = cRegistry::getBackendPath();
        $filePathName = $this->_getRealFilePathName($stylesheet);

        // Warning message for not existing resources
        if ($perm->isSysadmin($currentuser) && ((!empty($this->_pluginName) && !cFileHandler::exists($backendPath . $cfg['path']['plugins'] . $this->_pluginName . '/' . $cfg['path']['styles'] . $stylesheet))) ||
           (empty($this->_pluginName) && !cFileHandler::exists($backendPath . $cfg['path']['styles'] . $filePathName))) {
            $this->displayWarning(i18n("The requested resource") . " <strong>" . $filePathName . "</strong> " . i18n("was not found"));
        }

        if (cString::findFirstPos($stylesheet, 'http') === 0 || cString::findFirstPos($stylesheet, '//') === 0) {
            // the given stylesheet path is absolute
            if (!in_array($stylesheet, $this->_styles)) {
                $this->_styles[] = $stylesheet;
            }
        } else if (!empty($this->_pluginName) && cFileHandler::exists($backendPath . $cfg['path']['plugins'] . $this->_pluginName . '/' . $cfg['path']['styles'] . $filePathName)) {
            // the given stylesheet path is relative to the plugin stylesheets
            // folder
            $fullPath = $backendUrl . $cfg['path']['plugins'] . $this->_pluginName . '/' . $cfg['path']['styles'] . $stylesheet;
            if (!in_array($fullPath, $this->_styles)) {
                $this->_styles[] = $fullPath;
            }
        } else if (cFileHandler::exists($backendPath . $cfg['path']['styles'] . $filePathName)) {
            // the given stylesheet path is relative to the CONTENIDO
            // stylesheets folder
            $fullPath = $backendUrl . $cfg['path']['styles'] . $stylesheet;
            if (!in_array($fullPath, $this->_styles)) {
                $this->_styles[] = $fullPath;
            }
        }
    }
    
    public function addMeta(array $meta) {
        $allowedAttributes = [
            'charset',
            'content',
            'http-equiv',
            'name',
            'itemprop'
        ];
        foreach ($meta as $key => $value) {
            if (!in_array($key, $allowedAttributes)) {
                throw new cInvalidArgumentException('Unallowed attribute for meta tag given - meta tag will be ignored!');
            }
        }
        $this->_metaTags[] = $meta;
    }
    
    public function addBodyClassName($className) {
        if (!in_array($className, $this->_bodyClassNames)) {
            $this->_bodyClassNames[] = $className;
        }
    }
    
    public function setSubnav($additional = '', $aarea = '') {
        $area = cRegistry::getArea();
        $sess = cRegistry::getSession();

        if ($aarea == '') {
            $aarea = $area;
        }

        $this->_subnav = '
        <script type="text/javascript">
        Con.getFrame("right_top").location.href = "' . $sess->url("main.php?area={$aarea}&frame=3&{$additional}") . '";
        </script>
        ';
    }
    
    public function setReload(array $parameters = []) {
        $reloadParameters = count($parameters) > 0 ? json_encode($parameters) : '';
        $this->_uniqueScripts['left_bottom'] = '
            <script type="text/javascript">
                (function(Con, $) {
                    Con.FrameLeftBottom.reload(' . $reloadParameters . ');
                })(Con, Con.$);
            </script>
        ';
    }

    public function reloadFrame($frameName, $updatedParameters = null) {
        if (is_array($updatedParameters)) {
            $reloadParameters = count($updatedParameters) > 0 ? json_encode($updatedParameters) : '{}';
            $this->_uniqueScripts[$frameName] = '
                <script type="text/javascript">
                    (function(Con, $) {
                        var frame = Con.getFrame("' . $frameName . '");
                        if (frame) {
                            frame.location.href = Con.UtilUrl.replaceParams(frame.location.href, ' . $reloadParameters . ');
                        }
                    })(Con, Con.$);
                </script>
            ';
        } else {
            $this->_uniqueScripts[$frameName] = '
                <script type="text/javascript">
                    (function(Con, $) {
                        var frame = Con.getFrame("' . $frameName . '");
                        if (frame) {
                            frame.location.href = "' . $updatedParameters .'";
                        }
                    })(Con, Con.$);
                </script>
            ';
        }
    }

    public function reloadLeftTopFrame($updatedParameters = null) {
        if (is_array($updatedParameters) && !isset($updatedParameters['frame'])) {
            $updatedParameters['frame'] = 1;
        }
        $this->reloadFrame('left_top', $updatedParameters);
    }

    public function reloadLeftBottomFrame($updatedParameters = null) {
        if (is_array($updatedParameters) && !isset($updatedParameters['frame'])) {
            $updatedParameters['frame'] = 2;
        }
        $this->reloadFrame('left_bottom', $updatedParameters);
    }

    public function reloadRightTopFrame($updatedParameters = null) {
        if (is_array($updatedParameters) && !isset($updatedParameters['frame'])) {
            $updatedParameters['frame'] = 3;
        }
        $this->reloadFrame('right_top', $updatedParameters);
    }

    public function reloadRightBottomFrame($updatedParameters = null) {
        if (is_array($updatedParameters) && !isset($updatedParameters['frame'])) {
            $updatedParameters['frame'] = 4;
        }
        $this->reloadFrame('right_bottom', $updatedParameters);
    }

    public function setMarkScript($item) {
        $this->_markScript = markSubMenuItem($item, true);
    }

    public function setEncoding($encoding) {
        if (empty($encoding)) {
            return;
        }
        $this->_metaTags[] = [
            'http-equiv' => 'Content-type',
            'content' => 'text/html;charset=' . $encoding
        ];
    }
    
    public function set($type, $key, $value) {
        $this->_contentTemplate->set($type, $key, $value);
    }
    
    public function setPageBase($filename = '') {
        $sFolder = ($this->_bHTML5)?'html5/':'';
        if ('' === $filename) {
            $cfg = cRegistry::getConfig();
            $this->_pageBase = $cfg['path']['templates']. $sFolder . $cfg['templates']['generic_page'];
        } else {
            $this->_pageBase = $sFolder.$filename;
        }
    }
    
    public function next() {
        $this->_contentTemplate->next();
    }
    
    public function abortRendering() {
        $this->_abort = true;
    }

    public function displayCriticalError($msg) {
        $this->_error = $msg;
        $this->_abort = true;
    }

    public function displayError($msg) {
        $this->_error .= $msg . '<br>';
    }

    public function displayWarning($msg) {
        $this->_warning .= $msg . '<br>';
    }

    public function displayInfo($msg) {
        $this->_info .= $msg . '<br>';
    }

    public function displayOk($msg) {
        $this->_ok .= $msg . '<br>';
    }

    public function setContent($objects) {
        if (!is_array($objects)) {
            $objects = [
                $objects
            ];
        }
        $this->_objects = $objects;
    }

    public function appendContent($objects) {
        if (!is_array($objects)) {
            $this->_objects[] = $objects;
        } else {
            $this->_objects = array_merge($this->_objects, $objects);
        }
    }

    public function render($template = NULL, $return = false) {

        if ($template == NULL) {
            $template = $this->_contentTemplate;
        }

        // Render some parts like meta tags, scripts, styles, etc...
        $this->_renderMetaTags();
        $this->_renderScripts();
        $this->_renderStyles();

        // Set body class attribute values
        $this->_pageTemplate->set('s', 'PAGECLASS', implode(' ', $this->_bodyClassNames));

        // Get all messages for the content
        $text = $this->_renderContentMessages();
        if (cString::getStringLength(trim($text)) > 0) {
            $this->_skipTemplateCheck = true;
        }

        if (!$this->_abort) {
            if (count($this->_objects) == 0) {
                $output = $this->_renderTemplate($template);
            } else {
                $output = $this->_renderObjects();
            }
            $this->_pageTemplate->set('s', 'CONTENT', $text . $output);
        } else {
            $this->_pageTemplate->set('s', 'CONTENT', $text);
        }
        
        return $this->_pageTemplate->generate($this->_pageBase, $return);
    }

    protected function _renderMetaTags() {
        // render the meta tags
        // NB! We don't produce xhtml in the backend
        // $produceXhtml = getEffectiveSetting('generator', 'xhtml', 'false');
        $produceXhtml = false;
        $meta = '';
        foreach ($this->_metaTags as $metaTag) {
            $tag = '<meta';
            foreach ($metaTag as $key => $value) {
                $tag .= ' ' . $key . '="' . $value . '"';
            }
            if ($produceXhtml) {
                $tag .= ' /';
            }
            $tag .= ">\n";
            $meta .= $tag;
        }
        if (!empty($meta)) {
            $this->_pageTemplate->set('s', 'META', $meta);
        } else {
            $this->_pageTemplate->set('s', 'META', '');
        }
    }

    protected function _renderScripts() {
        $scripts = $this->_subnav . "\n" . $this->_markScript . "\n";
        $scripts .= implode("\n", $this->_uniqueScripts);
        foreach ($this->_scripts as $script) {
            if (cString::findFirstPos($script, 'http') === 0 || cString::findFirstPos($script, '//') === 0) {
                $scripts .= '<script type="text/javascript" src="' . $script . '"></script>' . "\n";
            } else if (cString::findFirstPos($script, '<script') === false) {
                $scripts .= '<script type="text/javascript" src="scripts/' . $script . '"></script>' . "\n";
            } else {
                $scripts .= $script;
            }
        }
        $this->_pageTemplate->set('s', 'SCRIPTS', $scripts);
    }

    protected function _renderStyles() {
        $styles = '';
        foreach ($this->_styles as $style) {
            if (cString::findFirstPos($style, 'http') === 0 || cString::findFirstPos($style, '//') === 0) {
                $styles .= '<link href="' . $style . '" type="text/css" rel="stylesheet">' . "\n";
            } else {
                $styles .= '<link href="styles/' . $style . '" type="text/css" rel="stylesheet">' . "\n";
            }
        }
        $this->_pageTemplate->set('s', 'STYLES', $styles);
    }

    protected function _renderContentMessages() {
        global $notification;

        // Get messages from cRegistry
        $okMessages = cRegistry::getOkMessages();
        foreach ($okMessages as $message) {
            $this->displayOk($message);
        }

        $infoMessages = cRegistry::getInfoMessages();
        foreach ($infoMessages as $message) {
            $this->displayInfo($message);
        }

        $errorMessages = cRegistry::getErrorMessages();
        foreach ($errorMessages as $message) {
            $this->displayError($message);
        }

        $warningMessages = cRegistry::getWarningMessages();
        foreach ($warningMessages as $message) {
            $this->displayWarning($message);
        }

        $text = '';
        if ($this->_ok != '') {
            $text .= $notification->returnNotification('ok', $this->_ok) . '<br>';
        }
        if ($this->_info != '') {
            $text .= $notification->returnNotification('info', $this->_info) . '<br>';
        }
        if ($this->_warning != '') {
            $text .= $notification->returnNotification('warning', $this->_warning) . '<br>';
        }
        if ($this->_error != '') {
            $text .= $notification->returnNotification('error', $this->_error) . '<br>';
        }

        return $text;
    }

    protected function _renderObjects() {
        $output = '';

        foreach ($this->_objects as $obj) {
            if (is_string($obj)) {
                $output .= $obj;
            }

            if (!method_exists($obj, 'render')) {
                continue;
            }

            // Ridiculous workaround because some objects return
            // code if the parameter is true and some return the
            // code if the parameter is false.
            $oldOutput = $output;

            // We don't want any code outside the body (in case the
            // object outputs directly we will catch this output).
            ob_start();
            $output .= $obj->render(false);

            // We get the code either directly or via the output
            $output .= ob_get_contents();
            if ($oldOutput == $output) {
                cWarning(__FILE__, __LINE__, "Rendering this object (" . print_r($obj, true) . ") doesn't seem to have any effect.");
            }
            ob_end_clean();
        }

        return $output;
    }

    protected function _renderTemplate($template) {
        global $currentuser, $notification;

        $perm = cRegistry::getPerm();
        $cfg = cRegistry::getConfig();

        if ($this->_pluginName == '') {
            $sHtml5 = ($this->_bHTML5)?'html5/':'';
            $file = $cfg['path']['templates'] . $sHtml5 . 'template.' . $this->_pageName . '.html';
        } else {
            $file = $cfg['path']['plugins'] . $this->_pluginName . '/templates/template.' . $this->_pageName . '.html';
        }

        $output = '';
        // Warning message for not existing resources
        if (!$this->_skipTemplateCheck && $perm->isSysadmin($currentuser) && !cFileHandler::exists($file)) {
            $output .= $notification->returnNotification('warning', i18n("The requested resource") . " <strong>template." . $this->_pageName . ".html</strong> " . i18n("was not found")) . '<br>';
        }

        if (cFileHandler::exists($file)) {
            $output .= $template->generate($file, true);
        } else {
            $output .= '';
        }

        return $output;
    }

    protected function _getRealFilePathName($file) {
        $tmp = explode('?', $file);
        return $tmp[0];
    }
    
    function __destruct() {
        $this->_bHTML5 = false;
    }
}
