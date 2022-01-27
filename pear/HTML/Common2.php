<?php
/**
 * HTML_Common2: port of HTML_Common package to PHP5
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2004-2012, Alexey Borzov <avb@php.net>
 *
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *    * Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the
 *      documentation and/or other materials provided with the distribution.
 *    * The names of the authors may not be used to endorse or promote products
 *      derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS
 * IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
 * OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category HTML
 * @package  HTML_Common2
 * @author   Alexey Borzov <avb@php.net>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 * @version  SVN: $Id$
 * @link     http://pear.php.net/package/HTML_Common2
 */

/**
 * Base class for HTML classes
 *
 * Implements methods for working with HTML attributes, parsing and generating
 * attribute strings. Port of HTML_Common class for PHP4 originally written by
 * Adam Daniel with contributions from numerous other developers.
 *
 * @category HTML
 * @package  HTML_Common2
 * @author   Alexey Borzov <avb@php.net>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 * @version  Release: 2.1.0
 * @link     http://pear.php.net/package/HTML_Common2
 */
abstract class HTML_Common2 implements ArrayAccess
{
    /**
     * Associative array of attributes
     * @var array
     */
    protected $attributes = array();

    /**
     * Changes to attributes in this list will be announced via onAttributeChange()
     * method rather than performed by HTML_Common2 class itself
     * @var array
     * @see onAttributeChange()
     */
    protected $watchedAttributes = array();

    /**
     * Indentation level of the element
     * @var int
     */
    private $_indentLevel = 0;

    /**
     * Comment associated with the element
     * @var string
     */
    private $_comment = null;

    /**
     * Global options for all elements generated by subclasses of HTML_Common2
     *
     * Preset options are
     * - 'charset': charset parameter used in htmlspecialchars() calls,
     *   defaults to 'ISO-8859-1'
     * - 'indent': string used to indent HTML elements, defaults to "\11"
     * - 'linebreak': string used to indicate linebreak, defaults to "\12"
     *
     * @var array
     */
    private static $_options = array(
        'charset'   => 'ISO-8859-1',
        'indent'    => "\11",
        'linebreak' => "\12"
    );

    /**
     * Sets global option(s)
     *
     * @param string|array $nameOrOptions Option name or array ('option name' => 'option value')
     * @param mixed        $value         Option value, if first argument is not an array
     */
    public static function setOption($nameOrOptions, $value = null)
    {
        if (is_array($nameOrOptions)) {
            foreach ($nameOrOptions as $k => $v) {
                self::setOption($k, $v);
            }
        } else {
            $linebreaks = array('win' => "\15\12", 'unix' => "\12", 'mac' => "\15");
            if ('linebreak' == $nameOrOptions && isset($linebreaks[$value])) {
                $value = $linebreaks[$value];
            }
            self::$_options[$nameOrOptions] = $value;
        }
    }

    /**
     * Returns global option(s)
     *
     * @param string $name Option name
     *
     * @return mixed Option value, null if option does not exist,
     *               array of all options if $name is not given
     */
    public static function getOption($name = null)
    {
        if (null === $name) {
            return self::$_options;
        } else {
            return isset(self::$_options[$name])? self::$_options[$name]: null;
        }
    }

    /**
     * Parses the HTML attributes given as string
     *
     * @param string $attrString HTML attribute string
     *
     * @return array An associative array of attributes
     */
    protected static function parseAttributes($attrString)
    {
        $attributes = array();
        if (preg_match_all(
            "/(([A-Za-z_:]|[^\\x00-\\x7F])([A-Za-z0-9_:.-]|[^\\x00-\\x7F])*)" .
            "([ \\n\\t\\r]+)?(=([ \\n\\t\\r]+)?(\"[^\"]*\"|'[^']*'|[^ \\n\\t\\r]*))?/",
            $attrString,
            $regs
        )) {
            for ($i = 0; $i < count($regs[1]); $i++) {
                $name  = trim($regs[1][$i]);
                $check = trim($regs[0][$i]);
                $value = trim($regs[7][$i]);
                if ($name == $check) {
                    $attributes[strtolower($name)] = strtolower($name);
                } else {
                    if (!empty($value) && ($value[0] == '\'' || $value[0] == '"')) {
                        $value = substr($value, 1, -1);
                    }
                    $attributes[strtolower($name)] = $value;
                }
            }
        }
        return $attributes;
    }

