<?php

/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * HTML elements
 * 
 * Requirements: 
 * @con_php_req 5.0
 * 
 *
 * @package    Contenido Backend classes
 * @version    1.6.2
 * @author     Timo A. Hummel
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.6
 * 
 *
 *   $Id$:
 */
/**
 * 
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

/**
 * HTML Form element class
 *
 * @author      Timo A. Hummel <timo.hummel@4fb.de>
 */
class cHTMLFormElement extends cHTML {

    /**
     * Constructor. This is a generic form element, where
     * specific elements should be inherited from this class.
     *
     * @param $name string Name of the element 
     * @param $id string ID of the element
     * @param $disabled string Item disabled flag (non-empty to set disabled)
     * @param $tabindex string Tab index for form elements
     * @param $accesskey string Key to access the field
     *
     * @return none
     */
    public function __construct($name = "", $id = "", $disabled = "", $tabindex = "", $accesskey = "") {
        parent::__construct();

        $this->updateAttributes(array("name" => $name));

        if (is_string($id) && !empty($id)) {
            $this->updateAttributes(array("id" => $id));
        }
        
        $this->setDisabled($disabled);
        $this->setTabindex($tabindex);
        $this->setAccessKey($accesskey);
    }

    /**
     * Sets the "disabled" attribute of an element. User Agents
     * usually are showing the element as "greyed-out". 
     *
     * Example:
     * $obj->setDisabled("disabled");
     * $obj->setDisabled("");
     * 
     * The first example sets the disabled flag, the second one
     * removes the disabled flag.
     *
     * @param $disabled string Sets the disabled-flag if non-empty
     * @return none
     */
    function setDisabled($disabled) {
        if (!empty($disabled)) {
            $this->updateAttributes(array("disabled" => "disabled"));
        } else {
            $this->removeAttribute("disabled");
        }
    }

    /**
     * sets the tab index for this element. The tab
     * index needs to be numeric, bigger than 0 and smaller than 32767.
     *
     * @param $tabindex int desired tab index
     * @return none
     */
    function setTabindex($tabindex) {
        if (is_numeric($tabindex) && $tabindex >= 0 && $tabindex <= 32767) {
            $this->updateAttributes(array("tabindex" => $tabindex));
        }
    }

    /**
     * sets the access key for this element.
     *
     * @param $accesskey string The length of the access key. May be A-Z and 0-9.
     * @return none
     */
    function setAccessKey($accesskey) {
        if ((strlen($accesskey) == 1) && is_alphanumeric($accesskey)) {
            $this->updateAttributes(array("accesskey" => $accesskey));
        } else {
            $this->removeAttribute("accesskey");
        }
    }

}

/**
 * HTML Hidden Field
 *
 * @author      Timo A. Hummel <timo.hummel@4fb.de>
 */
class cHTMLHiddenField extends cHTMLFormElement {

    /**
     * Constructor. Creates an HTML hidden field.
     *
     * @param $name string Name of the element
     * @param $value string Title of the button
     * @param $id string ID of the element
     *
     * @return none
     */
    function __construct($name, $value = "", $id = "") {
        parent::__construct($name, $id);
        $this->setContentlessTag();
        $this->updateAttributes(array("type" => "hidden"));
        $this->_tag = "input";

        $this->setValue($value);
    }

    /**
     * Sets the value for the field
     *
     * @param $value string Value of the field
     * @return none
     */
    function setValue($value) {
        $this->updateAttributes(array("value" => $value));
    }

    /**
     * Renders the hidden field
     *
     * @param none
     * @return string Rendered HTML
     */
    function toHtml() {
        $attributes = $this->getAttributes(true);
        return $this->fillSkeleton($attributes);
    }

}

/**
 * HTML Button class
 *
 * @author      Timo A. Hummel <timo.hummel@4fb.de>
 */
class cHTMLButton extends cHTMLFormElement {

    /**
     * Constructor. Creates an HTML button.
     *
     * Creates a submit button by default, can be changed
     * using setMode.
     *
     * @param $name string Name of the element
     * @param $title string Title of the button
     * @param $id string ID of the element
     * @param $disabled string Item disabled flag (non-empty to set disabled)
     * @param $tabindex string Tab index for form elements
     * @param $accesskey string Key to access the field
     *
     * @return none
     */
    function __construct($name, $title = "", $id = "", $disabled = false, $tabindex = null, $accesskey = "", $mode = "submit") {
        parent::__construct($name, $id, $disabled, $tabindex, $accesskey);
        $this->_tag = "input";
        $this->setContentlessTag();
        $this->setTitle($title);
        $this->setMode($mode);
    }

    /**
     * Sets the title (caption) for the button
     *
     * @param $title string The title to set
     * @return none
     */
    function setTitle($title) {
        $this->updateAttributes(array("value" => $title));
    }

    /**
     * Sets the mode (submit or reset) for the button
     *
     * @param $mode string Either "submit", "reset" or "image".
     * @return boolean Returns false if failed to set the mode
     */
    function setMode($mode) {

        switch ($mode) {
            case "submit" :
            case "reset" :
                $this->updateAttributes(array("type" => $mode));
                break;
            case "image" :
                $this->updateAttributes(array("type" => $mode));
                break;
            case "button" :
                $this->updateAttributes(array("type" => $mode));
                break;
            default :
                return false;
        }
    }

    /**
     * Set the image src if mode type is "image"
     *
     * @param $mode string image path.
     * @return void
     */
    function setImageSource($src) {
        $this->updateAttributes(array("src" => $src));
    }

    /**
     * Renders the button
     *
     * @param none
     * @return string Rendered HTML
     */
    function toHtml() {
        $attributes = $this->getAttributes(true);
        return $this->fillSkeleton($attributes);
    }

}

