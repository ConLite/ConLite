<?php

/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * Page widgets
 * 
 * Requirements: 
 * @con_php_req 5.0
 * 
 *
 * @package    Contenido Backend classes
 * @version    1.24
 * @author     Bjoern Behrens
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * 
 * {@internal 
 *   created 2007-01-30
 *   
 *   $Id$
 * }}
 * 
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

/**
 * Regular page
 *
 * @author      Timo A. Hummel <timo.hummel@4fb.de>
 */
class cPage extends cHTML {

    /**
     * Storage of scripts to be used on the page
     * @var array
     * @access private
     */
    var $_scripts;

    /**
     * Storage of the page's content
     * @var string
     * @access private
     */
    var $_content;

    /**
     * Storage of the margin
     * @var int
     * @access private
     */
    var $_margin;

    /**
     * Storage of the desired encoding
     * @var string
     * @access private
     */
    var $_encoding;

    /**
     * Storage of the sub navigation
     * @var string
     * @access private
     */
    var $_subnav;

    /**
     * Storage of the extra data (see template)
     * @var string
     * @access private
     */
    var $extra;

    /**
     * Switch if HTML5
     * @var boolean 
     */
    protected $_isHtml5 = false;

    /**
     * CSS files to add
     * @var array 
     */
    protected $_css;

    /**
     * JS files to add
     * @var array 
     */
    protected $_js;

    /**
     * Constructor
     * 
     * @global obj $auth
     * @global string $lang
     * @param obj $object
     */
    public function __construct($object = false) {
        global $auth, $lang;

        $this->_margin = 10;
        $this->_object = $object;

        /* Check for global register parameters */
        if (array_key_exists("u_register", $_GET)) {
            $user = new cApiUser($auth->auth["uid"]);
            if (is_array($_GET["u_register"])) {
                foreach ($_GET["u_register"] as $type => $values) {
                    foreach ($values as $name => $value) {
                        $user->setProperty($type, $name, $value);
                    }
                }
            }
        }
        /* Try to extract the current conlite language */
        $clang = new cApiLanguage($lang);
        if (!$clang->virgin) {
            $this->setEncoding($clang->get("encoding"));
        }
    }

    /**
     * use HTML5 for page output
     */
    public function setHtml5() {
        $this->_isHtml5 = true;
    }

    /**
     * set the margin width (pixels)
     *
     * @param $margin int Margin width
     */
    function setMargin($margin) {
        $this->_margin = $margin;
    }

    /**
     * 
     * @param string $sFile path to file
     */
    public function addCssFile($sFile) {
        if (!is_array($this->_css)) {
            $this->_css = array();
        }
        $this->_css[] = $sFile;
    }

    public function addJsFile($sFile) {
        if (!is_array($this->_css)) {
            $this->_js = array();
        }
        $this->_js[] = $sFile;
    }

    /**
     * sets a specific JavaScript for the header
     * Important: The passed script needs to define <script></script> tags.
     *
     * @param $name string Script identifier
     * @param $script string Script code
     */
    public function addScript($name, $script) {
        $this->_scripts[$name] = $script;
    }

    /**
     * sets the link to the subnavigation. Should be set on the first page only.
     *
     * @param $append URL to append
     */
    function setSubnav($append, $marea = false) {
        if ($marea === false) {
            global $area;
            $marea = $area;
        }
        $this->_subnavArea = $marea;
        $this->_subnav = $append;
    }

    /**
     * adds the default script to reload the left pane (frame 2)
     *
     * @param none
     */
    function setReload($location = false) {
        if ($location != false) {
            $this->_scripts["__reload"] = '<script type="text/javascript">' .
                    "if (parent.parent.frames['left'].frames['left_bottom'].get_registered_parameters) {" .
                    "parent.parent.frames['left'].frames['left_bottom'].location.href = '$location' + parent.parent.frames['left'].frames['left_bottom'].get_registered_parameters();" .
                    "} else {" .
                    "parent.parent.frames['left'].frames['left_bottom'].location.href = '$location';" .
                    "}"
                    . "</script>";
        } else {
            $this->_scripts["__reload"] = '<script type="text/javascript">' .
                    "if (parent.parent.frames['left'].frames['left_bottom'].get_registered_parameters) {" .
                    "parent.parent.frames['left'].frames['left_bottom'].location.href = parent.parent.frames['left'].frames['left_bottom'].location.href + parent.parent.frames['left'].frames['left_bottom'].get_registered_parameters();" .
                    "} else {" .
                    "parent.parent.frames['left'].frames['left_bottom'].location.href = parent.parent.frames['left'].frames['left_bottom'].location.href;}" .
                    "</script>";
        }
    }