    /**
     * Creates a valid attribute array from either a string or an array
     *
     * @param string|array $attributes Array of attributes or HTML attribute string
     *
     * @return array An associative array of attributes
     */
    protected static function prepareAttributes($attributes)
    {
        $prepared = array();
        if (is_string($attributes)) {
            return self::parseAttributes($attributes);

        } elseif (is_array($attributes)) {
            foreach ($attributes as $key => $value) {
                if (is_int($key)) {
                    $key = strtolower($value);
                    $prepared[$key] = $key;
                } else {
                    $prepared[strtolower($key)] = (string)$value;
                }
            }
        }
        return $prepared;
    }

    /**
     * Removes an attribute from an attribute array
     *
     * @param array  &$attributes Attribute array
     * @param string $name        Name of attribute to remove
     */
    protected static function removeAttributeArray(array &$attributes, $name)
    {
        unset($attributes[strtolower($name)]);
    }

    /**
     * Creates HTML attribute string from array
     *
     * @param array $attributes Attribute array
     *
     * @return string Attribute string
     */
    protected static function getAttributesString(array $attributes)
    {
        $str     = '';
        $charset = self::getOption('charset');
        foreach ($attributes as $key => $value) {
            $str .= ' ' . $key . '="' . htmlspecialchars($value, ENT_QUOTES, $charset) . '"';
        }
        return $str;
    }

    /**
     * Class constructor, sets default attributes
     *
     * @param array|string $attributes Array of attribute 'name' => 'value' pairs
     *                                 or HTML attribute string
     */
    public function __construct($attributes = null)
    {
        $this->mergeAttributes($attributes);
    }

    /**
     * Sets the value of the attribute
     *
     * @param string $name  Attribute name
     * @param string $value Attribute value (will be set to $name if omitted)
     *
     * @return HTML_Common2
     */
    public function setAttribute($name, $value = null)
    {
        $name = strtolower($name);
        if (is_null($value)) {
            $value = $name;
        }
        if (in_array($name, $this->watchedAttributes)) {
            $this->onAttributeChange($name, $value);
        } else {
            $this->attributes[$name] = (string)$value;
        }
        return $this;
    }

    /**
     * Returns the value of an attribute
     *
     * @param string $name Attribute name
     *
     * @return string|null Attribute value, null if attribute does not exist
     */
    public function getAttribute($name)
    {
        $name = strtolower($name);
        return isset($this->attributes[$name])? $this->attributes[$name]: null;
    }

    /**
     * Sets the attributes
     *
     * @param string|array $attributes Array of attribute 'name' => 'value' pairs
     *                                 or HTML attribute string
     *
     * @return HTML_Common2
     */
    public function setAttributes($attributes)
    {
        $attributes = self::prepareAttributes($attributes);
        $watched    = array();
        foreach ($this->watchedAttributes as $watchedKey) {
            if (isset($attributes[$watchedKey])) {
                $this->setAttribute($watchedKey, $attributes[$watchedKey]);
                unset($attributes[$watchedKey]);
            } else {
                $this->removeAttribute($watchedKey);
            }
            if (isset($this->attributes[$watchedKey])) {
                $watched[$watchedKey] = $this->attributes[$watchedKey];
            }
        }
        $this->attributes = array_merge($watched, $attributes);
        return $this;
    }

    /**
     * Returns the attribute array or string
     *
     * @param bool $asString Whether to return attributes as string
     *
     * @return array|string
     */
    public function getAttributes($asString = false)
    {
        if ($asString) {
            return self::getAttributesString($this->attributes);
        } else {
            return $this->attributes;
        }
    }