/**
 * HTML Textbox
 *
 * @author      Timo A. Hummel <timo.hummel@4fb.de>
 */
class cHTMLTextbox extends cHTMLFormElement {

    /**
     * Constructor. Creates an HTML text box.
     *
     * If no additional parameters are specified, the
     * default width is 20 units.
     *
     * @param $name string Name of the element
     * @param $initvalue string Initial value of the box
     * @param $width int width of the text box
     * @param $maxlength int maximum input length of the box
     * @param $id string ID of the element
     * @param $disabled string Item disabled flag (non-empty to set disabled)
     * @param $tabindex string Tab index for form elements
     * @param $accesskey string Key to access the field
     *
     * @return none
     */
    public function __construct($name, $initvalue = "", $width = "", $maxlength = "", $id = "", $disabled = false, $tabindex = null, $accesskey = "") {
        parent::__construct($name, $id, $disabled, $tabindex, $accesskey);

        $this->_tag = "input";
        $this->setContentlessTag();
        $this->setValue($initvalue);

        $this->setWidth($width);
        $this->setMaxLength($maxlength);

        $this->updateAttributes(array("type" => "text"));
    }

    /**
     * sets the width of the text box.
     *
     * @param $width int width of the text box
     *
     * @return none
     */
    function setWidth($width) {
        $width = intval($width);

        if ($width <= 0) {
            $width = 50;
        }

        $this->updateAttributes(array("size" => $width));
    }

    /**
     * sets the maximum input length of the text box.
     *
     * @param $maxlen int maximum input length
     *
     * @return none
     */
    function setMaxLength($maxlen) {
        $maxlen = intval($maxlen);

        if ($maxlen <= 0) {
            $this->removeAttribute("maxlength");
        } else {
            $this->updateAttributes(array("maxlength" => $maxlen));
        }
    }

    /**
     * sets the initial value of the text box.
     *
     * @param $value string Initial value
     *
     * @return none
     */
    function setValue($value) {
        $this->updateAttributes(array("value" => $value));
    }

    /**
     * Renders the textbox
     *
     * @param none
     * @return string Rendered HTML
     */
    function toHtml() {
        return parent::toHtml();
    }

}

/**
 * HTML Password Box
 *
 * @author      Timo A. Hummel <timo.hummel@4fb.de>
 */
class cHTMLPasswordbox extends cHTMLFormElement {

    /**
     * Constructor. Creates an HTML password box.
     *
     * If no additional parameters are specified, the
     * default width is 20 units.
     *
     * @param $name string Name of the element
     * @param $initvalue string Initial value of the box
     * @param $width int width of the text box
     * @param $maxlength int maximum input length of the box
     * @param $id string ID of the element
     * @param $disabled string Item disabled flag (non-empty to set disabled)
     * @param $tabindex string Tab index for form elements
     * @param $accesskey string Key to access the field
     *
     * @return none
     */
    function __construct($name, $initvalue = "", $width = "", $maxlength = "", $id = "", $disabled = false, $tabindex = null, $accesskey = "") {
        parent::__construct($name, $id, $disabled, $tabindex, $accesskey);
        $this->_tag = "input";
        $this->setValue($initvalue);

        $this->setWidth($width);
        $this->setMaxLength($maxlength);

        $this->updateAttributes(array("type" => "password"));
    }

    /**
     * sets the width of the text box.
     *
     * @param $width int width of the text box
     *
     * @return none
     */
    function setWidth($width) {
        $width = intval($width);

        if ($width <= 0) {
            $width = 20;
        }

        $this->updateAttributes(array("size" => $width));
    }

    /**
     * sets the maximum input length of the text box.
     *
     * @param $maxlen int maximum input length
     *
     * @return none
     */
    function setMaxLength($maxlen) {
        $maxlen = intval($maxlen);

        if ($maxlen <= 0) {
            $this->removeAttribute("maxlength");
        } else {
            $this->updateAttributes(array("maxlength" => $maxlen));
        }
    }

    /**
     * sets the initial value of the text box.
     *
     * @param $value string Initial value
     *
     * @return none
     */
    function setValue($value) {
        $this->updateAttributes(array("value" => $value));
    }

    /**
     * Renders the textbox
     *
     * @param none
     * @return string Rendered HTML
     */
    function toHtml() {
        return parent::toHTML();
    }

}

class cHTMLTextarea extends cHTMLFormElement {

    var $_value;

    /**
     * Constructor. Creates an HTML text area.
     *
     * If no additional parameters are specified, the
     * default width is 60 chars, and the height is 5 chars.
     *
     * @param $name string Name of the element
     * @param $initvalue string Initial value of the textarea
     * @param $width int width of the textarea
     * @param $height int height of the textarea
     * @param $id string ID of the element
     * @param $disabled string Item disabled flag (non-empty to set disabled)
     * @param $tabindex string Tab index for form elements
     * @param $accesskey string Key to access the field
     *
     * @return none
     */
    function __construct($name, $initvalue = "", $width = "", $height = "", $id = "", $disabled = false, $tabindex = null, $accesskey = "") {
        parent::__construct($name, $id, $disabled, $tabindex, $accesskey);
        $this->_tag = "textarea";
        $this->setValue($initvalue);
        $this->setContentlessTag(false);
        $this->setWidth($width);
        $this->setHeight($height);
    }

    /**
     * sets the width of the text box.
     *
     * @param $width int width of the text box
     *
     * @return none
     */
    function setWidth($width) {
        $width = intval($width);

        if ($width <= 0) {
            $width = 50;
        }

        $this->updateAttributes(array("cols" => $width));
    }

