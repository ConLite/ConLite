/*****************************************
* File      :   $RCSfile: setPager.js,v $
* Project   :   Contenido
* Descr     :   Pager folding row JavaScript helpers
* Modified  :   $Date: 2011-07-20 14:00:48 +0200 (Wed, 20 Jul 2011) $
*
* © four for business AG, www.4fb.de
*
* $Id: setPager.js 2 2011-07-20 12:00:48Z oldperl $
******************************************/

function fncSetPager(sId, sCurPage)
{
	var oLeftTop = parent.left_top;

	if (oLeftTop.document)
	{
		var oPager = oLeftTop.document.getElementById(sId);
		
		if (oPager)
		{
			oInsert = oPager.firstChild;
			oInsert.innerHTML = sNavigation;
			oLeftTop.newsletter_listoptionsform_curPage = sCurPage;
			oLeftTop.toggle_pager(sId);

			window.clearInterval(oTimer);
		}
	}
}