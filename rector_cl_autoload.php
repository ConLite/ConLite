<?php
if (!defined("CON_FRAMEWORK")) {
    define("CON_FRAMEWORK", true);
}
include_once('conlite/classes/class.autoload.php');
$cfg = [
    'path' => [
        'config' => dirname(__FILE__) . '/data/config/production/',
        'contenido' => dirname(__FILE__) . '/conlite/',
    ],
];

cAutoload::initialize($cfg);