    /**
     * sets the maximum input length of the text box.
     *
     * @param $maxlen int maximum input length
     *
     * @return none
     */
    function setHeight($height) {
        $height = intval($height);

        if ($height <= 0) {
            $height = 5;
        }

        $this->updateAttributes(array("rows" => $height));
    }

    /**
     * sets the initial value of the text box.
     *
     * @param $value string Initial value
     *
     * @return none
     */
    function setValue($value) {
        $this->_value = $value;
    }

    /**
     * Renders the textbox
     *
     * @param none
     * @return string Rendered HTML
     */
    function toHtml() {
        $attributes = $this->getAttributes(true);
        return $this->fillSkeleton($attributes) . $this->_value . $this->fillCloseSkeleton();
    }

}

/**
 * 
 * @package Contenido cHTML
 * @subpackage cHTMLElements
 * @author Timo A. Hummel
 * @author Ortwin Pinke
 */
class cHTMLLabel extends cHTML {

    /**
     * the text for label
     * 
     * @var string $_sText
     */
    protected $_sText;

    /**
     * Creates an HTML label which can be linked
     * to any form element (specified by their ID).
     *
     * A label can be used to link to elements. This is very useful
     * since if a user clicks a label, the linked form element receives
     * the focus (if supported by the user agent).
     * 
     * @param string $sText Text for the label
     * @param string $for Id of the form element the label belongs to
     * 
     * @return void
     */
    public function __construct($sText, $sFor) {
        parent::__construct();
        $this->_tag = "label";
        $this->setContentlessTag(false);
        $this->updateAttributes(array("for" => $sFor));
        $this->_sText = $sText;
    }

    /**
     * setter for label text
     * 
     * @param string $sText 
     */
    public function setLabelText($sText) {
        $this->_sText = $sText;
    }

    /**
     * Renders the label
     *
     * @param none
     * @return string Rendered HTML
     */
    function toHtml() {
        $attributes = $this->getAttributes(true);
        return $this->fillSkeleton($attributes) . $this->_sText . $this->fillCloseSkeleton();
    }

}

/**
 * HTML Select Element
 *
 * @author      Timo A. Hummel <timo.hummel@4fb.de>
 */
class cHTMLSelectElement extends cHTMLFormElement {

    /**
     * All cHTMLOptionElements
     * @var array
     */
    var $_options;

    /**
     * Constructor. Creates an HTML select field (aka "DropDown").
     *
     * @param $name string Name of the element
     * @param $width int width of the select element
     * @param $id string ID of the element
     * @param $disabled string Item disabled flag (non-empty to set disabled)
     * @param $tabindex string Tab index for form elements
     * @param $accesskey string Key to access the field
     *
     * @return none
     */
    function __construct($name, $width = "", $id = "", $disabled = false, $tabindex = null, $accesskey = "") {
        parent::__construct($name, $id, $disabled, $tabindex, $accesskey);
        $this->_tag = "select";
        $this->setContentlessTag(false);
    }

    /**
     * Automatically creates and fills cHTMLOptionElements
     *
     * Array format:
     * $stuff = array(
     * 					array("value","title"),
     * 					array("value","title"));
     * 
     * or regular key => value arrays.
     *
     * @param $stuff array Array with all items
     *
     * @return none
     */
    function autoFill($stuff) {
        if (is_array($stuff)) {
            foreach ($stuff as $key => $row) {
                if (is_array($row)) {
                    $option = new cHTMLOptionElement($row[1], $row[0]);
                    $this->addOptionElement($row[0], $option);
                } else {
                    $option = new cHTMLOptionElement($row, $key);
                    $this->addOptionElement($key, $option);
                }
            }
        }
    }

    /**
     * Adds an cHTMLOptionElement to the number of choices.
     *
     * @param $index string Index of the element
     * @param $element object Filled cHTMLOptionElement to add
     *
     * @return none
     */
    function addOptionElement($index, $element) {
        $this->_options[$index] = $element;
    }

    function setMultiselect() {
        $this->updateAttributes(array("multiple" => "multiple"));
    }

    function setSize($size) {
        $this->updateAttributes(array("size" => $size));
    }

    /**
     * Sets a specific cHTMLOptionElement to the selected
     * state. 
     *
     * @param $lvalue string Specifies the "value" of the cHTMLOptionElement to set
     *
     * @return none
     */
    function setDefault($lvalue) {
        $bSet = false;
        $lvalue = cString::nullToString($lvalue);
        if (is_array($this->_options)) {
            foreach ($this->_options as $key => $value) {
                if (strcmp($value->getAttribute("value"), $lvalue) == 0) {
                    $value->setSelected(true);
                    $this->_options[$key] = $value;
                    $bSet = true;
                } else {
                    $value->setSelected(false);
                    $this->_options[$key] = $value;
                }
            }
        }

        if ($bSet == false) {
            if (is_array($this->_options)) {
                foreach ($this->_options as $key => $value) {
                    $value->setSelected(true);
                    $this->_options[$key] = $value;
                    return;
                }
            }
        }
    }

    /**
     * Search for the selected elements
     *
     * @param none
     *
     * @return Selected "lvalue"
     */
    function getDefault() {
        if (is_array($this->_options)) {
            foreach ($this->_options as $key => $value) {
                if ($value->isSelected()) {
                    return $key;
                }
            }
        }
        return false;
    }

    /**
     * Sets specified elements as selected (and all others as unselected)
     *
     * @param array		$aElements Array with "values" of the cHTMLOptionElement to set
     *
     * @return none
     */
    function setSelected($aElements) {
        if (is_array($this->_options) && is_array($aElements)) {
            foreach ($this->_options as $sKey => $oOption) {
                if (in_array($oOption->getAttribute("value"), $aElements)) {
                    $oOption->setSelected(true);
                    $this->_options[$sKey] = $oOption;
                } else {
                    $oOption->setSelected(false);
                    $this->_options[$sKey] = $oOption;
                }
            }
        }
    }

