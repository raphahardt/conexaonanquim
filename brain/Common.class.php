<?php

abstract class Common {

  const max_link_length = 100;

  /**
   * Pula para uma pagina determinada
   * @param <string> $page Pagina para onde redirecionar (pode ser uma pagina da web com http:// ou uma pagina interna, definida em routes.php)
   * @param <string> $query Query a ser apendada a url
   */
  public static function go_to($page, $query = '', $header = false) {
    if (preg_match('/http[s]?\:\/\/(:?www\.)?[A-Z0-9\.\-\_\%]+[A-Z0-9]{2,4}/i', $page)) {
      header('Location: ' . $page);
      exit();
    } else {
      $url = WEBSITE_URL . '/' . $page;
      if (!empty($query)) {
        $url .= '?' . $query;
      }
      if (!$header) {
        echo '<script type="text/javascript">window.location.href="' . $url . '"</script>';
      } else {
        session_write_close();
        header('Location: ' . $url);
      }
      exit();
    }
  }

  public static function jsonEncode($obj, $addPrefix = true) {

    $prefix = '';

    if ($addPrefix) {
      $prefix = "while(1);\n\n\n\n";
    }

    return $prefix . json_encode($obj);
  }

  public static function arrayValues($arr) {
    $arr = array_values($arr);
    foreach ($arr as $key => $val)
      if (is_array($val))
        $arr[$key] = self::arrayValues($val);

    return $arr;
  }

