<?php

/**
 * Carrega as variaveis globais para uso geral do sistema
 * @author Raphael Hardt
 * @version 1.0
 */

//registra as funcoes de manipulacao de sessao e a inicia na pagina
/*global $session, $sid, $token;

if(isset($_COOKIE[SESSION_NAME]) && !empty($_COOKIE[SESSION_NAME])) 
	Session::setId($_COOKIE[SESSION_NAME]);

//cria a sessao se ela ainda nao existir
if(!isset($session)) $session = new Session(rand(0,9));

//regera a sessao se o usuario acabou de logar (motivos de seguranca)
if(isset($_SESSION['logged']) && $_SESSION['logged'] === TRUE) {
	$session->regenerate(rand(0,9));
	$_SESSION['logged'] = NULL;
	unset($_SESSION['logged']);
}

//cria um token de seguranca
if(!isset($_SESSION['token'])) {
	$_SESSION['token'] = md5(uniqid()).rand(5, 15).rand(0, 5);
}

$token = $_SESSION['token'];

//seta o sid global da sessao
$sid = Session::getId();

//carrega o usuario (se ele estiver logado)
//Usuario::load();*/

?>