    /**
     * Renders the select box
     *
     * @param none
     * @return string Rendered HTML
     */
    function toHtml() {

        $attributes = $this->getAttributes(true);

        $options = "";

        if (is_array($this->_options)) {
            foreach ($this->_options as $key => $value) {
                $options .= $value->toHtml();
            }
        }

        return ($this->fillSkeleton($attributes) . $options . $this->fillCloseSkeleton());
    }

}

/**
 * HTML Select Option Element
 *
 * @author      Timo A. Hummel <timo.hummel@4fb.de>
 */
class cHTMLOptionElement extends cHTMLFormElement {

    /**
     * Title to display
     * @var string 
     * @access private
     */
    var $_title;

    /**
     * Constructor. Creates an HTML option element.
     *
     * @param $title string Displayed title of the element
     * @param $value string Value of the option
     * @param $selected boolean If true, element is selected
     * @param $disabled boolean If true, element is disabled
     *
     * @return none
     */
    function __construct($title, $value, $selected = false, $disabled = false) {
        cHTML::__construct();
        $this->_tag = "option";
        $this->_title = $title;

        $this->updateAttributes(array("value" => $value));
        $this->setContentlessTag(false);

        $this->setSelected($selected);
        $this->setDisabled($disabled);
    }

    /**
     * sets the selected flag
     *
     * @param $selected boolean If true, adds the "selected" attribute 
     *
     * @return none
     */
    function setSelected($selected) {
        if ($selected == true) {
            $this->updateAttributes(array("selected" => "selected"));
        } else {
            $this->removeAttribute("selected");
        }
    }

    /**
     * sets the selected flag
     *
     * @param $selected boolean If true, adds the "selected" attribute 
     *
     * @return none
     */
    function isSelected() {
        if ($this->getAttribute("selected") == "selected") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * sets the disabled flag
     *
     * @param $disabled boolean If true, adds the "disabled" attribute 
     *
     * @return none
     */
    function setDisabled($disabled) {
        if ($disabled == true) {
            $this->updateAttributes(array("disabled" => "disabled"));
        } else {
            $this->removeAttribute("disabled");
        }
    }

    /**
     * Renders the option element. Note:
     * the cHTMLSelectElement renders the options by itself.
     *
     * @param none
     * @return string Rendered HTML
     */
    function toHtml() {
        $attributes = $this->getAttributes(true);
        return $this->fillSkeleton($attributes) . $this->_title . $this->fillCloseSkeleton();
    }

}

/**
 * HTML Radio Button
 *
 * @author      Timo A. Hummel <timo.hummel@4fb.de>
 */
class cHTMLRadiobutton extends cHTMLFormElement {

    /**
     * Values for the check box
     * @var string 
     * @access private
     */
    var $_value;

    /**
     * Constructor. Creates an HTML radio button element.
     *
     * @param $name string Name of the element
     * @param $value string Value of the radio button
     * @param $id string ID of the element
     * @param $checked boolean Is element checked?
     * @param $disabled string Item disabled flag (non-empty to set disabled)
     * @param $tabindex string Tab index for form elements
     * @param $accesskey string Key to access the field
     *
     * @return none
     */
    function __construct($name, $value, $id = "", $checked = false, $disabled = false, $tabindex = null, $accesskey = "") {
        parent::__construct($name, $id, $disabled, $tabindex, $accesskey);
        $this->_tag = "input";
        $this->_value = $value;
        $this->setContentlessTag();

        $this->setChecked($checked);
        $this->updateAttributes(array("type" => "radio"));
        $this->updateAttributes(array("value" => $value));
    }

    /**
     * Sets the checked flag.
     *
     * @param $checked boolean If true, the "checked" attribute will be assigned.
     *
     * @return none
     */
    function setChecked($checked) {
        if ($checked == true) {
            $this->updateAttributes(array("checked" => "checked"));
        } else {
            $this->removeAttribute("checked");
        }
    }

    /**
     * Sets a custom label text
     *
     * @param $text string Text to display
     *
     * @return none
     */
    function setLabelText($text) {
        $this->_labelText = $text;
    }

    /**
     * Renders the option element. Note:
     *
     * If this element has an ID, the value (which equals the text displayed)
     * will be rendered as seperate HTML label, if not, it will be displayed
     * as regular text. Displaying the value can be turned off via the parameter.
     *
     * @param $renderlabel boolean If true, renders a label 
     *
     * @return string Rendered HTML
     */
    function toHtml($renderLabel = true) {
        $attributes = $this->getAttributes(true);
        //print_r($attributes);
        if ($renderLabel == false) {
            return $this->fillSkeleton($attributes);
        }

        $id = $this->getAttribute("id");

        $renderedLabel = "";

        if ($id != "") {
            $label = new cHTMLLabel($this->_value, $this->getAttribute("id"));

            if ($this->_labelText != "") {
                $label->setLabelText($this->_labelText);
            }

            $renderedLabel = $label->toHtml();
        } else {
            $renderedLabel = $this->_value;
        }

        return $this->fillSkeleton($attributes) . $renderedLabel;
    }

}

/**
 * HTML Checkbox
 *
 * @author      Timo A. Hummel <timo.hummel@4fb.de>
 */
class cHTMLCheckbox extends cHTMLFormElement {

    var $_value;

