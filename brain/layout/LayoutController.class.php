<?php

djimport('plugins.smarty.Smarty');

class LayoutController extends Smarty {

  private $page_header = 'header.tpl';
  private $page_footer = 'footer.tpl';
  protected $view;
  private $page_title = '';
  private $page_breadcrumbs = array('Página inicial' => '');
  private $page_js = array();
  private $json_prefixed = false;

  public function __construct($view) {
    // inicia o smarty normalmente
    parent::__construct();

    // define os padrões
    $this->debugging = false;
    $this->caching = false;
    $this->cache_lifetime = 120;

    $this->setTemplateDir(array(DJCK_BASE . 'www', DJCK_BASE . 'app'.DS.'view'));
    $this->setCompileDir(DJCK_SMARTY_DIR. 'templates_c');
    $this->setCacheDir(DJCK_SMARTY_DIR. 'cache');
    $this->setConfigDir(DJCK_SMARTY_DIR. 'config');
    
    // definicoes principais
    $this->assign('site', array(
        'title' => DJCK_SITE_NAME,
        'copyright' => DJCK_SITE_COPYRIGHT,
        'description' => DJCK_SITE_DESCRIPTION,
        'keywords' => DJCK_SITE_KEYWORDS,
        'owner' => DJCK_SITE_OWNER,
        'URL' => DJCK_URL,
        'fullURL' => DJCK_SITE_URL,
        
        'facebookID' => DJCK_FACEBOOK_APP_ID,
    ));
    
    // compile the main css
    $less = new lessc();
    $less->setVariables(array(
      "siteURL" => '"'.DJCK_SITE_URL.'"'
    ));
    $less->checkedCompile( DJCK_BASE . 'www'.DS.'css'.DS. 'main.less', DJCK_BASE . 'www'.DS.'css'.DS.'main.css');

    // pasta padrao que o smarty vai buscar as paginas
    $this->view = $view;

    // inicializa
    $this->initialize();

  }

  final private function initialize() {
    return;
    global $sid, $token;
    global $menu;
    
    $user = $_SESSION['user'];

    if ($this->isLogged()) {

      $this->assign('usuario_logged_in', Usuario::isLoggedIn());
      $this->assign('sid', $sid);
      $this->assign('STOKEN', $token);
      $this->assign('username', $user->login);

      //$messages = Layout::get_messages();
      //$this->assign('messages', $messages);
      $this->assign('SID', $sid);

      //variaveis globais do smarty utilizadas nos templates
      $this->assign('siteName', WEBSITE_NAME);
      $this->assign('siteOwner', WEBSITE_OWNER);
      $this->assign('siteURL', WEBSITE_URL);
      $this->assign('tituloModulo', WEBSITE_MODULO);

      if (!$this->getTemplateVars('msg')) {
        $this->assign('msg', $_SESSION['messages']['msg']);
        $this->assign('msgType', $_SESSION['messages']['success'] ? 'success' : 'warning');

        unset($_SESSION['messages']);
      }

      // menu
      $this->assign('mainMenu', $menu->show());

      /* $less = new lessc;
        $lesscompiled = $less->compileFile(ABSPATH.'/css/estilos.less');

        $this->assign('estilo', htmlentities($lesscompiled)); */

      // modulos html predefinidos
      //$this->assign('titulo_pagina', ABSPATH.'/templates/modulos/titulo_pagina.html');
      //$this->assign('subtitulo', ABSPATH.'/templates/modulos/subtitulo.html');
      $this->assign('botao_home', ABSPATH.'/templates/modulos/botao_home.tpl');
      $this->assign('fw_toolbar', ABSPATH . '/templates/modulos/fw-toolbar.tpl');
      $this->assign('fw_selectbox', ABSPATH . '/templates/modulos/fw-selectbox.tpl');
      $this->assign('fwInputElement', ABSPATH . '/templates/modulos/fwInputElement.tpl');

      $this->assign('basePath', BASE_PATH);

      $this->assign('modoDev', MODO_DEV);
      $this->assign('js', ABSPATH . '/templates/js.tpl');
      $this->assign('selectbox', ABSPATH . '/templates/modulos/selectbox.tpl');
      $this->assign('submenu', ABSPATH . '/templates/modulos/submenu.tpl');
      $this->assign('fieldbox', ABSPATH . '/templates/modulos/field.tpl');
    }
  }
  
  protected function show404($supress_header = false) {
    $this->view = 'not_found';
    $this->showContents('/index.tpl', $supress_header);
  }

