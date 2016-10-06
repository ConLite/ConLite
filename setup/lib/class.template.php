<?php
/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * Contenido Template Engine
 * 
 * Requirements: 
 * @con_php_req 5
 * @con_notice 
 * Light template mechanism
 *
 * @package    ContenidoBackendArea
 * @version    1.2
 * @author     Jan Lengowski <Jan.Lengowski@4fb.de>
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * 
 * 
 * 
 * {@internal 
 *   created  unknown
 *   modified unknown, Stefan Jelner (Optimizations)
 *   modified 2008-07-07, bilal arslan, added security fix
 *
 *   $Id: class.template.php 283 2014-01-09 14:48:38Z oldperl $:
 * }}
 * 
 */
 if(!defined('CON_FRAMEWORK')) {
   die('Illegal call');
}

class Template
{
	/**
	 * Needles (static)
	 * @var array
	 */
	var $needles = array ();

	/**
	 * Replacements (static)
	 * @var array
	 */
	var $replacements = array ();

	/**
	 * Dyn_Needles (dynamic)
	 * @var array
	 */
	var $Dyn_needles = array ();

	/**
	 * Dyn_Replacements (dynamic)
	 * @var array
	 */
	var $Dyn_replacements = array ();

	/**
	 * Database instance
	 * @var object
	 */
	var $db;

	/**
	 * Template cache
	 * @var array
	 */
	var $tplcache;

	/**
	 * Template name cache
	 * @var array
	 */
	var $tplnamecache;

	/**
	 * Dynamic counter
	 * @var int
	 */
	var $dyn_cnt = 0;

	/**
	 * Tags array (for dynamic blocks);
	 * @var array
	 */
	var $tags = array ('static' => '{%s}', 'start' => '<!-- BEGIN:BLOCK -->', 'end' => '<!-- END:BLOCK -->');

	/**
	 * Constructor function
	 * @return void
	 */
	function Template($tags = false)
	{
		$this->tplcache = Array ();
		$this->tplnamecache = Array ();

		if (is_array($tags))
		{
			$this->tags = $tags;
		}
		
		$this->setEncoding("");
        $this->setDomain("conlite");		
	} // end function

    /**
     * setDomain
     *
     * Sets the gettext domain to use for translations in a template
     *
	 * @param $sDomain	string	Sets the domain to use for template translations
     * @return none
     */    
    function setDomain ($sDomain)
    {
    	$this->_sDomain = $sDomain;
    }
    
	/**
	 * Set Templates placeholders and values
	 *
	 * With this method you can replace the placeholders
	 * in the static templates with dynamic data.
	 *
	 * @param $which String 's' for Static or else dynamic
	 * @param $needle String Placeholder
	 * @param $replacement String Replacement String
	 *
	 * @return void
	 */
	function set($which = 's', $needle, $replacement)
	{
		if ($which == 's')
		{ // static
			$this->needles[] = sprintf($this->tags['static'], $needle);
			$this->replacements[] = $replacement;

		} else
		{ // dynamic
			$this->Dyn_needles[$this->dyn_cnt][] = sprintf($this->tags['static'], $needle);
			$this->Dyn_replacements[$this->dyn_cnt][] = $replacement;

		}
	}

    /**
     * Sets an encoding for the template's head block.
     *
     * @param $encoding string Encoding to set
     */    
    function setEncoding ($encoding)
    {
    	$this->_encoding = $encoding;
    }
    
	/**
	 * Iterate internal counter by one
	 *
	 * @return void
	 */
	function next()
	{
		$this->dyn_cnt++;
	}

	/**
	 * Reset template data
	 *
	 * @return void
	 */
	function reset()
	{
		$this->dyn_cnt = 0;
		$this->needles = array ();
		$this->replacements = array ();
		$this->Dyn_needles = array ();
		$this->Dyn_replacements = array ();
	}

	/**
	 * Generate the template and
	 * print/return it. (do translations sequentially to save memory!!!)
	 *
	 * @param $template string/file Template
	 * @param $return bool Return or print template
	 * @param $note bool Echo "Generated by ... " Comment
	 *
	 * @return string complete Template string
	 */
	function generate($template, $return = 0, $note = 1)
	{
		global $cfg;
  $this->set("s", "TPL_ACT_YEAR", date("Y"));
		//check if the template is a file or a string
		if (!@ file_exists($template))
			$content = & $template; //template is a string (it is a reference to save memory!!!)
		else
			$content = implode("", file($template)); //template is a file

		$content = (($note) ? "<!-- Generated by ConLite ".$cfg['version']."-->\n" : "").$content;

		$pieces = array();
		
		//if content has dynamic blocks
		if (preg_match("/^.*".preg_quote($this->tags['start'], "/").".*?".preg_quote($this->tags['end'], "/").".*$/s", $content))
		{
			//split everything into an array
			preg_match_all("/^(.*)".preg_quote($this->tags['start'], "/")."(.*?)".preg_quote($this->tags['end'], "/")."(.*)$/s", $content, $pieces);
			//safe memory
			array_shift($pieces);
			$content = "";
			//now combine pieces together

			//start block
			$pieces[0][0] = str_replace($this->needles, $this->replacements, $pieces[0][0]);
			$this->replacei18n($pieces[0][0], "i18n");
			$this->replacei18n($pieces[0][0], "trans");
			$content .= $pieces[0][0];
			unset ($pieces[0][0]);

			//generate dynamic blocks
			for ($a = 0; $a < $this->dyn_cnt; $a ++)
			{
				$temp = str_replace($this->Dyn_needles[$a], $this->Dyn_replacements[$a], $pieces[1][0]);
				$this->replacei18n($temp, "i18n");
				$this->replacei18n($temp, "trans");
				$content .= $temp;
			}
			unset ($temp);

			//end block
			$pieces[2][0] = str_replace($this->needles, $this->replacements, $pieces[2][0]);
			$this->replacei18n($pieces[2][0], "i18n");
			$this->replacei18n($pieces[2][0], "trans");
			$content .= $pieces[2][0];
			unset ($pieces[2][0]);
		} else
		{
			$content = str_replace($this->needles, $this->replacements, $content);
			$this->replacei18n($content, "i18n");
			$this->replacei18n($content, "trans");
		}

        if ($this->_encoding != "")
        {
        	$content = str_replace("</head>", '<meta http-equiv="Content-Type" content="text/html; charset='.$this->_encoding.'">'."\n".'</head>', $content);
        }
        
		if ($return)
			return $content;
		else
			echo $content;

	} # end function

	/**
	 * replacei18n()
	 *
	 * Replaces a named function with the translated variant
	 *
	 * @param $template string Contents of the template to translate (it is reference to save memory!!!)
	 * @param $functionName string Name of the translation function (e.g. i18n)
	 */
	function replacei18n(& $template, $functionName)
	{
		$matches = array();
		
		//if template contains functionName + parameter store all matches
		preg_match_all("/".preg_quote($functionName, "/")."\\(([\\\"\\'])(.*?)\\1\\)/s", $template, $matches);
		$matches = array_values(array_unique($matches[2]));
		for ($a = 0; $a < count($matches); $a ++)
		{
			$template = preg_replace("/".preg_quote($functionName, "/")."\\([\\\"\\']".preg_quote($matches[$a], "/")."[\\\"\\']\\)/s", i18n($matches[$a], $this->_sDomain), $template);
		}
	}

} # end class
?>