    /**
     * Constructor. Creates an HTML checkbox element.
     *
     * @param $name string Name of the element
     * @param $value string Value of the radio button
     * @param $id string ID of the element
     * @param $checked boolean Is element checked?
     * @param $disabled string Item disabled flag (non-empty to set disabled)
     * @param $tabindex string Tab index for form elements
     * @param $accesskey string Key to access the field
     *
     * @return none
     */
    function __construct($name, $value, $id = "", $checked = false, $disabled = false, $tabindex = null, $accesskey = "") {

        parent::__construct($name, $id, $disabled, $tabindex, $accesskey);
        $this->_tag = "input";
        $this->_value = $value;
        $this->setContentlessTag();

        $this->setChecked($checked);
        $this->updateAttributes(array("type" => "checkbox"));
        $this->updateAttributes(array("value" => $value));
    }

    /**
     * Sets the checked flag.
     *
     * @param $checked boolean If true, the "checked" attribute will be assigned.
     *
     * @return none
     */
    function setChecked($checked) {
        if ($checked == true) {
            $this->updateAttributes(array("checked" => "checked"));
        } else {
            $this->removeAttribute("checked");
        }
    }

    /**
     * Sets a custom label text
     *
     * @param $text string Text to display
     *
     * @return none
     */
    function setLabelText($text) {
        $this->_labelText = $text;
    }

    /**
     * Renders the checkbox element. Note:
     *
     * If this element has an ID, the value (which equals the text displayed)
     * will be rendered as seperate HTML label, if not, it will be displayed
     * as regular text. Displaying the value can be turned off via the parameter.
     *
     * @param $renderlabel boolean If true, renders a label 
     *
     * @return string Rendered HTML
     */
    function toHtml($renderlabel = true) {
        $attributes = $this->getAttributes(true);
        
        if ($renderlabel == false) {
            return $this->fillSkeleton($attributes);
        }

        $id = $this->getAttribute("id");
        
        $renderedLabel = "";

        if ($id != "") {
            $label = new cHTMLLabel($this->_value, $this->getAttribute("id"));

            if ($this->_labelText != "") {
                $label->setLabelText($this->_labelText);
            }

            $renderedLabel = $label->toHtml();
        } else {
            $renderedLabel = $this->_value;
        }

        return $this->fillSkeleton($attributes) . $renderedLabel;        
    }
}

/**
 * HTML File upload box
 *
 * @author      Timo A. Hummel <timo.hummel@4fb.de>
 */
class cHTMLUpload extends cHTMLFormElement {

    /**
     * Constructor. Creates an HTML upload box.
     *
     * If no additional parameters are specified, the
     * default width is 20 units.
     *
     * @param $name string Name of the element
     * @param $initvalue string Initial value of the box
     * @param $width int width of the text box
     * @param $maxlength int maximum input length of the box
     * @param $id string ID of the element
     * @param $disabled string Item disabled flag (non-empty to set disabled)
     * @param $tabindex string Tab index for form elements
     * @param $accesskey string Key to access the field
     *
     * @return none
     */
    function __construct($name, $width = "", $maxlength = "", $id = "", $disabled = false, $tabindex = null, $accesskey = "") {
        parent::__construct($name, $id, $disabled, $tabindex, $accesskey);
        $this->_tag = "input";
        $this->setContentlessTag();

        $this->setWidth($width);
        $this->setMaxLength($maxlength);

        $this->updateAttributes(array("type" => "file"));
    }

    /**
     * sets the width of the text box.
     *
     * @param $width int width of the text box
     *
     * @return none
     */
    function setWidth($width) {
        $width = intval($width);

        if ($width <= 0) {
            $width = 20;
        }

        $this->updateAttributes(array("size" => $width));
    }

    /**
     * sets the maximum input length of the text box.
     *
     * @param $maxlen int maximum input length
     *
     * @return none
     */
    function setMaxLength($maxlen) {
        $maxlen = intval($maxlen);

        if ($maxlen <= 0) {
            $this->removeAttribute("maxlength");
        } else {
            $this->updateAttributes(array("maxlength" => $maxlen));
        }
    }

    /**
     * Renders the textbox
     *
     * @param none
     * @return string Rendered HTML
     */
    function toHtml() {
        $attributes = $this->getAttributes(true);
        return $this->fillSkeleton($attributes);
    }

}

/**
 * HTML Link
 *
 * @author      Timo A. Hummel <timo.hummel@4fb.de>
 */
class cHTMLLink extends cHTML {
    /* Stores the link location */

    var $_link;

    /* Stores the content */
    var $_content;

    /* Stores the anchor */
    var $_anchor;

    /* Stores the custom entries */
    var $_custom;
    
    protected $_type;

    /**
     * Constructor. Creates an HTML link.
     *
     * @param $href String with the location to link to
     *
     */
    function __construct($href = "") {
        global $sess;
        parent::__construct();

        $this->setLink($href);
        $this->setContentlessTag(false);
        $this->_tag = "a";

        /* Check for backend */
        if (is_object($sess)) {
            if ($sess->classname == "Contenido_Session") {
                $this->enableAutomaticParameterAppend();
            }
        }
    }

    function enableAutomaticParameterAppend() {
        $this->setEvent("click", 'var doit = true; try { var i = get_registered_parameters() } catch (e) { doit = false; }; if (doit == true) { this.href += i; }');
    }

    function disableAutomaticParameterAppend() {
        $this->unsetEvent("click");
    }

    /**
     * setLink: Sets the link to a specific location
     *
     * @param $href String with the location to link to
     *
     */
    function setLink($href) {
        $this->_link = $href;
        $this->_type = "link";

        if (strpos($href, "javascript:") !== false) {
            $this->disableAutomaticParameterAppend();
        }
    }

    /**
     * setTargetFrame: Sets the target frame
     *
     * @param $target string Target frame identifier
     *
     */
    function setTargetFrame($target) {
        $this->updateAttributes(array("target" => $target));
    }

