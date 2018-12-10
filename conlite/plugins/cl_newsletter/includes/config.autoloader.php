<?php
$sAutoloadClassPath = strstr(dirname(dirname(__FILE__)), "conlite/plugins")."/classes/";
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
    'Recipient' => $sAutoloadClassPath.'class.newsletter.recipients.php'
);
?>