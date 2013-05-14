<?php

/**
 * Classe de manipulacao de Rotas
 * @author Murilo Bezerril
 * @version 1.0
 * @package core
 * @subpackage router
 */
abstract class Router {

  public static $route_tree = array();
  public static $routes = array();
  private static $route_done = false;

  /**
   * Conecta a rota
   * 
   * @global array $_ROUTE
   * @param array $route
   * @param bollean $cachedTree
   * @return void 
   */
  public static function connect($route, $cachedTree = false) {
    $url = &$route['#url'];
    $params = &$route['#params'];

    self::$routes[$url] = &$route;

    if ($cachedTree == true) {
      return;
    }

    $url_arr = explode('/', $url);
    $route_tree = &self::$route_tree;

    foreach ($url_arr as $url_item) {
      if (empty($url_item))
        continue;
      if ($url_item[0] == ':') {
        if (!isset($params[$url_item])) { // param on path was not specified on the param list
          trigger_error('Missing param <b>' . $url_item . '</b> for route <b>"' . $url . '"</b>', E_USER_ERROR);
        }
        $url_index = ':';
      } else {
        $url_index = $url_item;
      }
      if (!isset($route_tree[$url_index])) {
        $route_tree[$url_index] = array();
        if ($url_index == ':') { // we add a couple of private keys so our app knows what param it is dealing with
          $route_tree[$url_index]['__pattern'] = $params[$url_item];
          $route_tree[$url_index]['__name'] = $url_item;
        }
      } else if ($url_index == ':') { // param of that level already set
        if ($url_item != $route_tree[$url_index]['__name']) { // and its not the same we had on our tree already
          trigger_error('A route param already exists with a different name.', E_USER_ERROR);
        }
      }

      // move the pointer to the next level
      $route_tree = & $route_tree[$url_index];
    }
  }

  /**
   * Tries to guess the correct route to use for the given url.
   * @param <string> $url Url to be 'routed'
   * @param <array> &$extras Return array with anything that might still exist on the URL once it's 'routed'
   * @return <bool|string> Returns false if the route does not exist or the route itself as a string.
   */
  public static function parseUrl($url, &$params = array()) {
    
    $pieces = explode('/', $url);
    $route_tree = & self::$route_tree;
    $route_arr = self::_parseUrlAux($pieces, $route_tree, $params); // will return false or the route found

    if ($route_arr !== false) {
      $route = implode('/', $route_arr);
    }

    if ($route_arr === false || (!empty($route) && !isset(self::$routes[$route]))) { // guessed route does not exist!!
      return false;
    }

    return $route;
  }

  /**
   *
   * @param type $pieces
   * @param type $currentTreeLevel
   * @param type $params
   * @param type $index
   * @param type $currentRoute
   * @return boolean 
   */
  private static function _parseUrlAux(&$pieces, &$currentTreeLevel, &$params = array(), $index = 0, $currentRoute = array()) {

    if (empty($pieces)) { // no URL!
      return false;
    }

    if ($index >= count($pieces)) { // no more pieces to check
      return $currentRoute;
    }

    $url_piece = $pieces[$index];
    if (is_null($currentTreeLevel)) {
      return $currentRoute;
    } else if (isset($currentTreeLevel[$url_piece])) { // that static node exists in our tree		
      $currentRoute[] = $url_piece;
      return self::_parseUrlAux($pieces, $currentTreeLevel[$url_piece], $params, $index + 1, $currentRoute);
    } else { // no static node with that name
      if (isset($currentTreeLevel[':']) && preg_match($currentTreeLevel[':']['__pattern'], $url_piece)) { // it may be an explicit parameter
        $currentRoute[] = $currentTreeLevel[':']['__name'];
        $params[$currentTreeLevel[':']['__name']] = $url_piece;
        return self::_parseUrlAux($pieces, $currentTreeLevel[':'], $params, $index + 1, $currentRoute);
      } else { // or not, so let's group them and let the controller decide
        for ($i = $index; $i < count($pieces); $i++) {
          $params[] = $pieces[$i];
        }

        return $currentRoute;
      }
    }
  }

  /**
   * Executes a route based on the path it received
   * @param <string> $path Path of my route
   * @param <array> $params Params to be passed on to the controller
   */
  public static function doRoute($path, $params = array()) {
    
    // verifica se a rota ta sendo feita de novo, se estiver, usar redirect
    if (self::$route_done) {
      header('Location: ' . DJCK_SITE_URL . $_GET['q']);
      self::$route_done = false;
      return;
    }
    
    $type = self::$routes[$path]['#type'];
    $extras = self::$routes[$path]['#extras'];
    $action = self::$routes[$path]['#action'];

    if ($type == 'link') { //se a rota eh um link para uma outra
      $path = self::$routes[$path]['#path'];
    }
    
    // indica q a rota ja foi feita para nao ser feita novamente
    self::$route_done = true;

    $visibility = self::$routes[$path]['#visibility'];
    if ($visibility == 'hidden' && $_GET['q'] == $path) { //impede que acessem a rota se ela for invisivel
      return self::doRoute('forbidden');
    }
    
    $class_name = self::$routes[$path]['#controller'];

    //require_once ABSPATH . '/app/controller/' . $file;
    Loader::register($class_name, DJCK_BASE . 'app'.DS.'controller'.DS.$class_name.'.class.php');

    //acerto o path do meu view
    $dir = $path;

    if (is_null($action) && !empty($params) && isset($params[0])) {
      //extrai a 'acao'
      $action = $params[0];
    }

    // verifica se a classe existe
    if (class_exists($class_name)) {

      $class = new $class_name($dir);

      // verifica se existe ação ou se o controller é NotFound
      if (!empty($action) && $class_name != 'NotFoundController') {
        if (method_exists($class_name, $action)) {
          $class->$action($params, $extras);
        } else {
          self::doRoute('not_found');
        }
      } else {
        // checa e instancia o método index da classe
        if (method_exists($class_name, 'index')) {
          $class->index($params, $extras);
        } else {
          self::doRoute('not_found');
        }
      }
    }
  }

}