  protected function showContents($page = '/index.tpl', $supress_header = false) {

    // controla o buffer
    ob_start();

    try {
      // header
      //require_once ABSPATH.'/templates/pre-html.tpl.php';
      if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        // verifica se é uma chamada de conteudo por ajax, se for, não mostrar os headers
        $supress_header = true;
      }
      
      // define variaveis
      $this->assign('page', 
        array(
          'title' => $this->page_title
        )
      ); //titulo
      if (!empty($this->page_js) && is_array($this->page_js)) { //javascript
        $this->assign('jsfile', WEBSITE_URL . '/min/?b=js&amp;f=' . implode(',', $this->page_js));
      }
      if (is_array($this->page_breadcrumbs) && count($this->page_breadcrumbs) > 1) { //breadcrumb
        $bread = array();
        foreach ($this->page_breadcrumbs as $t => $b) {
          $bread[] = array('title' => $t, 'url' => $b);
        }
        $this->assign('breadcrumb', $bread);
      }

      if (!$supress_header) {
        $this->display($this->page_header);
      }


      // mostra o conteudo da pagina
      $this->display($this->view . $page);

      // footer
      //require_once ABSPATH.'/templates/pos-html.tpl.php';
      if (!$supress_header) {
        $this->display($this->page_footer);
      }

      // mostra efetivamente tudo
      ob_end_flush();
    } catch (SmartyException $e) {
      // descarta tudo que foi mostrado na tela (medida de segurança)
      ob_end_clean();

      // mostra erro
      $trace = $e->getTrace();
      $trace = end($trace);
      echo '<b>' . $e->getMessage() . '</b> em ' . $trace['class'] . $trace['type'] . $trace['function'] . ' em ' . $trace['line'] . ' (' . basename($e->getFile()) . ', linha ' . $e->getLine() . ')';
      // TODO: mandar erro por email, e pro usuario mostrar erro amigavel
    }
  }
  
  protected function addBreadcrumb($title, $url) {
    $this->page_breadcrumbs[$title] = $url;
  }

  protected function setPageTitle($title) {
    $this->page_title = (string) $title;
  }

  protected function setJavascripts($jss) {
    $args = func_get_args();
    $count = count($args);

    if ($count > 1) {
      // se tiver mais de 1 argumento
      $this->page_js = array_merge($this->page_js, $args);
    } else if (is_string($jss)) {
      // se tiver apenas 1 argumento e ele for string
      $this->page_js[] = $jss;
    } else if (is_array($jss)) {
      // se tiver apenas 1 argumento e ele for array
      $this->page_js = array_merge($this->page_js, $jss);
    } else {
      // erro: precisa passar pelo menos 1 argumento
    }
  }

  protected function setJSONPrefixed($val) {
    $this->json_prefixed = (bool) $val;
  }

  protected function showResponseAuth($success, $msg = '', $referer = 'home') {

    if (!$this->authToken()) {
      $success = false;
      $msg = 'Acesso negado';
      $referer = 'forbidden';
    }

    $this->showResponse($success, $msg, $referer);
  }

  protected function showResponse($success, $msg = '', $referer = 'home') {

    // verifica se é um ajax
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

      // verifica se foi passado uma variavel verificadora
      if ($_POST['ajax'] != true) {
        $this->_showJSON(
                array('success' => false, 'msg' => 'Acesso negado.')
        );
        return;
      }

      // se ok, retornar a msg
      $this->_showJSON(
              array('success' => $success, 'msg' => (string) $msg)
      );
    } else {
      // não é um ajax, entao mandar como redirecionamento
      $this->_setMessage($success, (string) $msg);
      if (strpos($referer, '?') !== false) {
        list($referer, $querystr) = explode('?', $referer);
      }
      GlobalFunc::go_to($referer, $querystr, true);
    }
  }

  protected function showData($data) {
    // verifica se é um ajax
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

      // autentica o token
      if (!$this->authToken()) {
        $data = array();
      }

      // se ok, retornar a msg
      $this->_showJSON($data);
    } else {
      // quando for retorno de dados, ir somente por ajax
      // se não for ajax, dar pagina nao encontrada
      GlobalFunc::go_to('not_found');
    }
  }

  protected function authToken() {
    global $token;

    return ($token === $_GET['token'] || $token === $_POST['token']);
  }

  private function _showJSON($data) {
    // manda header de json
    header('Content-Type: application/json');
    echo GlobalFunc::jsonEncode($data, $this->json_prefixed);
  }

  private function _setMessage($success = true, $msg = '') {
    // define a mensagem que vai aparecer na prox pagina
    $_SESSION['messages']['msg'] = $msg;
    $_SESSION['messages']['success'] = $success;
  }

}