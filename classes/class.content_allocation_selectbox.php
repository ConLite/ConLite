<?php

/**
 * Project: 
 * Contenido Content Management System
 * 
 * @package    Contenido Backend plugins
 * @version    0.2.1
 * @author     Marco Jahn
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.6
 * 
 * $Id: class.content_allocation_selectbox.php 128 2019-07-03 11:58:28Z oldperl $:
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

class pApiContentAllocationSelectBox extends pApiTree {

    var $idSetter = true;
    var $load = array();

    public function __construct($uuid) {
        global $cfg;

        parent::__construct($uuid);
    }

    public function _buildRenderTree($tree) {
        global $action, $frame, $area, $sess, $idart;

        $oldIdSetter = $this->idSetter;
        $this->idSetter = false;

        $result = '';

        $levelElms = sizeof($tree);
        $cnt = 1;
        foreach ($tree as $item_tmp) {
            $item = '';

            $spacer = '|-';
            $spacer = str_pad($spacer, (($item_tmp['level'] + 1) * 2), "--", STR_PAD_RIGHT);

            $result .= '<option value="' . $item_tmp['idpica_alloc'] . '_' . $item_tmp['level'] . '">' . $spacer . $item_tmp['name'] . '</option>';

            if ($item_tmp['children']) {
                $children = $this->_buildRenderTree($item_tmp['children']);
                $result .= $children;
            }
        }

        return $result;
    }

    public function setChecked($load) {
        return false;
    }

    /**
     * 
     * @modified 27.10.2005 $bUseTreeStatus = false (content allocation tree in selectbox is always expanded)
     */
    public function renderTree($return = true, $parentId = false, $bUseTreeStatus = false) {

        $tree = $this->fetchTree($parentId, 0, $bUseTreeStatus);

        if ($tree === false) {
            return false;
        }

        $tree = $this->_buildRenderTree($tree);

        if ($return === true) {
            return $tree;
        } else {
            echo $tree;
        }
    }

}

?>