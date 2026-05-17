<?php

/**
 * File:
 * class.user.php
 *
 * Description:
 *  cApi class
 * 
 * @package Core
 * @subpackage cApi
 * @version $Rev$
 * @since 2.0
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright (c) 2015, conlite.org
 * @license http://www.gnu.de/documents/gpl.en.html GPL v3 (english version)
 * @license http://www.gnu.de/documents/gpl.de.html GPL v3 (deutsche Version)
 * @link http://www.conlite.org ConLite.org
 *
 * @deprecated since 3.0.0, use User and UserCollection instead
 * 
 * $Id$
 */

use ConLite\Conlite\UserCollection;
use ConLite\Conlite\User;

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

/**
 * @deprecated since 3.0.0
 * @uses \ConLite\Conlite\UserCollection
 */
class cApiUserCollection extends UserCollection {}

/**
 * @deprecated since 3.0.0
 * @uses \ConLite\Conlite\User
 */
class cApiUser extends User {}