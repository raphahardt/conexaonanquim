<?php

/**
 * Classe para manipulação de cookies
 * 
 * Exemplo de uso:
 * <pre>
 * $cookie = new Cookie( 'nomeDoCookie', date()+3600 ); // expira daqui 3600 segundos (1 hora)
 * 
 * // define um valor
 * $cookie->set('valor');
 * 
 * // retorna o valor
 * $valor = $cookie->get();
 * </pre>
 * 
 * @author Raphael Hardt <sistema13@furacao.com.br>
 * @package helpers
 * @since 1.0 (8/4/13 Raphael)
 * @version 1.0 (8/4/13 Raphael)
 */
class Cookie {

  private $name;
  private $value;
  private $rawvalue;
  private $expire = 0;
  private $path = '/';
  private $domain;
  private $secure = false;
  private $httponly = false;

  public function __construct($name, $expire = 0, $path = null, $domain = null, $secure = null, $httponly = null) {

    if ($expire < 0)
      throw new Exception('Set a positive expire value');

    if (is_string($expire)) {
      $expire = strtotime($expire);
    }

    $this->name = $name;
    $this->expire = $expire;
    if (isset($path))
      $this->path = $path;
    if (isset($domain))
      $this->domain = $domain;
    if (isset($secure))
      $this->secure = $secure;
    if (isset($httponly))
      $this->httponly = $httponly;

    $this->value = $_COOKIE[$name];
  }

  public function set($value) {
    if (is_array($value)) {

      foreach ($value as $index => &$v) {
        $v = rawurlencode($v);

        setrawcookie($this->name . '[' . $index . ']', $v, $this->expire, $this->path, $this->domain, $this->secure, $this->httponly);
      }
      unset($v); // destroi referencia

      $this->value = $value;

      // deleta se existir um cookie com o mesmo nome mas um não array
      setrawcookie($this->name, '', $time, $this->path, $this->domain, $this->secure, $this->httponly);
    } else {
      // 	deleta se existir um cookie com o mesmo nome mas um array
      if (is_array($this->value)) {

        foreach ($this->value as $index => $v) {
          setrawcookie($this->name . '[' . $index . ']', '', $time, $this->path, $this->domain, $this->secure, $this->httponly);
        }
      }

      $this->value = rawurlencode($value);
      setrawcookie($this->name, $this->value, $this->expire, $this->path, $this->domain, $this->secure, $this->httponly);
    }
  }

  public function delete() {
    $time = time() - 10;
    if (is_array($this->value)) {

      foreach ($this->value as $index => $v) {
        setrawcookie($this->name . '[' . $index . ']', '', $time, $this->path, $this->domain, $this->secure, $this->httponly);
      }
    } else {
      setrawcookie($this->name, '', $time, $this->path, $this->domain, $this->secure, $this->httponly);
    }
    unset($this->value);
  }

  public function get() {
    return is_array($this->value) ? array_map('rawurldecode', $this->value) : rawurldecode($this->value);
  }

}