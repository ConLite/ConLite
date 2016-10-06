<?php
/**
 * Project:
 * Contenido Content Management System
 *
 * Description:
 * Module history
 *
 * Requirements:
 * @con_php_req 5.0
 *
 *
 * @package    Contenido Backend classes
 * @version    1.2
 * @author     Timo A. Hummel
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since contenido release <= 4.6
 *
 * {@internal
 *   created  2003-12-14
 *   modified 2008-06-30, Dominik Ziegler, add security fix
 *   modified 2008-10-03, Oliver Lohkemper, modified UploadCollection::delete()
 *   modified 2008-10-03, Oliver Lohkemper, add CEC in UploadCollection::store()
 *   modified 2011-03-14, Murat Purc, adapted to new GenericDB, partly ported to PHP 5, formatting
 *
 *   $Id: class.upload.php 347 2015-09-18 13:42:15Z oldperl $:
 * }}
 *
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

class UploadCollection extends cApiUploadCollection {}

class UploadItem extends cApiUpload {}

?>