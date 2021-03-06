/*****************************************
* File      :   $RCSfile: setPager.js,v $
* Project   :   Contenido
* Descr     :   Pager folding row JavaScript helpers
* Modified  :   $Date$
*
* ? four for business AG, www.4fb.de
*
* $Id$
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