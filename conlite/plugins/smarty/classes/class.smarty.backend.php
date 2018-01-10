<?php
/**
 * This file contains the backend class for smarty wrapper plugin.
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

/**
 * Wrapper class for Integration of smarty.
 *
 * @package Plugin
 * @subpackage SmartyWrapper
 */
class cSmartyBackend extends cSmartyFrontend {

    public function __construct(&$aCfg, &$aClientCfg, $bSanityCheck = false) {
        parent::__construct($aCfg, $aClientCfg, false);

        parent::$aDefaultPaths = array(
            'template_dir' => $aCfg['path']['contenido'] . 'plugins/smarty_templates/',
            'cache_dir' => $aCfg['path']['conlite_cache'],
            'compile_dir' => $aCfg['path']['conlite_cache'] . 'templates_c/'
        );

        parent::$bSmartyInstanciated = true;

        $this->resetPaths();
    }

}