    /**
     * setLink: Sets a Contenido link (area, frame, action)
     *
     * @param $targetarea 	string	Target backend area
     * @param $targetframe 	string	Target frame (1-4)
     * @param $targetaction string	Target action
     */
    function setCLink($targetarea, $targetframe, $targetaction = "") {
        $this->_targetarea = $targetarea;
        $this->_targetframe = $targetframe;
        $this->_targetaction = $targetaction;
        $this->_type = "clink";
    }

    /**
     * setMultiLink: Sets a multilink
     *
     * @param $righttoparea      string Area   (right top)
     * @param $righttopaction    string Action (right top)
     * @param $rightbottomarea   string Area   (right bottom)
     * @param $rightbottomaction string Action (right bottom)
     */
    function setMultiLink($righttoparea, $righttopaction, $rightbottomarea, $rightbottomaction) {
        $this->_targetarea = $righttoparea;
        $this->_targetframe = 3;
        $this->_targetaction = $righttopaction;
        $this->_targetarea2 = $rightbottomarea;
        $this->_targetframe2 = 4;
        $this->_targetaction2 = $rightbottomaction;
        $this->_type = "multilink";
    }

    /**
     * setCustom: Sets a custom attribute to be appended to the link
     *
     * @param $key  	string	Parameter name
     * @param $value	string	Parameter value
     */
    function setCustom($key, $value) {
        $this->_custom[$key] = $value;
    }

    function getHref() {
        global $sess;

        if (is_array($this->_custom)) {
            $custom = "";

            foreach ($this->_custom as $key => $value) {
                $custom .= "&$key=$value";
            }
        }

        if ($this->_anchor) {
            $anchor = "#" . $this->_anchor;
        } else {
            $anchor = "";
        }

        switch ($this->_type) {
            case "link" :
                $custom = "";
                if (is_array($this->_custom)) {
                    foreach ($this->_custom as $key => $value) {
                        if ($custom == "") {
                            $custom .= "?$key=$value";
                        } else {
                            $custom .= "&$key=$value";
                        }
                    }
                }

                return $this->_link . $custom . $anchor;
                break;
            case "clink" :
                $this->disableAutomaticParameterAppend();
                return 'main.php?area=' . $this->_targetarea . '&frame=' . $this->_targetframe . '&action=' . $this->_targetaction . $custom . "&contenido=" . $sess->id . $anchor;
                break;
            case "multilink" :
                $this->disableAutomaticParameterAppend();
                $tmp_mstr = 'javascript:conMultiLink(\'%s\',\'%s\',\'%s\',\'%s\');';
                $mstr = sprintf($tmp_mstr, 'right_top', $sess->url("main.php?area=" . $this->_targetarea . "&frame=" . $this->_targetframe . "&action=" . $this->_targetaction . $custom), 'right_bottom', $sess->url("main.php?area=" . $this->_targetarea2 . "&frame=" . $this->_targetframe2 . "&action=" . $this->_targetaction2 . $custom));
                return $mstr;
                break;
        }
    }

    /**
     * setAnchor: Sets an anchor
     *
     * Only works for the link types Link and cLink.
     *
     * @param $content string Anchor name
     *
     */
    function setAnchor($anchor) {
        $this->_anchor = $anchor;
    }

    /**
     * setContent: Sets the link's content
     *
     * @param $content string/object String with the content or an object to render.
     *
     */
    function setContent($content) {
        $this->_setContent($content);
    }

    /**
     * Renders the link
     *
     * @param none
     * @return string Rendered HTML
     */
    function toHTML() {
        $this->updateAttributes(array("href" => $this->getHref()));

        return parent::toHTML();
    }

}

/**
 * DIV Element
 *
 * @author      Timo A. Hummel <timo.hummel@4fb.de>
 */
class cHTMLDiv extends cHTML {

    /**
     * Constructor. Creates an HTML Div element.
     *
     * @param $content mixed String or object with the contents
     */
    function __construct($content = "") {
        parent::__construct();
        $this->setContent($content);
        $this->setContentlessTag(false);
        $this->_tag = "div";
    }

    /**
     * setContent: Sets the div's content
     *
     * @param $content string/object String with the content or an object to render.
     */
    function setContent($content) {
        $this->_setContent($content);
    }

    /**
     * Renders the DIV element
     *
     * @param none
     * @return string Rendered HTML
     */
    function toHTML() {
        return parent::toHTML();
    }

}

/**
 * SPAN Element
 *
 * @author      Timo A. Hummel <timo.hummel@4fb.de>
 */
class cHTMLSpan extends cHTML {

    /**
     * Constructor. Creates an HTML Span element.
     *
     * @param $content mixed String or object with the contents
     */
    function __construct($content = "") {
        parent::__construct();
        $this->setContent($content);
        $this->setContentlessTag(false);
        $this->_tag = "span";
    }

    /**
     * setContent: Sets the div's content
     *
     * @param $content string/object String with the content or an object to render.
     */
    function setContent($content) {
        $this->_setContent($content);
    }

    /**
     * Renders the SPAN element
     *
     * @param none
     * @return string Rendered HTML
     */
    function toHTML() {
        $attributes = $this->getAttributes(true);
        return $this->fillSkeleton($attributes) . $this->_content . $this->fillCloseSkeleton();
    }

}

/**
 * Image Element
 *
 * @author      Timo A. Hummel <timo.hummel@4fb.de>
 */
class cHTMLImage extends cHTML {

    /**
     * Image source
     * @var string 
     * @access private
     */
    var $_src;

    /**
     * Image width
     * @var int 
     * @access private
     */
    var $_width;

    /**
     * Image height
     * @var int
     * @access private
     */
    var $_height;
    
