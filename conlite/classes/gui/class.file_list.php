<?php

/**
 * 
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

class cGuiFileList {

    protected $_sPath;
    protected $_mFileExt;
    protected $_iScanDepth;
    protected $_aDirItems;
    protected $_bWritable;
    protected $_oTpl;

    public function __construct($sPath, $mFileExt = null) {

        if (!empty($mFileExt) && is_string($mFileExt)) {
            $mFileExt = [strtolower($mFileExt)];
        }
        $this->_sPath = $sPath;
        $this->_mFileExt = $mFileExt;
        $this->_iScanDepth = 3;
        $this->_oTpl = new Template();
    }

    public function scanDir() {
        if (empty($this->_sPath) || !is_readable($this->_sPath)) {
            return false;
        }
        $this->_bWritable = (!is_writable($this->_sPath)) ? true : false;

        $this->_aDirItems = $this->_assetsMap($this->_sPath, $this->_iScanDepth);
        asort($this->_aDirItems, SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL);
    }

    public function renderList($sTpl = null, $bReturn = false) {
        global $sess, $area;
        $sList = '<ul id="treeData" style="display: none;">' . "\n";
        foreach ($this->_aDirItems as $key => $item) {
            if (is_array($item)) {
                $sList .= $this->_getSubItems($key, $item, $this->_sPath);
            } else {
                $sAddClass = (is_writable($this->_sPath . $item)) ? '' : ' notwritable';
                $sList .= '<li class="file' . $sAddClass . '" data-filepath="' . $item . '">' . $item . '</li>' . "\n";
            }
        }

        $sList .= '</ul>' . "\n";
        $this->_oTpl->set('s', 'item_list', $sList);

        $this->_oTpl->set('s', 'multilink1', $sess->url("main.php?area=$area&frame=3&file=\${file}"));
        $this->_oTpl->set('s', 'multilink2', $sess->url("main.php?area=$area&frame=4&action=js_edit&file=\${file}&tmp_file=\${file}"));

        $this->_oTpl->generate(cRegistry::getConfigValue('path', 'contenido') . cRegistry::getConfigValue('path', 'templates') . "html5/file_list.html", $bReturn);
    }

    protected function _getSubItems($sName, $aItems, $sPathToItem) {
        $sPathToItem = $sPathToItem . $sName . DIRECTORY_SEPARATOR;
        $sItemListEntry = '<li class="folder directory">' . $sName . "\n\t";
        if (is_array($aItems) && count($aItems) > 0) {
            $sItemListEntry .= '<ul data-filepath="' . $sPathToItem . '">' . "\n\t";
            foreach ($aItems as $key => $item) {
                if (is_array($item)) {
                    $sItemListEntry .= $this->_getSubItems($key, $item, $sPathToItem);
                } else {
                    $sAddClass = (is_writable($sPathToItem . $item)) ? '' : ' notwritable';
                    $sItemListEntry .= '<li class="file' . $sAddClass . '" data-filepath="' . str_replace($this->_sPath, '', $sPathToItem . $item) . '">' . $item . '</li>' . "\n";
                }
            }
            $sItemListEntry .= '</ul>' . "\n";
        }
        $sItemListEntry .= '</li>' . "\n";
        return $sItemListEntry;
    }

    protected function _assetsMap($source_dir, $directory_depth = 0, $hidden = false) {
        if ($fp = @opendir($source_dir)) {
            $filedata = array();
            $new_depth = $directory_depth - 1;
            $source_dir = rtrim($source_dir, '/') . '/';

            while (FALSE !== ($file = readdir($fp))) {
                // Remove '.', '..', and hidden files [optional]
                if (!trim($file, '.') OR ($hidden == false && $file[0] == '.')) {
                    continue;
                }


                if (($directory_depth < 1 OR $new_depth > 0) && is_dir($source_dir . $file)) {
                    $aTmp = $this->_assetsMap($source_dir . $file . '/', $new_depth, $hidden);
                    if (!empty($aTmp)) {
                        asort($aTmp, SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL);
                        $filedata[$file] = $aTmp;
                    }
                    unset($aTmp);
                } else {
                    $sFileExt = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    if (!empty($this->_mFileExt) && in_array($sFileExt, $this->_mFileExt)) {
                        $filedata[] = $file;
                    }
                }
            }

            closedir($fp);
            return $filedata;
        }
        echo 'can not open dir';
        return FALSE;
    }

}
