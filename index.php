<?php

define('_DJCK', true);
include "init.php";

$q = $_GET['q']; //diretorio base do meu controller/view
//impede que tentem acessar um diretorio acima e remove tentativas de injecao de javascript e outras tags na url
$q = str_replace(array('../', './', 'javascript:'), '', $q);
$q = strip_tags($q);
$q = strtolower($q);

// tenta encontrar a rota correta
if (empty($q)) {
  Router::doRoute('root');
} else {
  $params = array();
  $route = Router::parseUrl($q, $params);
  if ($route === false) {
    Router::doRoute('not_found');
  } elseif (empty($route)) {
    Router::doRoute('not_found', $params);
  } else {
    Router::doRoute($route, $params);
  }
}