    protected $_border;
    protected $_type;

    /**
     * Constructor. Creates an HTML IMG element.
     *
     * @param $content mixed String or object with the contents
     *
     */
    function __construct($src = NULL) {
        parent::__construct();

        $this->_tag = "img";
        $this->setContentlessTag();

        $this->setBorder(0);
        $this->setSrc($src);
    }

    /**
     * setSrc: Sets the image's source file
     *
     * @param $src string source location
     *
     */
    function setSrc($src) {
        if ($src === NULL) {
            $this->_src = "images/spacer.gif";
        } else {
            $this->_src = $src;
        }
    }

    /**
     * setWidth: Sets the image's width
     *
     * @param $width int Image width
     *
     */
    function setWidth($width) {
        $this->_width = $width;
    }

    /**
     * setHeight: Sets the image's height
     *
     * @param $height int Image height
     *
     */
    function setHeight($height) {
        $this->_height = $height;
    }

    /**
     * setBorder: Sets the border size
     *
     * @param $border int Border size
     *
     */
    function setBorder($border) {
        $this->_border = $border;
    }

    function setAlignment($alignment) {
        $this->updateAttributes(array("align" => $alignment));
    }

    /**
     * applyDimensions: Apply dimensions from the source image
     *
     * @param none
     *
     */
    function applyDimensions() {
        global $cfg;

        /* Try to open the image */
        list ($width, $height) = @ getimagesize($cfg['path']['contenido'] . $this->_src);

        if (!empty($width) && !empty($height)) {
            $this->_width = $width;
            $this->_height = $height;
        }
    }

    /**
     * Renders the IMG element
     *
     * @param none
     * @return string Rendered HTML
     */
    function toHTML() {
        $this->updateAttributes(array("src" => $this->_src));

        if (!empty($this->_width)) {
            $this->updateAttributes(array("width" => $this->_width));
        }

        if (!empty($this->_height)) {
            $this->updateAttributes(array("height" => $this->_height));
        }

        //$this->updateAttributes(array ("border" => $this->_border));

        return parent::toHTML();
    }

}

/**
 * Table Element
 *
 * @author      Timo A. Hummel <timo.hummel@4fb.de>
 */
class cHTMLTable extends cHTML {

    function __construct() {
        parent::__construct();

        $this->_tag = "table";
        $this->setContentlessTag(false);
        $this->setPadding(0);
        $this->setSpacing(0);
        $this->setBorder(0);
    }

    /**
     * setContent: Sets the table's content
     *
     * @param $content string/object String with the content or an object to render.
     *
     */
    function setContent($content) {
        $this->_setContent($content);
    }

    /**
     * setCellSpacing: Sets the spacing between cells
     *
     * @param $cellspacing Spacing
     *
     */
    function setCellSpacing($cellspacing) {
        $this->updateAttributes(array("cellspacing" => $cellspacing));
    }

    function setPadding($cellpadding) {
        $this->setCellPadding($cellpadding);
    }

    function setSpacing($cellspacing) {
        $this->setCellSpacing($cellspacing);
    }

    /**
     * setCellPadding: Sets the padding between cells
     *
     * @param $cellpadding Padding
     *
     */
    function setCellPadding($cellpadding) {
        $this->updateAttributes(array("cellpadding" => $cellpadding));
    }

    /**
     * setBorder: Sets the table's border
     *
     * @param border Border size
     *
     */
    function setBorder($border) {
        $this->updateAttributes(array("border" => $border));
    }

    /**
     * setWidth: Sets the table width
     *
     * @param $width Width
     *
     */
    function setWidth($width) {
        $this->updateAttributes(array("width" => $width));
    }

    /**
     * Renders the Table element
     *
     * @param none
     * @return string Rendered HTML
     */
    function toHTML() {
        return parent::toHTML();
    }

}

/**
 * Table Body Element
 *
 * @author      Timo A. Hummel <timo.hummel@4fb.de>
 */
class cHTMLTableBody extends cHTML {

    function __construct() {
        parent::__construct();
        $this->setContentlessTag(false);
        $this->_tag = "tbody";
    }

    /**
     * setContent: Sets the table body's content
     *
     * @param $content string/object String with the content or an object to render.
     *
     */
    function setContent($content) {
        $this->_setContent($content);
    }

    /**
     * Renders the table body element
     *
     * @param none
     * @return string Rendered HTML
     */
    function toHTML() {
        return parent::toHTML();
    }

}

/**
 * Table Row Element
 *
 * @author      Timo A. Hummel <timo.hummel@4fb.de>
 */
class cHTMLTableRow extends cHTML {

    function __construct() {
        parent::__construct();
        $this->setContentlessTag(false);
        $this->_tag = "tr";
    }

    /**
     * setContent: Sets the table row's content
     *
     * @param $content string/object String with the content or an object to render.
     *
     */
    function setContent($content) {
        $this->_setContent($content);
    }

    /**
     * Renders the table row element
     *
     * @param none
     * @return string Rendered HTML
     */
    function toHTML() {
        return parent::toHTML();
    }

}

/**
 * Table Data Element
 *
 * @author      Timo A. Hummel <timo.hummel@4fb.de>
 */
class cHTMLTableData extends cHTML {

    function __construct() {
        parent::__construct();
        $this->setContentlessTag(false);
        $this->_tag = "td";
    }

    /**
     * setWidth: Sets the table width
     *
     * @param $width Width
     *
     */
    function setWidth($width) {
        $this->updateAttributes(array("width" => $width));
    }

    function setHeight($height) {
        $this->updateAttributes(array("height" => $height));
    }

    function setAlignment($alignment) {
        $this->updateAttributes(array("align" => $alignment));
    }