    /**
     * Sets the content for the page
     *
     * @param $content mixed Object with a render method or a string containing the content
     */
    function setContent($content) {
        /* Is it an array? */
        if (is_array($content)) {
            foreach ($content as $item) {
                if (is_object($item)) {
                    if (method_exists($item, "render")) {
                        $this->_content .= $item->render();
                    }
                } else {
                    $this->_content .= $item;
                }
            }
        } else {
            if (is_object($content)) {
                if (method_exists($content, "render")) {
                    $this->_content = $content->render();
                    return;
                }
            } else {
                $this->_content = $content;
            }
        }
    }

    function setExtra($extra) {
        $this->extra = $extra;
    }

    /**
     * adds the default script for a messagebox
     *
     * @param none
     */
    function setMessageBox() {
        global $sess;
        $this->_scripts["__msgbox"] = '<script type="text/javascript" src="scripts/messageBox.js.php?contenido=' . $sess->id . '"></script>' .
                '<script type="text/javascript"> 

               /* Session-ID */
               var sid = "' . $sess->id . '";

               /* Create messageBox
                  instance */
               box = new messageBox("", "", "", 0, 0);

              </script>';
    }

    function setMarkScript($item) {
        $this->_scripts["__markscript"] = markSubMenuItem($item, true);
    }

    function setEncoding($encoding) {
        $this->_encoding = $encoding;
    }

    public function sendNoCacheHeaders() {
        header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
        header("Pragma: no-cache"); // HTTP 1.0.
        header("Expires: 0"); // Proxies.
    }

    /**
     * render the page
     *
     * @param none
     */
    function render($print = true) {
        global $sess, $cfg;
        $tpl = new Template();

        $scripts = "";
        if (is_array($this->_scripts)) {
            foreach ($this->_scripts as $key => $value) {
                $scripts .= $value;
            }
        }

        if ($this->_object !== false && method_exists($this->_object, "render") && is_array($this->_requiredScripts)) {
            foreach ($this->_requiredScripts as $key => $value) {
                $scripts .= '<script type="text/javascript" src="scripts/' . $value . '"></script>' . "\n";
            }
        }

        if (is_array($this->_js) && count($this->_js) > 0) {
            foreach ($this->_js as $sFilename) {
                $scripts .= '<script type="text/javascript" src="' . $sFilename . '"></script>' . "\n";
            }
        }

        if ($this->_subnav != "") {
            $scripts .= '<script type="text/javascript">';
            $scripts .= 'parent.frames["right_top"].location.href = "' . $sess->url("main.php?area=" . $this->_subnavArea . "&frame=3&" . $this->_subnav) . '";';
            $scripts .= '</script>';
        }

        $css = "";
        if (is_array($this->_css)) {
            foreach ($this->_css as $sFilename) {
                $css .= '<link rel="stylesheet" type="text/css" href="' . $sFilename . '" />' . "\n";
            }
        }

        $meta = '';
        if(!empty($this->_encoding)) {
            if($this->_isHtml5) {
                $meta .= '<meta charset="' . $this->_encoding . '">' . "\n";
            } else {
                $meta .= '<meta http-equiv="Content-type" content="text/html;charset=' . $this->_encoding . '">' . "\n";
            }
        }

        if ($this->_object !== false && method_exists($this->_object, "render")) {
            $this->_content = $this->_object->render();
        }

        if ($this->_isHtml5) {
            $this->_content .= "\n" . '<script type="text/javascript" src="scripts/jquery/jquery.js"></script>';
            $this->_content .= "\n" . '<script type="text/javascript" src="scripts/jquery/jquery-ui.js"></script>';
        }

        $tpl->set('s', 'META', $meta);
        $tpl->set('s', 'SCRIPTS', $scripts);
        $tpl->set('s', 'CSS', $css);
        $tpl->set('s', 'CONTENT', $this->_content);
        $tpl->set('s', 'MARGIN', $this->_margin);
        $tpl->set('s', 'EXTRA', $this->extra);
        $tpl->set('s', 'SESSION_ID', $sess->id);

        if ($print == true) {
            $tplRender = false;
        } else {
            $tplRender = true;
        }

        if ($this->_isHtml5) {
            $rendered = $tpl->generate($cfg['path']['contenido'] . $cfg['path']['templates'] . "html5/" . $cfg['templates']['generic_page'], $tplRender, false);
        } else {
            $rendered = $tpl->generate($cfg['path']['contenido'] . $cfg['path']['templates'] . $cfg['templates']['generic_page'], $tplRender, false);
        }

        if ($print == true) {
            echo $rendered;
        } else {
            return $rendered;
        }
    }

}

/**
 * Predefined page for use in frame 1
 *
 * @author      Timo A. Hummel <timo.hummel@4fb.de>
 */
class cPageLeftTop extends cPage {

