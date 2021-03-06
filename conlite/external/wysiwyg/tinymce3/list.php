<?php
/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * TINYMCE 1.45rc1 PHP WYSIWYG interface
 * Generates file/link list for editor
 * 
 * Requirements: 
 * @con_php_req 5
 * @con_notice 
 * TINYMCE 1.45rc1 Fileversion
 *
 * @package    ContenidoBackendArea
 * @version    0.0.4
 * @author     Martin Horwath, horwath@dayside.net
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * 
 * 
 * 
 * {@internal 
 *   created  2005-06-10
 *   modified 2008-07-04, bilal arslan, added security fix
 *   modified 2010-01-13, Ingo van Peeren, CON-295
 *   modified 2010-05-20, Murat Purc, standardized Contenido startup and security check invocations, see [#CON-307]
 *
 *   $Id$:
 * }}
 * 
 */
 
if (!defined("CON_FRAMEWORK")) {
    define("CON_FRAMEWORK", true);
}

// Contenido startup process
include_once ('../../../includes/startup.php');

// include editor config/combat file
@include (dirname(__FILE__).DIRECTORY_SEPARATOR."config.php"); // CONTENIDO

$db2 = new DB_ConLite();

$arg_seperator = "&amp;";

switch($_REQUEST['mode']) {

	case "link":
		$sql = "SELECT
					*
				FROM
					".$cfg["tab"]["cat_tree"]." AS a,
					".$cfg["tab"]["cat_lang"]." AS b,
					".$cfg["tab"]["cat"]." AS c
				WHERE
					a.idcat = b.idcat AND
					c.idcat = a.idcat AND
					c.idclient = '".Contenido_Security::toInteger($client)."' AND
					b.idlang = '".Contenido_Security::toInteger($lang)."'
				ORDER BY
					a.idtree";

		$db->query($sql);

		echo "var tinyMCELinkList = new Array(";

		$loop = false;
				
		while ( $db->next_record() ) {
			$tmp_catname  = $db->f("name");
			$spaces = "";

			for ($i = 0; $i < $db->f("level"); $i++) {
				$spaces .= "&nbsp;&nbsp;";
			}

			if ($loop) {
				echo ",";
			} else {
				$loop = true;
			}
			
			if ($db->f("visible") == 0) {
				$tmp_catname = "[" . $tmp_catname . "]";
			}

			echo "\n\t".'["'.$spaces.$tmp_catname.'", "'."front_content.php?idcat=".$db->f("idcat").'"]';

			if ($cfg["is_start_compatible"] == true)
			{
				$sql2 = "SELECT
							 *
						 FROM
							 ".$cfg["tab"]["cat_art"]." AS a,
							 ".$cfg["tab"]["art"]." AS b,
							 ".$cfg["tab"]["art_lang"]." AS c
						 WHERE
							 a.idcat = '".$db->f("idcat")."' AND
							 b.idart = a.idart AND
							 c.idart = a.idart AND
							 c.idlang = '".$lang."' AND
							 b.idclient = '".$client."'
						 ORDER BY
							 a.is_start DESC,
							 c.title ASC";
			} else {
				$sql2 = "SELECT
							 *
						 FROM
							 ".$cfg["tab"]["cat_art"]." AS a,
							 ".$cfg["tab"]["art"]." AS b,
							 ".$cfg["tab"]["art_lang"]." AS c
						 WHERE
							 a.idcat = '".$db->f("idcat")."' AND
							 b.idart = a.idart AND
							 c.idart = a.idart AND
							 c.idlang = '".Contenido_Security::toInteger($lang)."' AND
							 b.idclient = '".Contenido_Security::toInteger($client)."'
						 ORDER BY
							 c.title ASC";
			}

			$db2->query($sql2);

			while ($db2->next_record()) {

				$tmp_title = $db2->f("title");

				if ( strlen($tmp_title) > 32 ) {
					$tmp_title = substr($tmp_title, 0, 32);
				}

				if ($cfg["is_start_compatible"] == true)
				{
					$is_start = $db2->f("is_start");
				} else {
					$is_start = isStartArticle($db2->f("idartlang"), $db2->f("idcat"), $lang);
					if ($is_start == true)
					{
						$is_start = 1;
					} else {
						$is_start = 0;
					}
				}
				if ($is_start == 1) {
					$tmp_title .= "*";
				}
				if ($db2->f("online") == 0) {
					$tmp_title = "[" . $tmp_title . "]";
				}
				echo ",\n\t".'["&nbsp;&nbsp;'.$spaces.'|&nbsp;&nbsp;'.$tmp_title.'", "'."front_content.php?idart=".$db2->f("idart").'"]';
			}
		}

		echo "\n);";

		break;

	case "image":
		$sql = "SELECT * FROM ".$cfg["tab"]["upl"]." WHERE idclient='".Contenido_Security::toInteger($client)."' AND filetype IN ('gif', 'jpg', 'jpeg', 'png') ORDER BY dirname, filename ASC";
		$db->query($sql);

		echo "var tinyMCEImageList = new Array(";

		$loop = false;

		while ( $db->next_record() ) {
			if ($loop) {
				echo ",";
			} else {
				$loop = true;
			}

			echo "\n\t".'["'.$db->f("dirname").$db->f("filename").'", "'.$cfgClient[$client]["upload"].$db->f("dirname").$db->f("filename").'"]';
		}

		echo "\n);";
		break;

	case "flash":
		$sql = "SELECT * FROM ".$cfg["tab"]["upl"]." WHERE idclient='".Contenido_Security::toInteger($client)."' AND filetype IN ('swf') ORDER BY dirname,filename ASC";
		$db->query($sql);

		echo "var tinyMCEFlashList = new Array(";

		$loop = false;

		while ( $db->next_record() ) {
			if ($loop) {
				echo ",";
			} else {
				$loop = true;
			}

			echo "\n\t".'["'.$db->f("dirname").$db->f("filename").'", "'.$cfgClient[$client]["upload"].$db->f("dirname").$db->f("filename").'"]';
		}

		echo "\n);";
		break;

	case "media":
		$sql = "SELECT * FROM ".$cfg["tab"]["upl"]." WHERE idclient='".Contenido_Security::toInteger($client)."' AND filetype IN ('swf','dcr','mov','qt','mpg','mpg3','mpg4','mpeg','avi','wmv','wm','asf','asx','wmx','wvx','rm','ra','ram') ORDER BY dirname, filename ASC";
		$db->query($sql);

		echo "var tinyMCEMediaList = new Array(";

		$loop = false;

		while ( $db->next_record() ) {
			if ($loop) {
				echo ",";
			} else {
				$loop = true;
			}

			echo "\n\t".'["'.$db->f("dirname").$db->f("filename").'", "'.$cfgClient[$client]["upload"].$db->f("dirname").$db->f("filename").'"]';
		}

		echo "\n);";
		break;

	default:
}
?>