    function setVerticalAlignment($alignment) {
        $this->updateAttributes(array("valign" => $alignment));
    }

    function setBackgroundColor($color) {
        $this->updateAttributes(array("bgcolor" => $color));
    }

    function setColspan($colspan) {
        $this->updateAttributes(array("colspan" => $colspan));
    }

    /**
     * setContent: Sets the table data's content
     *
     * @param $content string/object String with the content or an object to render.
     *
     */
    function setContent($content) {
        $this->_setContent($content);
    }

    /**
     * Renders the table data element
     *
     * @param none
     * @return string Rendered HTML
     */
    function toHTML() {
        $attributes = $this->getAttributes(true);
        return $this->fillSkeleton($attributes) . $this->_content . $this->fillCloseSkeleton();
    }

}

/**
 * Table Head Element
 *
 * @author      Timo A. Hummel <timo.hummel@4fb.de>
 */
class cHTMLTableHead extends cHTML {

    function __construct() {
        parent::__construct();
        $this->setContentlessTag(false);
        $this->_tag = "th";
    }

    /**
     * setContent: Sets the table head's content
     *
     * @param $content string/object String with the content or an object to render.
     *
     */
    function setContent($content) {
        $this->_setContent($content);
    }

    /**
     * Renders the table head element
     *
     * @param none
     * @return string Rendered HTML
     */
    function toHTML() {
        $attributes = $this->getAttributes(true);
        return $this->fillSkeleton($attributes) . $this->_content . $this->fillCloseSkeleton();
    }

}

/**
 * Table Head Element
 *
 * @author      Timo A. Hummel <timo.hummel@4fb.de>
 */
class cHTMLTableHeader extends cHTML {

    function __construct() {
        parent::__construct();
        $this->setContentlessTag(false);
        $this->_tag = "thead";
    }

    /**
     * setContent: Sets the table head's content
     *
     * @param $content string/object String with the content or an object to render.
     *
     */
    function setContent($content) {
        $this->_setContent($content);
    }

    /**
     * Renders the table head element
     *
     * @param none
     * @return string Rendered HTML
     */
    function toHTML() {
        $attributes = $this->getAttributes(true);
        return $this->fillSkeleton($attributes) . $this->_content . $this->fillCloseSkeleton();
    }

}

/**
 * IFrame element
 *
 * @author      Timo A. Hummel <timo.hummel@4fb.de>
 */
class cHTMLIFrame extends cHTML {

    function __construct() {
        parent::__construct();
        $this->setContentlessTag(false);
        $this->_tag = "iframe";
    }

    /**
     * setSrc: Sets this frame's source
     *
     * @param $content string/object String with the content or an object to render.
     *
     */
    function setSrc($src) {
        $this->updateAttributes(array("src" => $src));
    }

    /**
     * setWidth: Sets this frame's width
     *
     * @param $width Width of the item
     *
     */
    function setWidth($width) {
        $this->updateAttributes(array("width" => $width));
    }

    /**
     * setHeight: Sets this frame's height
     *
     * @param $height Height of the item
     *
     */
    function setHeight($height) {
        $this->updateAttributes(array("height" => $height));
    }

    /**
     * setBorder: Sets wether this iframe should have a border or not
     *
     * @param $border If 1 or true, this frame will have a border
     *
     */
    function setBorder($border) {
        $this->updateAttributes(array("frameborder" => intval($border)));
    }

    /**
     * Renders the table head element
     *
     * @param none
     * @return string Rendered HTML
     */
    function toHTML() {
        $attributes = $this->getAttributes(true);
        return $this->fillSkeleton($attributes) . $this->_content . $this->fillCloseSkeleton();
    }

}

class cHTMLAlignmentTable extends cHTMLTable {

    function __construct() {
        parent::__construct();

        $this->_data = func_get_args();
        $this->setContentlessTag(false);
    }

    function render() {
        $tr = new cHTMLTableRow;
        $td = new cHTMLTableData;

        $out = "";

        foreach ($this->_data as $data) {
            $td->setContent($data);
            $out .= $td->render();
        }

        $tr->setContent($out);

        $this->setContent($tr);

        return $this->toHTML();
    }

}

class cHTMLForm extends cHTML {

    function __construct() {
        parent::__construct();
        $this->setContentlessTag(false);
        $this->_tag = "form";
    }

    function setVar($var, $value) {
        $this->_vars[$var] = $value;
    }

    /**
     * setContent: Sets the form's content
     *
     * @param $content string/object String with the content or an object to render.
     *
     */
    function setContent($content) {
        $this->_setContent($content);
    }

    /**
     * Renders the form element
     *
     * @param none
     * @return string Rendered HTML
     */
    function toHTML() {
        $out = '';
        foreach ($this->_vars as $var => $value) {
            $f = new cHTMLHiddenField($var, $value);
            $out .= $f->render();
        }

        $attributes = $this->getAttributes(true);

        return $this->fillSkeleton($attributes) . $out . $this->_content . $this->fillCloseSkeleton();
    }

}

/**
 * Table Head Element
 *
 * @author      Timo A. Hummel <timo.hummel@4fb.de>
 */
class cHTMLScript extends cHTML {

    function __construct() {
        parent::__construct();
        $this->setContentlessTag(false);
        $this->_tag = "script";
    }

    /**
     * setContent: Sets the table head's content
     *
     * @param $content string/object String with the content or an object to render.
     *
     */
    function setContent($content) {
        $this->_setContent($content);
    }

    /**
     * Renders the table head element
     *
     * @param none
     * @return string Rendered HTML
     */
    function toHTML() {
        $attributes = $this->getAttributes(true);
        return $this->fillSkeleton($attributes) . $this->_content . $this->fillCloseSkeleton();
    }

}

?>