  /**
   * FUNCAO COPIADA DE: WordPress
   * Converts all accent characters to ASCII characters.
   *
   * If there are no accent characters, then the string given is just returned.
   *
   * @since 1.2.1
   *
   * @param string $string Text that might have accent characters
   * @return string Filtered string with replaced "nice" characters.
   * @author WordPress.org
   */
  public static function removeAccents($string) {
    if (!preg_match('/[\x80-\xff]/', $string))
      return $string;

    if (self::seemsUtf8($string)) {
      $chars = array(
          // Decompositions for Latin-1 Supplement
          chr(195) . chr(128) => 'A', chr(195) . chr(129) => 'A',
          chr(195) . chr(130) => 'A', chr(195) . chr(131) => 'A',
          chr(195) . chr(132) => 'A', chr(195) . chr(133) => 'A',
          chr(195) . chr(135) => 'C', chr(195) . chr(136) => 'E',
          chr(195) . chr(137) => 'E', chr(195) . chr(138) => 'E',
          chr(195) . chr(139) => 'E', chr(195) . chr(140) => 'I',
          chr(195) . chr(141) => 'I', chr(195) . chr(142) => 'I',
          chr(195) . chr(143) => 'I', chr(195) . chr(145) => 'N',
          chr(195) . chr(146) => 'O', chr(195) . chr(147) => 'O',
          chr(195) . chr(148) => 'O', chr(195) . chr(149) => 'O',
          chr(195) . chr(150) => 'O', chr(195) . chr(153) => 'U',
          chr(195) . chr(154) => 'U', chr(195) . chr(155) => 'U',
          chr(195) . chr(156) => 'U', chr(195) . chr(157) => 'Y',
          chr(195) . chr(159) => 's', chr(195) . chr(160) => 'a',
          chr(195) . chr(161) => 'a', chr(195) . chr(162) => 'a',
          chr(195) . chr(163) => 'a', chr(195) . chr(164) => 'a',
          chr(195) . chr(165) => 'a', chr(195) . chr(167) => 'c',
          chr(195) . chr(168) => 'e', chr(195) . chr(169) => 'e',
          chr(195) . chr(170) => 'e', chr(195) . chr(171) => 'e',
          chr(195) . chr(172) => 'i', chr(195) . chr(173) => 'i',
          chr(195) . chr(174) => 'i', chr(195) . chr(175) => 'i',
          chr(195) . chr(177) => 'n', chr(195) . chr(178) => 'o',
          chr(195) . chr(179) => 'o', chr(195) . chr(180) => 'o',
          chr(195) . chr(181) => 'o', chr(195) . chr(182) => 'o',
          chr(195) . chr(182) => 'o', chr(195) . chr(185) => 'u',
          chr(195) . chr(186) => 'u', chr(195) . chr(187) => 'u',
          chr(195) . chr(188) => 'u', chr(195) . chr(189) => 'y',
          chr(195) . chr(191) => 'y',
          // Decompositions for Latin Extended-A
          chr(196) . chr(128) => 'A', chr(196) . chr(129) => 'a',
          chr(196) . chr(130) => 'A', chr(196) . chr(131) => 'a',
          chr(196) . chr(132) => 'A', chr(196) . chr(133) => 'a',
          chr(196) . chr(134) => 'C', chr(196) . chr(135) => 'c',
          chr(196) . chr(136) => 'C', chr(196) . chr(137) => 'c',
          chr(196) . chr(138) => 'C', chr(196) . chr(139) => 'c',
          chr(196) . chr(140) => 'C', chr(196) . chr(141) => 'c',
          chr(196) . chr(142) => 'D', chr(196) . chr(143) => 'd',
          chr(196) . chr(144) => 'D', chr(196) . chr(145) => 'd',
          chr(196) . chr(146) => 'E', chr(196) . chr(147) => 'e',
          chr(196) . chr(148) => 'E', chr(196) . chr(149) => 'e',
          chr(196) . chr(150) => 'E', chr(196) . chr(151) => 'e',
          chr(196) . chr(152) => 'E', chr(196) . chr(153) => 'e',
          chr(196) . chr(154) => 'E', chr(196) . chr(155) => 'e',
          chr(196) . chr(156) => 'G', chr(196) . chr(157) => 'g',
          chr(196) . chr(158) => 'G', chr(196) . chr(159) => 'g',
          chr(196) . chr(160) => 'G', chr(196) . chr(161) => 'g',
          chr(196) . chr(162) => 'G', chr(196) . chr(163) => 'g',
          chr(196) . chr(164) => 'H', chr(196) . chr(165) => 'h',
          chr(196) . chr(166) => 'H', chr(196) . chr(167) => 'h',
          chr(196) . chr(168) => 'I', chr(196) . chr(169) => 'i',
          chr(196) . chr(170) => 'I', chr(196) . chr(171) => 'i',
          chr(196) . chr(172) => 'I', chr(196) . chr(173) => 'i',
          chr(196) . chr(174) => 'I', chr(196) . chr(175) => 'i',
          chr(196) . chr(176) => 'I', chr(196) . chr(177) => 'i',
          chr(196) . chr(178) => 'IJ', chr(196) . chr(179) => 'ij',
          chr(196) . chr(180) => 'J', chr(196) . chr(181) => 'j',
          chr(196) . chr(182) => 'K', chr(196) . chr(183) => 'k',
          chr(196) . chr(184) => 'k', chr(196) . chr(185) => 'L',
          chr(196) . chr(186) => 'l', chr(196) . chr(187) => 'L',
          chr(196) . chr(188) => 'l', chr(196) . chr(189) => 'L',
          chr(196) . chr(190) => 'l', chr(196) . chr(191) => 'L',
          chr(197) . chr(128) => 'l', chr(197) . chr(129) => 'L',
          chr(197) . chr(130) => 'l', chr(197) . chr(131) => 'N',
          chr(197) . chr(132) => 'n', chr(197) . chr(133) => 'N',
          chr(197) . chr(134) => 'n', chr(197) . chr(135) => 'N',
          chr(197) . chr(136) => 'n', chr(197) . chr(137) => 'N',
          chr(197) . chr(138) => 'n', chr(197) . chr(139) => 'N',
          chr(197) . chr(140) => 'O', chr(197) . chr(141) => 'o',
          chr(197) . chr(142) => 'O', chr(197) . chr(143) => 'o',
          chr(197) . chr(144) => 'O', chr(197) . chr(145) => 'o',
          chr(197) . chr(146) => 'OE', chr(197) . chr(147) => 'oe',
          chr(197) . chr(148) => 'R', chr(197) . chr(149) => 'r',
          chr(197) . chr(150) => 'R', chr(197) . chr(151) => 'r',
          chr(197) . chr(152) => 'R', chr(197) . chr(153) => 'r',
          chr(197) . chr(154) => 'S', chr(197) . chr(155) => 's',
          chr(197) . chr(156) => 'S', chr(197) . chr(157) => 's',
          chr(197) . chr(158) => 'S', chr(197) . chr(159) => 's',
          chr(197) . chr(160) => 'S', chr(197) . chr(161) => 's',
          chr(197) . chr(162) => 'T', chr(197) . chr(163) => 't',
          chr(197) . chr(164) => 'T', chr(197) . chr(165) => 't',
          chr(197) . chr(166) => 'T', chr(197) . chr(167) => 't',
          chr(197) . chr(168) => 'U', chr(197) . chr(169) => 'u',
          chr(197) . chr(170) => 'U', chr(197) . chr(171) => 'u',
          chr(197) . chr(172) => 'U', chr(197) . chr(173) => 'u',
          chr(197) . chr(174) => 'U', chr(197) . chr(175) => 'u',
          chr(197) . chr(176) => 'U', chr(197) . chr(177) => 'u',
          chr(197) . chr(178) => 'U', chr(197) . chr(179) => 'u',
          chr(197) . chr(180) => 'W', chr(197) . chr(181) => 'w',
          chr(197) . chr(182) => 'Y', chr(197) . chr(183) => 'y',
          chr(197) . chr(184) => 'Y', chr(197) . chr(185) => 'Z',
          chr(197) . chr(186) => 'z', chr(197) . chr(187) => 'Z',
          chr(197) . chr(188) => 'z', chr(197) . chr(189) => 'Z',
          chr(197) . chr(190) => 'z', chr(197) . chr(191) => 's',
          // Euro Sign
          chr(226) . chr(130) . chr(172) => 'E',
          // GBP (Pound) Sign
          chr(194) . chr(163) => '');

      $string = strtr($string, $chars);
    } else {
      // Assume ISO-8859-1 if not UTF-8
      $chars['in'] = chr(128) . chr(131) . chr(138) . chr(142) . chr(154) . chr(158)
              . chr(159) . chr(162) . chr(165) . chr(181) . chr(192) . chr(193) . chr(194)
              . chr(195) . chr(196) . chr(197) . chr(199) . chr(200) . chr(201) . chr(202)
              . chr(203) . chr(204) . chr(205) . chr(206) . chr(207) . chr(209) . chr(210)
              . chr(211) . chr(212) . chr(213) . chr(214) . chr(216) . chr(217) . chr(218)
              . chr(219) . chr(220) . chr(221) . chr(224) . chr(225) . chr(226) . chr(227)
              . chr(228) . chr(229) . chr(231) . chr(232) . chr(233) . chr(234) . chr(235)
              . chr(236) . chr(237) . chr(238) . chr(239) . chr(241) . chr(242) . chr(243)
              . chr(244) . chr(245) . chr(246) . chr(248) . chr(249) . chr(250) . chr(251)
              . chr(252) . chr(253) . chr(255);

      $chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

      $string = strtr($string, $chars['in'], $chars['out']);
      $double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
      $double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
      $string = str_replace($double_chars['in'], $double_chars['out'], $string);
    }

    return $string;
  }

