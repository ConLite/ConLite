<?php
/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * Builds the third navigation layer
 * 
 * Requirements: 
 * @con_php_req 5.0
 * 
 *
 * @package    Contenido Backend includes
 * @version    1.0.2
 * @author     Jan Lengowski
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.6
 * 
 * {@internal 
 *   created  2003-01-25
 *   modified 2008-06-27, Frederic Schneider, add security fix
 *   modified 2010-05-20, Murat Purc, removed request check during processing ticket [#CON-307]
 *
 *   $Id$:
 * }}
 * 
 */

if (!defined('CON_FRAMEWORK')) {
	die('Illegal call');
}


if ( isset($_GET['file']) ) {

    $area = $_GET['area'];

	$nav = new Contenido_Navigation;
	
    $sql = "SELECT
                idarea
            FROM
                ".$cfg["tab"]["area"]." AS a
            WHERE
                a.name = '".Contenido_Security::escapeDB($area, $db)."' OR
                a.parent_id = '".Contenido_Security::escapeDB($area, $db)."'
            ORDER BY
                idarea";

    $db->query($sql);

    $in_str = "";

    while ( $db->next_record() ) {
        $in_str .= $db->f('idarea') . ',';
    }

    $len = strlen($in_str)-1;
    $in_str = substr($in_str, 0, $len);
    $in_str = '('.$in_str.')';

    $sql = "SELECT
                b.location AS location,
                a.name AS name
            FROM
                ".$cfg["tab"]["area"]." AS a,
                ".$cfg["tab"]["nav_sub"]." AS b
            WHERE
                b.idarea IN ".$in_str." AND
                b.idarea = a.idarea AND
                b.level = 1 AND 
				b.online = 1
            ORDER BY
                b.idnavs";

    $db->query($sql);

    while ( $db->next_record() ) {
        
		/* Extract names from the XML document. */
		$caption = $nav->getName($db->f("location"));

        $tmp_area = $db->f("name");
        if ($tmp_area == 'style') {
            $sAction = '&action=style_edit';
        } else if ($tmp_area == 'js') {
            $sAction = '&action=js_edit';
        } else if ($tmp_area == 'htmltpl') {
            $sAction = '&action=htmltpl_edit';
        } else {
            $sAction = '';
        }
        if ($perm->have_perm_area_action($tmp_area))
        {
            # Set template data
            $tpl->set("d", "ID",        'c_'.$tpl->dyn_cnt);
            $tpl->set("d", "CLASS",     '');
            $tpl->set("d", "OPTIONS",   '');
            $tpl->set("d", "CAPTION",   '<a class="white" onclick="'.setHelpContext($sArea).'sub.clicked(this)" target="right_bottom" href="'.$sess->url("main.php?area=$tmp_area&frame=4".$sAction."&file=$file&tmp_file=$file").'">'.$caption.'</a>');
            $tpl->next();

        }
    }

    $tpl->set('s', 'COLSPAN', ($tpl->dyn_cnt * 2) + 2);
    
    if ($_GET['history'] == 'true') {
        $tpl->set('s', 'ACTIVATE_HISTORY', 'setHistory();');
    } else {
        $tpl->set('s', 'ACTIVATE_HISTORY', '');
    }

    # Generate the third
    # navigation layer
    $tpl->generate($cfg["path"]["templates"] . $cfg['templates']['file_subnav']);

} else {
    include ($cfg["path"]["contenido"].$cfg["path"]["templates"] . $cfg["templates"]["right_top_blank"]);
}

?>
