<?php

//configurando o caminho absoluto
define('DS', DIRECTORY_SEPARATOR);

// doc root, to base and root dirs
$docroot = $_SERVER['DOCUMENT_ROOT'];
if (strstr($docroot, '/') !== false) {
  $docroot = str_replace('/', DS, $docroot);
}

// define root hard directory
$parts = explode(DS, dirname(__FILE__));
//array_pop($parts);
define('URL_BASE_ABS', implode(DS, $parts) . DS);

// define root base dir, for links
$parts = str_ireplace($docroot, '', URL_BASE_ABS);
$parts = str_replace(DS, '/', $parts);
define('URL_BASE', $parts);

// define current hard dir
define('URL_CURRENT_ABS', dirname(realpath($_SERVER['SCRIPT_FILENAME'])) . DS);

// define current dir, for links
$curdir = dirname($_SERVER['PHP_SELF']);
$curdir = str_replace(DS, '/', $curdir);
define('URL_CURRENT', $curdir . '/');

// chama uma classe ou arquivo php, chamando dessa forma: app.helper.bd.BD-class  irá chamar app/helper/bd/BD.class.php
function _load($string, $file) {
  $path = explode('.', $string);
  //$last = array_pop($path);

  list($filetype, $file) = explode(':', $file);

  switch ($filetype) {
    case 'class':
    case 'model':
      $file .= '.class';
      break;
    case 'controller':
      $file .= '_controller';
      break;
    default:
      $file = $filetype;
  }
  if (substr($file, -4) != '.php')
    $file .= '.php';

  $path[] = $file;

  include URL_BASE_ABS . implode(DS, $path);
}

function _path($string, $file = '') {
  $path = explode('.', $string);

  if (!empty($file))
    $path[] = $file;

  return URL_BASE_ABS . implode(DS, $path);
}

// Configurando variaveis globais
//_load('config', 'globais');

if (!defined('IS_BOT')) {
  //rotas
  //_load('config', 'routes');
}