    /**
     * Constructor Function
     *
     * @param $showCloser boolean True if the closer should be shown (default)
     */
    function __construct($showCloser = true) {
        $this->showCloser($showCloser);
    }

    /**
     * set wether the closer should be shown. 
     *
     * @param $show boolean True if the closer should be shown (default)
     */
    function showCloser($show) {
        $this->_showCloser = $show;
    }

    function render($print = true) {
        global $cfg;

        $tpl = new Template();
        $tpl->set('s', 'CONTENT', $content);
        $this->setContent($tpl->generate($cfg['path']['contenido'] . $cfg['path']['templates'] . $cfg['templates']['widgets']['left_top'], true));

        parent::render($print);
    }

}

/**
 * Predefined page for use in frame 1 with a multipane
 *
 * @author      Timo A. Hummel <timo.hummel@4fb.de>
 */
class cPageLeftTopMultiPane extends cPageLeftTop {

    /**
     * Storage of the items
     * @var array
     * @access private
     */
    var $_items;

    /**
     * Constructor Function
     *
     * The passed array needs to be a multi-array in the following format:
     *
     * 	$items = array(
     * 		array(	"image", "description", "link"),
     * 		array(	"image", "description", "link")
     * 	);
     *
     * Each sub-array needs to define an image, a description and a link.
     * Note that the images are relative to the current directory, so you
     * should include $cfg["path"]["images"] to retrieve the correct path.
     *
     * @param $items array All items passed as multi array (see constructor description)
     */
    function __construct($items) {
        $this->_items = $items;

        parent::__construct();
    }

    /**
     * set wether the closer should be shown. 
     *
     * @param $show boolean True if the closer should be shown (default)
     */
    function showCloser($show) {
        $this->_showCloser = $show;
    }

    /**
     * render
     *
     */
    function render($print = true) {
        global $cfg;

        $infodiv = new cHTMLDiv;

        if (count($this->_items) > 0) {
            foreach ($this->_items as $item) {
                if (count($item) != 3) {
                    echo "Error: the passed multi-array for cPageLeftTopMultiPane should contain 3 entries for each sub-item (see documentation for cPageLeftTopMultiPane)";
                } else {
                    $button = new cWidgetMultiToggleButton($item[0], $item[1], $item[2]);
                    $button->setBorder(1);
                    $button->setHint($infodiv->getID(), $item[1]);
                    $button->_link->setTargetFrame("left_bottom");
                    $linkedids[] = $button->_img->getID();
                    $buttons[] = $button;
                }
            }

            $buttons[0]->setDefault();
            $infodiv->setContent($buttons[0]->_hinttext);

            foreach ($buttons as $button) {
                foreach ($linkedids as $value) {
                    $button->addLinkedItem($value);
                }

                $button->setStyle("margin-right: 2px;");

                $content .= $button->render();
            }

            $content .= $infodiv->render();

            $wrapdiv = new cHTMLDiv;
            $wrapdiv->setStyle("padding: 10px;");
            $wrapdiv->setContent($content);
            $this->setContent($wrapdiv);
        }

        $content = $this->_content;

        $tpl = new Template;
        $tpl->set('s', 'CONTENT', $content);
        $this->setContent($tpl->generate($cfg['path']['templates'] . $cfg['templates']['widgets']['left_top'], true));

        parent::render();
    }

}

class cNewPageLeftTopMultiPane extends cPageLeftTopMultiPane {

    function __construct($items) {
        parent::__construct($items);
    }

    function render($print = true) {
        global $cfg;

        $infodiv = new cHTMLDiv();

        if (count($this->_items) > 0) {
            foreach ($this->_items as $item) {
                if (count($item) != 3) {
                    echo "Error: the passed multi-array for cPageLeftTopMultiPane should contain 3 entries for each sub-item (see documentation for cPageLeftTopMultiPane)";
                } else {
                    $button = new cWidgetMultiToggleButton($item[0], $item[1], $item[2]);
                    $button->setBorder(1);
                    $button->setHint($infodiv->getID(), $item[1]);
                    $button->_link->setTargetFrame("left_bottom");
                    $linkedids[] = $button->_img->getID();
                    $buttons[] = $button;
                }
            }

            $buttons[0]->setDefault();
            $infodiv->setContent($buttons[0]->_hinttext);

            foreach ($buttons as $button) {
                foreach ($linkedids as $value) {
                    $button->addLinkedItem($value);
                }

                $button->setStyle("margin-right: 2px;");

                $content .= $button->render();
            }

            $content .= $infodiv->render();

            $wrapdiv = new cHTMLDiv;
            $wrapdiv->setStyle("padding: 10px;");
            $wrapdiv->setContent($content);
            $this->setContent($wrapdiv);
        }

        return $content;
    }

}
