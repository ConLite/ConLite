<?php
/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * 
 * Requirements: 
 * @con_php_req 5
 * 
 *
 * @package    ContenidoBackendArea
 * @version    0.1
 * @author     unknown
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * 
 * 
 * 
 * {@internal 
 *   created  unknown
 *   modified 2008-07-07, bilal arslan, added security fix
 *
 *   $Id$:
 * }}
 * 
 */
 if(!defined('CON_FRAMEWORK')) {
                die('Illegal call');
}
 	

class cHTMLAlphaImage extends cHTMLImage {
    public $_sClickImage;
    public $_sMouseoverClickImage;
    public $_sMouseoverSrc;

    public function __construct()
    {
    }
	
	function setMouseover ($sMouseoverSrc)
	{
		$this->_sMouseoverSrc = $sMouseoverSrc;	
	}

	function setSwapOnClick ($sClickSrc, $sMouseoverClickSrc)
	{
		$this->_sClickImage = $sClickSrc;
		$this->_sMouseoverClickImage = $sMouseoverClickSrc;
	}	
	
	function toHTML ()
	{
		
		$alphaLoader = 'progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'%s\')';
		$imageLocations = "this.imgnormal = '%s'; this.imgover = '%s'; this.clickimgnormal = '%s'; this.clickimgover = '%s';";
		
		$this->attachStyleDefinition("filter", sprintf($alphaLoader, $this->_src));
		$this->attachEventDefinition("imagelocs", "onLoad", sprintf($imageLocations, $this->_src, $this->_sMouseoverSrc, $this->_sClickImage, $this->_sMouseoverClickImage));
		$this->attachEventDefinition("swapper", "onLoad", 'if (!this.init) {IEAlphaInit(this); IEAlphaApply(this, this.imgnormal); this.init = true;}');
		
		
		if ($this->_sMouseoverSrc != "")
		{
			if ($this->_sClickImage != "")
			{
				$this->attachEventDefinition("click", "onClick", "clickHandler(this);");
				$this->attachEventDefinition("mouseover", "onMouseOver", "mouseoverHandler(this);");
				$this->attachEventDefinition("mouseover", "onMouseOut", "mouseoutHandler(this);");				
			} else {
				$sMouseScript = 'if (isMSIE) { this.style.filter = \'progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\\\'%1$s\\\');\'; } else { this.src=\'%1$s\'; }';
				$this->attachEventDefinition("mouseover", 	"onMouseOver", sprintf($sMouseScript, $this->_sMouseoverSrc) );
				$this->attachEventDefinition("mouseover", 	"onMouseOut", sprintf($sMouseScript, $this->_src) );
			}
		}
		

		return null;
	}
}

class cHTMLErrorMessageList extends cHTMLDiv {
    
    /**
     *  cHTMLErrorMessageList list all errors using a table
     */
    public function __construct() {
        $this->_oTable = new cHTMLTable();
        $this->_oTable->setWidth("100%");
        $this->setClass("errorlist");
        $this->setStyle("width: 450px; height: 218px; overflow: auto; border: 1px solid black;");
    }
	
    function setContent($content)	{
        $this->_oTable->setContent($content);
    }
	
    function toHTML()	{
        $this->_setContent($this->_oTable->render());
        return null;
    }
}

class cHTMLFoldableErrorMessage extends cHTMLTableRow {
    
    /**
     *
     * @param string $sTitle
     * @param string $sMessage
     * @param string $sIcon optional
     * @param string $sIconText optional
     */
    function __construct($sTitle, $sMessage, $sIcon = false, $sIconText = false)	{
        $this->_oFolding = new cHTMLTableData;
        $this->_oContent = new cHTMLTableData;
        $this->_oIcon    = new cHTMLTableData;
        $this->_oIconImg = new cHTMLAlphaImage;
        $this->_oTitle	 = new cHTMLDiv;
        $this->_oMessage = new cHTMLDiv;

        $alphaImage = new cHTMLAlphaImage;
        $alphaImage->setClass("closer");
        $alphaImage->setStyle('margin-top:4px;');
        $alphaImage->setSrc("../conlite/images/open_all.gif");
        $alphaImage->setMouseover("../conlite/images/open_all.gif");
        $alphaImage->setSwapOnClick("../conlite/images/close_all.gif", "../conlite/images/close_all.gif");
        $alphaImage->attachEventDefinition("showhide", "onClick", "aldiv = document.getElementById('".$this->_oMessage->getId()."');  showHideMessage(this, aldiv);");

        $this->_oTitle->setContent($sTitle);
        $this->_oTitle->setStyle("cursor: pointer;");
        $this->_oTitle->attachEventDefinition("showhide", "onClick", "alimg = document.getElementById('".$alphaImage->getId()."'); aldiv = document.getElementById('".$this->_oMessage->getId()."'); showHideMessage(alimg, aldiv); clickHandler(alimg);");

        $this->_oMessage->setContent($sMessage);
        $this->_oMessage->setClass("entry_closed");

        $this->_oFolding->setVerticalAlignment("top");
        $this->_oFolding->setContent($alphaImage);
        $this->_oFolding->setClass("icon");

        $this->_oContent->setVerticalAlignment("top");
        $this->_oContent->setClass("entry");
        $this->_oContent->setContent([$this->_oTitle, $this->_oMessage]);

        $this->_oIcon->setClass("icon");
        $this->_oIcon->setVerticalAlignment("top");
        if ($sIcon !== false)
        {
        $this->_oIconImg->setSrc($sIcon);

        if ($sIconText !== false)
        {
            $this->_oIconImg->setAlt($sIconText);
        }

        $this->_oIcon->setContent($this->_oIconImg);	


        } else {
        $this->_oIcon->setContent("&nbsp;");	
        }
    }
	
