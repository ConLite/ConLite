/*****************************************
* File      :   $RCSfile: class.article.php,v $
* Project   :   Contenido
* Descr     :   JavaScript parameter collector function
* Modified  :   $Date: 2011-07-20 14:00:48 +0200 (Wed, 20 Jul 2011) $
*
* © four for business AG, www.4fb.de
*
* $Id: parameterCollector.js 2 2011-07-20 12:00:48Z oldperl $
******************************************/

var m_documentParameters = new Array();

function register_parameter (name, value)
{
	m_documentParameters[name] = value;	
}

function get_registered_parameters ()
{
	var output = "";
	
	for (var parameter in m_documentParameters)
	{
		output += "&" + parameter + "=" + m_documentParameters[parameter];
	}
	
	return output;
}

function append_registered_parameters (form)
{
	for (var parameter in m_documentParameters)
	{
		var el = document.createElement("input");
		el.type = "hidden";
		el.name =  parameter;
		el.value = m_documentParameters[parameter];
		form.appendChild(el);
	}
}
