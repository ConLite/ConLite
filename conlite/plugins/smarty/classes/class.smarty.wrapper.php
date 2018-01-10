<?php
/**
 * This file contains the wrapper class for smarty wrapper plugin.
 * 
 * @package Plugin
 * @subpackage SmartyWrapper
 * @version $Rev$
 * @since 2.0.2
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright (c) 2018, conlite.org
 * @license http://www.gnu.de/documents/gpl.en.html GPL v3 (english version)
 * @license http://www.gnu.de/documents/gpl.de.html GPL v3 (deutsche Version)
 * @link http://www.conlite.org ConLite.org
 * 
 * $Id$
 */

/**
 * @package Plugin
 * @subpackage SmartyWrapper
 * @author Andreas Dieter
 * @copyright four for business AG <www.4fb.de>
 */
defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

class cSmartyWrapper extends Smarty {

    public function fetch($template = NULL, $cache_id = NULL, $compile_id = NULL, $parent = NULL, $display = false, $merge_tpl_vars = true, $no_output_filter = false) {
        /* @todo implement functionality for CL 2.0
        if ($this->templateExists($template) === false) {
            $moduleId = (int) cRegistry::getCurrentModuleId();
            if ($moduleId > 0) {
                $module = new cModuleHandler($moduleId);
                $template = $module->getTemplatePath($template);
            }
        }
        */
        return parent::fetch($template, $cache_id, $compile_id, $parent, $display, $merge_tpl_vars, $no_output_filter);
    }

    public function fetchGeneral($template = NULL, $cache_id = NULL, $compile_id = NULL, $parent = NULL, $display = false, $merge_tpl_vars = true, $no_output_filter = false) {
        $template = cRegistry::getFrontendPath() . 'templates/' . $template;

        return parent::fetch($template, $cache_id, $compile_id, $parent, $display, $merge_tpl_vars, $no_output_filter);
    }

    public function display($template = NULL, $cache_id = NULL, $compile_id = NULL, $parent = NULL) {
        global $frontend_debug;

        if ($frontend_debug['template_display']) {
            echo("<!-- SMARTY TEMPLATE " . $template . " -->");
        }

        return parent::display($template, $cache_id, $compile_id, $parent);
    }

    public function displayGeneral($template = NULL, $cache_id = NULL, $compile_id = NULL, $parent = NULL) {
        $this->fetchGeneral($template, $cache_id, $compile_id, $parent, true);
    }

    public function clearCache($template_name, $cache_id = null, $compile_id = null, $exp_time = null, $type = null) {
        /* @todo implement functionality for CL 2.0
        if ($this->templateExists($template_name) === false) {
            $moduleId = (int) cRegistry::getCurrentModuleId();
            if ($moduleId > 0) {
                $module = new cModuleHandler($moduleId);
                $template_name = $module->getTemplatePath($template_name);
            }
        }
        */
        return parent::clearCache($template_name, $cache_id, $compile_id, $exp_time, $type);
    }
}