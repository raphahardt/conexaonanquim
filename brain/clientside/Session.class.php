<?php

/**
 * Classe manipulacao de colecoes de objetos
 *
 * @access public
 * @author Murilo Bezerril
 * @package models
 */
class Session {

  private static $_bdc;

  public function __construct($servidor = 1) {

    self::$_bdc = new ModelCollection();

    session_set_save_handler(
      array($this, 'open'), 
      array($this, 'close'), 
      array($this, 'read'), 
      array($this, 'write'), 
      array($this, 'destroy'), 
      array($this, 'gc')
    );

    register_shutdown_function('session_write_close');

    session_name(SESSION_NAME);

    // padroes para instancia de tabela
    $bdc = &self::$_bdc;
    $bdc->setTable(TBL_SESSION);
    $bdc->log = false;

    try {
      
      $sid = session_id();

      if (empty($sid)) { //soh invoca na primeira requisicao (quando a session nao foi criada ainda)
        self::generateId($servidor);
      }

      session_start();
      // BUG:
      // quando session_start() é chamada, ela executa algumas funções da classe Session, como read() e write(),
      // registradas pelo session_set_save_handler. dentro dessas funções, algumas instruções de banco
      // são realizadas. quando um erro de banco acontecia e um echo() ou um Exception era jogado na tela, 
      // o código simplesmente parava e caracteres estranhos eram mostrados na tela.
      // FIX: capturar o Exception das classes de banco e dar um session_destroy() antes de mostrar (já feito abaixo)
    } 
    catch (Exception $e) {
      session_destroy(); // arruma o bug do Exception aparecer com caracteres estranhos (9/4/13)
      echo ( $e->getMessage() );
    }
  }

  public function open($save_path, $session_name) {
    return true;
  }

  public function close() {
    return true;
  }

  public function read($sid) {
    $bdc = & self::$_bdc;

    $bdc->cleanSQL();
    $bdc->setFields('id', 'sid', 'hostname', 'timestamp', 'sessao');
    $bdc->setConstraint('sid', $sid);

    if ($bdc->select()) {
      return $bdc[0]['sessao'];
    }
    return '';
  }

  public function write($sid, $data) {
    global $user;

    $bdc = & self::$_bdc;
    $ip = GlobalFunc::getIp();
    $timestamp = time();

    // pega o id do usuario logado
    $uid = (is_object($user) && $user->id) ? $user->id : 0;

    // procura se o usuario está gravado nas sessions
    $bdc->cleanSQL();
    $bdc->setFields('id', 'sid');
    $bdc->setException('where (id=:i and id>0) or sid=:s', $uid, $sid);

    $numrows = $bdc->select();
    if ($numrows > 0) {
      // pega apenas o primeiro registro
      $result = $bdc[0];
    }

    // cabeçalho padrão para outras interações das condicionais abaixo..
    $bdc->cleanSQL();
    $bdc->setFields('id', 'sid', 'hostname', 'timestamp', 'sessao');

    // verifica quantos registros foram encontrados
    if ($numrows > 1) {

      // se tiver mais de um usuario logado ao mesmo tempo
      $bdc->setException('where id=:i or sid=:s', $result['ID'], $sid);
      // ...derruba sem dó
      $bdc->delete();

      // depois insere novamente com o mesmo sid e uid
      $bdc->unsetException();
      $bdc->setDatas($uid, $sid, $ip, $timestamp, $data);

      if ($bdc->insert()) {
        return true;
      }
    } elseif ($numrows == 1) {

      // se só tiver ele logado
      $bdc->setException('WHERE sid=:s', $result['SID']);
      $bdc->setDatas($uid, $sid, $ip, $timestamp, $data);

      if ($bdc->update()) {
        return true;
      }
    } else {

      // se ele não existir nas sessions
      $bdc->unsetException();
      $bdc->setDatas($uid, $sid, $ip, $timestamp, $data);

      if ($bdc->insert()) {
        return true;
      }
    }

    return false;
  }

  public function destroy($sid) {

    $bdc = & self::$_bdc;

    $bdc->cleanSQL();
    $bdc->setConstraint('sid', $sid);

    if ($bdc->delete()) {
      return true;
    }

    return false;
  }

  public function gc($lifetime) {

    $bdc = & self::$_bdc;

    $time = time() - $lifetime;

    $bdc->cleanSQL();
    $bdc->setException('where timestamp < :i', $time);

    if ($bdc->delete()) {
      return true;
    }

    return false;
  }

  /**
   * Re-gera o id da sessao. Deve ser chamado apos o login por uma questao
   * de seguranca.
   */
  public function regenerate($servidor = 1) {
    global $sid;
    //$bd = self::$_bd;
    $bdc = & self::$_bdc;

    $new_sid = self::generateId($servidor);
    $cookie_session = new Cookie(SESSION_NAME);
    $cookie_session->set($new_sid);
    //$_COOKIE[SESSION_NAME] = $new_sid;
    //setcookie(SESSION_NAME, $new_sid, 0, '/');


    $bdc->cleanSQL();
    $bdc->setField('sid', 's');
    $bdc->setConstraint('timestamp', $time);
    $bdc->setData('sid', $sid);
    $bdc->update();
  }

  public static function getId() {
    global $session;
    if (!isset($session)) {
      $session = new Session;
    }
    return session_id();
  }

  public static function setId($sid) {
    global $session;
    if (!isset($session)) {
      session_id($sid);
    }
  }

  private static function generateId($servidor = 1) {
    $ip = GlobalFunc::getIp();
    $ip = str_replace('/', '-', $ip);
    $ip = str_replace(array('.', ':'), '', $ip);
    $sid = $servidor . '-' . uniqid() . '-' . $ip . '-';
    $rand = rand(3, 6);

    for ($i = 0; $i < $rand; $i++) {
      $sid .= rand(5, 17);
    }

    session_id($sid); //gera uma chave unica para cada visitante
    return $sid;
  }

}