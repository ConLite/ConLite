/**
 * 
 * @version $Rev: 301 $
 * 
 * $Id: cfoldingrow.js 301 2014-02-03 22:30:20Z oldperl $
 */
/*****************************************
* Project   :   Contenido
* Descr     :   cFoldingRow JavaScript helpers
*
* four for business AG, www.4fb.de
*/

/**
 * 
 * @param {type} image
 * @param {type} row
 * @param {type} hidden
 * @param {type} uuid
 * @returns void
 */
function cFoldingRow_expandCollapse (image, row, hidden, uuid)
{
	if (document.getElementById(image).getAttribute("data-folding-row") == "collapsed")
	{
		document.getElementById(row).style.display = '';
		document.getElementById(image).setAttribute("src", "images/widgets/foldingrow/expanded.gif");
		document.getElementById(image).setAttribute("data-folding-row", "expanded");
		document.getElementById(hidden).setAttribute("value", "expanded");
		register_parameter("u_register[expandstate]["+uuid+"]", "true");
	} else {
		document.getElementById(row).style.display = 'none';
		document.getElementById(image).setAttribute("src", "images/widgets/foldingrow/collapsed.gif");
		document.getElementById(image).setAttribute("data-folding-row", "collapsed");
		document.getElementById(hidden).setAttribute("value", "collapsed");
		register_parameter("u_register[expandstate]["+uuid+"]", "false");
	}
}