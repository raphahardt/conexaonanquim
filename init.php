<?php

// protege o arquivo
(_DJCK === true) or exit;

// inicia todo o sistema
define('DS', DIRECTORY_SEPARATOR);

$basedir = dirname(__FILE__).DS;
$root = str_replace('/', DS, $_SERVER['DOCUMENT_ROOT']);
$baseurl = str_replace(DS, '/', str_ireplace($root, '', $basedir));

define('DJCK_BASE', $basedir);
define('DJCK_URL', $baseurl);

include DJCK_BASE . 'config'. DS . 'brain.php';
include DJCK_BASE . 'config'. DS . 'definitions.php';
include DJCK_BASE . 'config'. DS . 'routes.php';
//include DJCK_BASE . 'config'. DS . 'menus.php';