  /**
   * FUNCAO COPIADA DE: WordPress
   * Checks to see if a string is utf8 encoded.
   *
   * NOTE: This function checks for 5-Byte sequences, UTF8
   *       has Bytes Sequences with a maximum length of 4.
   *
   * @author bmorel at ssi dot fr (modified)
   * @since 1.2.1
   *
   * @param string $str The string to be checked
   * @return bool True if $str fits a UTF-8 model, false otherwise.
   */
  public static function seemsUtf8($str) {
    $length = strlen($str);
    for ($i = 0; $i < $length; $i++) {
      $c = ord($str[$i]);
      if ($c < 0x80)
        $n = 0;# 0bbbbbbb
      elseif (($c & 0xE0) == 0xC0)
        $n = 1;# 110bbbbb
      elseif (($c & 0xF0) == 0xE0)
        $n = 2;# 1110bbbb
      elseif (($c & 0xF8) == 0xF0)
        $n = 3;# 11110bbb
      elseif (($c & 0xFC) == 0xF8)
        $n = 4;# 111110bb
      elseif (($c & 0xFE) == 0xFC)
        $n = 5;# 1111110b
      else
        return false;# Does not match any model
      for ($j = 0; $j < $n; $j++) { # n bytes matching 10bbbbbb follow ?
        if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80))
          return false;
      }
    }
    return true;
  }

  /**
   * 'explode' com multiplos delimitadores
   * @param array $delimitadores 
   *  Os delimitadores em que explodir a string
   * @param string $string 
   *  A string a ser quebrada em partes
   * @param string $delimitadorPadrao
   *  Um delimitador para ser usado de padrao. De preferencia um que seja diferente de todo o resto da string.
   */
  function multiExplode($delimitadores, $string, $delimitadorPadrao = '||') {

    $pattern = '/[' . addslashes(implode('', $delimitadores)) . ']/';
    $patternPadrao = '/[' . $delimitadorPadrao . ']+/s';

    // substitui os delimitadores pelo delimitador padrao
    // tambem remove delimitadores padrao redundantes
    $string = preg_replace(array($pattern, $patternPadrao), $delimitadorPadrao, $string);

    // finalmente faz o explode pelo delimitador padrao, retornando tudo em um soh vetor
    return explode($delimitadorPadrao, $string);
  }

  /**
   * Cria link para url
   * @param $title titulo da noticia
   * @return string
   */
  public static function createLink($title) {
    $title = self::removeAccents($title);
    $title = preg_replace('/[^0-9a-zA-Z\-\ ]+/', '', $title);
    $title = substr($title, 0, self::max_link_length);
    $title = trim($title);
    $title = str_replace(' ', '-', $title);
    return $title;
  }

  /**
   * Faz um parse de $dateString, de acordo com $formatString e re-formata a data conforme $newDate.
   * O formato segue o padrao da funcao date() do PHP. Ver documentacao em 
   * (http://www.php.net/manual/en/function.date.php)
   * 
   * @param $newDate novo formato da data
   * @param $dateString string da data
   * @param $formatString formato da string
   * @return unknown_type
   */
  public static function parseDate($newDate, $dateString, $formatString) {
    $i = 0;
    $j = 0;
    $formatChars = "dDjlNSwzWFmMntLoYyaABgGhHisueIOPTZcrU"; //caracteres permitidos conforme documentacao
    $len = strlen($dateString);
    $lenFormat = strlen($formatString);
    $myDate = $newDate;
    $s = '';

    while ($i <= $len) {
      //le os caracteres de dateString enquanto nao chegar em um 'separador'
      if (strpos($formatChars, $formatString{$j}) !== false && $formatString{$j + 1} != $dateString{$i}) {
        $s .= $dateString{$i};
        $i++;
      } else if ($formatString{$j + 1} == $dateString{$i} || $j + 1 == $lenFormat) { //se atingir um 'separador', substitui o valor na string nova
        $myDate = str_replace($formatString{$j}, $s, $myDate);
        $s = '';
        $i++;
        $j++;
      } else { //se atingir o separador na string de formato, passa pra frente sem fazer nada
        $j++;
      }
    }

    return $myDate;
  }

  /**
   * Adiciona a data passada: dias, meses e anos
   * @param <string> $date Data no formato Y-m-d
   * @param <string> $format Formato da data de saida
   * @param <int> $days Numero de dias a serem adicionados (valores negativos sao validos)
   * @param <int> $months Numero de meses a serem adicionados (valores negativos sao validos)
   * @param <int> $years Numero de anos a serem adicionados (valores negativos sao validos)
   */
  public static function addDate($date, $format, $days = 0, $months = 0, $years = 0) {
    $d = explode('-', $date);
    return date($format, mktime(0, 0, 0, $d[1] + $months, $d[2] + $days, $d[0] + $years));
  }

  /**
   * Extrai a quantidade de dias entre a data final e a data inicial
   * @param <string> $start_date Data inicial no formato Y-m-d
   * @param <string> $end_date Data final no formato Y-m-d
   */
  public static function getDays($start_date, $end_date) {
    $s_d = explode('-', $start_date);
    $e_d = explode('-', $end_date);
    $s_days = mktime(0, 0, 0, $s_d[1], $s_d[2], $s_d[0]);
    $e_days = mktime(0, 0, 0, $e_d[1], $e_d[2], $e_d[0]);

    $days = $e_days - $s_days; //dias em segundos
    $days = floor($days / 60 / 60 / 24);
    /* $days = floor($days / 60); // dias em minutos
      $days = floor($days / 60); // dias em horas
      $days = floor($days / 24); // dias */

    return $days;
  }

  /**
   * Calcula a diferença entre duas datas e retorna uma string "amigável" dessa diferença
   * @author Raphael Hardt
   * @param string $start_date Data inicial no formato Y-m-d[ H:i:s]
   * @param string $end_date Data final no formato Y-m-d[ H:i:s]. Caso nula, usa time()
   */
  public static function differenceDates($start_date, $end_date = null) {
    if (!isset($end_date)) {
      $end_date = time(); // se a data final não for informada, seta ela como "agora"
      $hoje = true;
    } else {
      // transforma a data final em inteiro-unix
      if (strpos($end_date, ':') !== false)
        $end_date .= ' ' . date('H:i:s'); // se não houver hora na data, concatena a atual
      $end_date = strtotime($end_date);
    }

    if (!is_numeric($start_date)) {
      // transforma a data inicial em inteiro-unix
      if (strpos($start_date, ':') !== false)
        $start_date .= ' ' . date('H:i:s'); // se não houver hora na data, concatena a atual
      $start_date = strtotime($start_date);
    }

    $nomes = array("segundo", "minuto", "hora", "dia", "semana", "mês", "ano");
    $equiv = array(60, 60, 24, 7, 4.35, 12);

    // verifica o tempo da diferença entre as datas
    $dif = $end_date - $start_date;

    if ($hoje) {
      if ($dif >= 0) {
        $tempo = "%s atrás";
      } else {
        $dif *= -1; // deixa dif positivo
        if ($dif == 1)
          $tempo = "passou-se %s";
        else
          $tempo = "passaram-se %s";
      }
    }
    else
      $tempo = "%s";

    // calcula o quociente da diferença até chegar no menor quociente possivel
    // para conseguir o maior tempo possivel
    $c = count($equiv) - 1;
    for ($i = 0; $dif >= $equiv[$i] && $i < $c; $i++) {
      $dif /= $equiv[$i];
    }
    $dif = round($dif);

    if ($dif != 1) {
      if ($nomes[$i] == "mês")
        $nomes[$i] = "meses";
      else
        $nomes[$i] .= "s";
    }

    // retorna uma string, como: '4 horas atrás', 'passou-se 1 dia', '6 minutos'... etc
    return sprintf($tempo, $dif . ' ' . $nomes[$i]);
  }

  public function verificaAcesso($pagina = '/') {
    $tempo = time();

    if (!isset($_SESSION[$pagina])) {
      $_SESSION[$pagina] = array();
      $_SESSION[$pagina][] = $tempo;
    } else {
      $acessos = & $_SESSION[$pagina];
      foreach ($acessos as $k => $acesso) {
        //se o acesso foi ha mais de X minutos, desconsidera
        if ($tempo - $acesso > LIMITE_TEMPO) {
          unset($acessos[$k]);
        }
      }
      $acessos[] = $tempo;
      $acessos_pagina = count($acessos); //conta quantos acessos o visitante teve na pagina que sao validos
      if ($acessos_pagina > LIMITE_ACESSOS) {
        return false;
      }
    }
    return true;
  }

  public static function validaData($data, $futuro = false) {
    $matches = array();

    if (preg_match(PATTERN_DATA, $data, $matches)) {
      $data = $matches[0];
      $d = explode('/', $data);

      if ($d[0] <= 0 || $d[0] > 31 || $d[1] <= 0 || $d[1] > 12 || $d[2] <= MIN_YEAR || (!$futuro && $d[2] > MAX_YEAR)) {
        return false;
      }

      if ($d[1] == 2) {
        //retorna a quantidade de dias em fevereiro no ano especificado em $d[2]
        $days = ((($d[2] % 4 == 0) && ( (!($d[2] % 100 == 0)) || ($d[2] % 400 == 0))) ? 29 : 28 );
        if ($d[1] > $days) {
          return false;
        }
      } else if (($d[1] == 4 || $d[1] == 6 || $d[1] == 9 || $d[1] == 11) && $d[0] > 30) {
        return false;
      }
      return true;
    }
    return false;
  }

  public static function validaHora($hora) {
    $matches = array();

    if (preg_match('/(:?[0-9]{2})\:(:?[0-9]{2})/', $hora, $matches)) {
      $h = $matches;
      if ($h[1] > '23' || $h[1] < '0') {
        return false;
      } else if ($h[2] > '59' || $h[2] < '0') {
        return false;
      }
      return true;
    }
    return false;
  }

  /**
   * FUNCAO COPIADA DE: PHPMailer
   * Check that a string looks roughly like an email address should
   * Static so it can be used without instantiation
   * Tries to use PHP built-in validator in the filter extension (from PHP 5.2), falls back to a reasonably competent regex validator
   * Conforms approximately to RFC2822
   * @link http://www.hexillion.com/samples/#Regex Original pattern found here
   * @param string $address The email address to check
   * @return boolean
   * @static
   * @access public
   */
  public static function validaEmail($address) {
    if (function_exists('filter_var')) { //Introduced in PHP 5.2
      if (filter_var($address, FILTER_VALIDATE_EMAIL) === FALSE) {
        return false;
      } else {
        return true;
      }
    } else {
      return preg_match(PATTERN_EMAIL, $address);
    }
  }

  public static function extractKeys($array = array()) {
    if (empty($array) || !is_array($array)) {
      return array();
    }
    return array_keys($array);
  }

  /**
   * Function to retrieve the browser information.
   * Provided at http://www.php.net/manual/en/function.get-browser.php#92310
   * @param $agent
   * @return <array>
   */
  public static function browserInfo($agent = null) {
    // Declare known browsers to look for
    $known = array('msie', 'firefox', 'safari', 'webkit', 'opera', 'netscape',
        'konqueror', 'gecko');

    // Clean up agent and build regex that matches phrases for known browsers
    // (e.g. "Firefox/2.0" or "MSIE 6.0" (This only matches the major and minor
    // version numbers.  E.g. "2.0.0.6" is parsed as simply "2.0"
    $agent = strtolower($agent ? $agent : $_SERVER['HTTP_USER_AGENT']);
    $pattern = '#(?<browser>' . join('|', $known) .
            ')[/ ]+(?<version>[0-9]+(?:\.[0-9]+)?)#';

    // Find all phrases (or return empty array if none found)
    if (!preg_match_all($pattern, $agent, $matches))
      return array();

    // Since some UAs have more than one phrase (e.g Firefox has a Gecko phrase,
    // Opera 7,8 have a MSIE phrase), use the last one found (the right-most one
    // in the UA).  That's usually the most correct.
    $i = count($matches['browser']) - 1;
    $browser = new stdClass;
    $browser->name = $matches['browser'][$i];
    $browser->version = $matches['version'][$i];
    return $browser;
  }

  /**
   * Verifica qual sistema operacional do usuário (lado cliente), pelo user_agent
   * @author Raphael Hardt
   * @param mixed $agent Informações do user_agent. Opcional.
   */
  public static function getOs($agent = null) {
    $agent = $agent ? $agent : $_SERVER['HTTP_USER_AGENT'];
    $oses = array('Windows 3.11' => 'Win16',
        'Windows 95' => '(Windows 95)|(Win95)|(Windows_95)',
        'Windows 98' => '(Windows 98)|(Win98)',
        'Windows 2000' => '(Windows NT 5\.0)|(Windows 2000)',
        'Windows XP' => '(Windows NT 5\.1)|(Windows XP)',
        'Windows 2003' => '(Windows NT 5\.2)',
        'Windows NT 4.0' => '(Windows NT 4\.0)|(WinNT4\.0)|(WinNT)',
        'Windows ME' => 'Windows ME',
        'Windows Vista/Windows Server 2008' => 'Windows NT 6\.0',
        'Windows 7' => 'Windows NT 6\.1',
        'Open BSD' => 'OpenBSD',
        'Sun OS' => 'SunOS',
        'Linux' => '(Linux)|(X11)',
        'Macintosh' => '(Mac_PowerPC)|(Macintosh)',
        'QNX' => 'QNX',
        'BeOS' => 'BeOS',
        'OS/2' => 'OS\/2',
        'Search Bot' => '(nuhk)|(Googlebot)|(Yammybot)|(Openbot)|(Slurp/cat)|(msnbot)|(ia_archiver)'
    );
    foreach ($oses as $os => $pattern) {
      if (preg_match("/$pattern/i", $agent))
        return $os;
    }
    return 'Desconhecido';
  }

  public static function getIp() {
    return $_SERVER['REMOTE_ADDR'];
  }

  public static function implode_key($glue, $pieces) {
    $glued = array();
    foreach ($pieces as $key => $value) {
      $glued[] = '[' . $key . '] ' . $value;
    }
    return implode($glue, $glued);
  }

}