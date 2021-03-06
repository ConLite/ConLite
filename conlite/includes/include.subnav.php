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


if ( isset($_GET['idcat']) ) {

	$nav = new Contenido_Navigation;
	
    $sql = "SELECT
                b.location AS location,
                a.name AS name,
                a.relevant AS relevant
            FROM
                ".$cfg["tab"]["area"]." AS a,
                ".$cfg["tab"]["nav_sub"]." AS b
            WHERE
                b.level = 1 AND
                b.idarea = a.idarea AND 
				b.online = 1
            ORDER BY
                b.idnavs";

    $db->query($sql);
    while ( $db->next_record() ) {

      /* Extract names from the XML document. */
      $caption = $nav->getName($db->f("location"));

        $tmp_area = $db->f("name");

        if ($perm->have_perm_area_action($tmp_area) || ($db->f("relevant") == 0))
        {
        # Set template data
            $tpl->set("d", "ID",        'c_'.$tpl->dyn_cnt);
            $tpl->set("d", "CLASS",     '');
            $tpl->set("d", "OPTIONS",   '');
            $tpl->set("d", "CAPTION",   '<a class="white" onclick="sub.clicked(this)" target="right_bottom" href="'.$sess->url("main.php?area=$tmp_area&frame=4&idcat=$idcat").'">'.$caption.'</a>');
            $tpl->next();
        }
    }

    $tpl->set('s', 'COLSPAN', ($tpl->dyn_cnt * 2) + 2);

    # Generate the third
    # navigation layer
    $tpl->generate($cfg["path"]["templates"] . $cfg["templates"]["subnav"]);

} else {
    include ($cfg["path"]["contenido"].$cfg["path"]["templates"] . $cfg["templates"]["right_top_blank"]);
}
?>