    /**
     * Merges the existing attributes with the new ones
     *
     * @param array|string $attributes Array of attribute 'name' => 'value' pairs
     *                                 or HTML attribute string
     *
     * @return HTML_Common2
     */
    public function mergeAttributes($attributes)
    {
        $attributes = self::prepareAttributes($attributes);
        foreach ($this->watchedAttributes as $watchedKey) {
            if (isset($attributes[$watchedKey])) {
                $this->onAttributeChange($watchedKey, $attributes[$watchedKey]);
                unset($attributes[$watchedKey]);
            }
        }
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    /**
     * Removes an attribute
     *
     * @param string $attribute Name of attribute to remove
     *
     * @return HTML_Common2
     */
    public function removeAttribute($attribute)
    {
        if (in_array(strtolower($attribute), $this->watchedAttributes)) {
            $this->onAttributeChange(strtolower($attribute), null);
        } else {
            self::removeAttributeArray($this->attributes, $attribute);
        }
        return $this;
    }

    /**
     * Sets the indentation level
     *
     * @param int $level Indentation level
     *
     * @return HTML_Common2
     */
    public function setIndentLevel($level)
    {
        $level = intval($level);
        if (0 <= $level) {
            $this->_indentLevel = $level;
        }
        return $this;
    }

    /**
     * Gets the indentation level
     *
     * @return int
     */
    public function getIndentLevel()
    {
        return $this->_indentLevel;
    }

    /**
     * Returns the string to indent the element
     *
     * @return string
     */
    protected function getIndent()
    {
        return str_repeat(self::getOption('indent'), $this->getIndentLevel());
    }

    /**
     * Sets the comment for the element
     *
     * @param string $comment String to output as HTML comment
     *
     * @return HTML_Common2
     */
    public function setComment($comment)
    {
        $this->_comment = $comment;
        return $this;
    }

    /**
     * Returns the comment associated with the element
     *
     * @return string
     */
    public function getComment()
    {
        return $this->_comment;
    }

    /**
     * Checks whether the element has given CSS class
     *
     * @param string $class CSS Class name
     *
     * @return bool
     */
    public function hasClass($class)
    {
        $regex = '/(^|\s)' . preg_quote($class, '/') . '(\s|$)/';
        return (bool)preg_match($regex, $this->getAttribute('class'));
    }

    /**
     * Adds the given CSS class(es) to the element
     *
     * @param string|array $class Class name, multiple class names separated by
     *                            whitespace, array of class names
     *
     * @return HTML_Common2
     */
    public function addClass($class)
    {
        if (!is_array($class)) {
            $class = preg_split('/\s+/', $class, null, PREG_SPLIT_NO_EMPTY);
        }
        $curClass = preg_split(
            '/\s+/', $this->getAttribute('class'), null, PREG_SPLIT_NO_EMPTY
        );
        foreach ($class as $c) {
            if (!in_array($c, $curClass)) {
                $curClass[] = $c;
            }
        }
        $this->setAttribute('class', implode(' ', $curClass));

        return $this;
    }

    /**
     * Removes the given CSS class(es) from the element
     *
     * @param string|array $class Class name, multiple class names separated by
     *                            whitespace, array of class names
     *
     * @return HTML_Common2
     */
    public function removeClass($class)
    {
        if (!is_array($class)) {
            $class = preg_split('/\s+/', $class, null, PREG_SPLIT_NO_EMPTY);
        }
        $curClass = array_diff(
            preg_split(
                '/\s+/', $this->getAttribute('class'), null, PREG_SPLIT_NO_EMPTY
            ),
            $class
        );
        if (0 == count($curClass)) {
            $this->removeAttribute('class');
        } else {
            $this->setAttribute('class', implode(' ', $curClass));
        }
        return $this;
    }

    /**
     * Returns the HTML representation of the element
     *
     * This magic method allows using the instances of HTML_Common2 in string
     * contexts
     *
     * @return string
     */
    abstract public function __toString();

    /**
     * Called if trying to change an attribute with name in $watchedAttributes
     *
     * This method is called for each attribute whose name is in the
     * $watchedAttributes array and which is being changed by setAttribute(),
     * setAttributes() or mergeAttributes() or removed via removeAttribute().
     * Note that the operation for the attribute is not carried on after calling
     * this method, it is the responsibility of this method to change or remove
     * (or not) the attribute.
     *
     * @param string $name  Attribute name
     * @param string $value Attribute value, null if attribute is being removed
     */
    protected function onAttributeChange($name, $value = null)
    {
    }

    /**
     * Whether or not an offset (HTML attribute) exists
     *
     * @param string $offset An offset to check for.
     *
     * @return boolean Returns true on success or false on failure.
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     */
    #[ReturnTypeWillChange] 
    public function offsetExists($offset) 
    {
        return isset($this->attributes[strtolower($offset)]);
    }

    /**
     * Returns the value at specified offset (i.e. attribute name)
     *
     * @param string $offset The offset to retrieve.
     *
     * @return string|null
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @see getAttribute()
     */
    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }

    /**
     * Assigns a value to the specified offset (i.e. attribute name)
     *
     * @param string $offset The offset to assign the value to
     * @param string $value  The value to set
     *
     * @return void
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @see setAttribute()
     */
    #[ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        if (null !== $offset) {
            $this->setAttribute($offset, $value);
        } else {
            // handles $foo[] = 'disabled';
            $this->setAttribute($value);
        }
    }

    /**
     * Unsets an offset (i.e. removes an attribute)
     *
     * @param string $offset The offset to unset
     *
     * @return void
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @see removeAttribute
     */
    #[ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        $this->removeAttribute($offset);
    }
}
?>