    function toHTML()	{
        $this->setContent([$this->_oFolding, $this->_oContent, $this->_oIcon]);
        return null;	
    }
}

class cHTMLInfoMessage extends cHTMLTableRow {
    
    /**
     *
     * @param string $sTitle
     * @param string $sMessage 
     */
    function __construct($sTitle, $sMessage)	{
        $this->_oTitle = new cHTMLTableData;
        $this->_oMessage = new cHTMLTableData;

        $this->_oTitle->setContent($sTitle);
        $this->_oTitle->setClass("entry_nowrap");
        $this->_oTitle->setAttribute("nowrap", "nowrap");
        $this->_oTitle->setWidth(1);
        $this->_oTitle->setVerticalAlignment("top");
        $this->_oMessage->setContent($sMessage);
        $this->_oMessage->setClass("entry_nowrap");
    }
	
    function toHTML()	{
        $this->setContent([$this->_oTitle, $this->_oMessage]);
        return null;
    }
}

class cHTMLLanguageLink extends cHTMLDiv {
    
    /**
     *
     * @param string $langcode
     * @param string $langname
     * @param int $stepnumber 
     */
    function __construct($langcode, $langname, $stepnumber) {
        $linkImage = new cHTMLAlphaImage();
        $linkImage->setAlt("");
        $linkImage->setSrc("../conlite/images/submit.gif");
        $linkImage->setMouseover("../conlite/images/submit_hover.gif");
        $linkImage->setWidth(16);
        $linkImage->setHeight(16);


        $this->setStyle("vertical-align: center; height: 40px; width: 150px;");
        $link = new cHTMLLink("#");
        $link->setContent($langname);
        $link->attachEventDefinition("stepAttach", "onclick", "document.setupform.step.value = '$stepnumber';");
        $link->attachEventDefinition("languageAttach", "onclick", "document.setupform.elements.language.value = '$langcode';");
        $link->attachEventDefinition("submitAttach", "onclick", "document.setupform.submit();");		

        $link2 = new cHTMLLink("#");
        $link2->setContent($langname);
        $link2->attachEventDefinition("stepAttach", "onclick", "document.setupform.step.value = '$stepnumber';");
        $link2->attachEventDefinition("languageAttach", "onclick", "document.setupform.elements.language.value = '$langcode';");
        $link2->attachEventDefinition("submitAttach", "onclick", "document.setupform.submit();");				

        $link->attachEventDefinition("mouseover", "onMouseOver", sprintf("mouseoverHandler(document.getElementById('%s'));", $linkImage->getId()));
        $link->attachEventDefinition("mouseout", "onMouseOut", sprintf("mouseoutHandler(document.getElementById('%s'));", $linkImage->getId()));
        $link2->setContent($linkImage);

        $alignment = '<table style="width: 100%%;border: 0;"><tr><td style="vertical-align: middle;">%s</td><td style="text-align: right; vertical-align: middle;">%s</td></tr></table>';
        $this->setContent(sprintf($alignment, $link->render(), $link2->render()));
    }
}

class cHTMLButtonLink extends cHTMLDiv {
    
    /**
     *
     * @param string $href
     * @param string $title 
     */
    function __construct($href, $title)	{
        $linkImage = new cHTMLAlphaImage();
        $linkImage->setSrc("../conlite/images/submit.gif");
        $linkImage->setMouseover("../conlite/images/submit_hover.gif");
        $linkImage->setWidth(16);
        $linkImage->setHeight(16);


        $this->setStyle("vertical-align: center; height: 40px; width: 150px;");
        $link = new cHTMLLink($href);
        $link->setAttribute("target", "_blank");
        $link->setContent($title);


        $link2 = new cHTMLLink($href);
        $link2->setAttribute("target", "_blank");
        $link2->setContent($title);

        $link->attachEventDefinition("mouseover", "onMouseOver", sprintf("mouseoverHandler(document.getElementById('%s'));", $linkImage->getId()));
        $link->attachEventDefinition("mouseout", "onMouseOut", sprintf("mouseoutHandler(document.getElementById('%s'));", $linkImage->getId()));
        $link2->setContent($linkImage);



        $alignment = '<table border="0" width="100%%" cellspacing="0" cellpadding="0"><tr><td valign="middle">%s</td><td valign="middle" align="right">%s</td></tr></table>';
        $this->setContent(sprintf($alignment, $link->render(), $link2->render()));
    }
}
?>