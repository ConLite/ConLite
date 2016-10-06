<?php
/**
 * File:
 * config.autoloader.php
 *
 * @package Plugins
 * @subpackage Newsletter
 * @version $Rev: 283 $
 * @since 2.0
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @copyright 2012 CL-Team
 * @link http://www.conlite.org
 *
 * $Id: config.autoloader.php 283 2014-01-09 14:48:38Z oldperl $
 */

$sAutoloadClassPath = 'conlite/plugins/newsletter/classes/';
return array(
    'RecipientGroupCollection' => $sAutoloadClassPath.'class.newsletter.groups.php',
    'RecipientGroup' => $sAutoloadClassPath.'class.newsletter.groups.php',
    'RecipientGroupMemberCollection' => $sAutoloadClassPath.'class.newsletter.groups.php',
    'RecipientGroupMember' => $sAutoloadClassPath.'class.newsletter.groups.php',
    'cNewsletterJobCollection' => $sAutoloadClassPath.'class.newsletter.jobs.php',
    'cNewsletterJob' => $sAutoloadClassPath.'class.newsletter.jobs.php',
    'cNewsletterLogCollection' => $sAutoloadClassPath.'class.newsletter.logs.php',
    'cNewsletterLog' => $sAutoloadClassPath.'class.newsletter.logs.php',
    'NewsletterCollection' => $sAutoloadClassPath.'class.newsletter.php',
    'Newsletter' => $sAutoloadClassPath.'class.newsletter.php',
    'RecipientCollection' => $sAutoloadClassPath.'class.newsletter.recipients.php',
    'Recipient' => $sAutoloadClassPath.'class.newsletter.recipients.php',
);
?>