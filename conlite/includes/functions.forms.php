<?php
/**
 * Project:
 * Contenido Content Management System
 *
 * Description:
 * Contenido Form Element Generator
 *
 * Requirements:
 * @con_php_req 5.0
 *
 *
 * @package    Contenido Backend includes
 * @version    1.5
 * @author     Timo A. Hummel
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.6
 *
 * {@internal
 *   created 2003-05-20
 *   modified 2008-06-26, Frederic Schneider, add security fix
 *
 *   $Id$:
 * }}
 *
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

/**
 * Generates textial Input Form elements
 *
 * @param string $type
 * @param string $name
 * @param ?string $initValue
 * @param int $width
 * @param int $maxLen
 * @return string
 */
function formGenerateField(string $type, string $name, ?string $initValue, int $width, int $maxLen): string
{
    switch ($type) {
        case "text":
            return ('<input class="text_medium" type="text" name="' . $name . '" size="' . $width . '" maxlength="' . $maxLen . '" value="' . $initValue . '">');
            break;
        case "password":
            return ('<input class="text_medium" type="password" name="' . $name . '" size="' . $width . '" maxlength="' . $maxLen . '" value="' . $initValue . '">');
            break;
        case "textbox":
            return ('<textarea class="text_medium" name="' . $name . '" rows="' . $maxLen . '" cols="' . $width . '">' . $initValue . '</textarea>');
            break;
        default:
            return ('');
            break;
    }


}

/**
 * @param string $name
 * @param string $value
 * @param mixed $checked
 * @param string $caption
 * @return string
 */
function formGenerateCheckbox(string $name, string $value, mixed $checked, string $caption = ""): string
{
    if (strlen($caption) > 0) {
        $label = '<label for="' . $name . $value . '">' . $caption . '</label>';
    } else {
        $label = "";
    }

    if ($checked) {
        return ('<input class="text_medium" id="' . $name . $value . '" type="checkbox" name="' . $name . '" value="' . $value . '" checked>' . $label);
    } else {
        return ('<input class="text_medium" id="' . $name . $value . '" type="checkbox" name="' . $name . '" value="' . $value . '">' . $label);
    }

}