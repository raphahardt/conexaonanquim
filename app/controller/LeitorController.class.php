<?php

function escape($string) {
  return str_replace('\'', '', utf8_decode($string));
}

function downloadFile($fullPath) {

  // Must be fresh start
  if (headers_sent())
    die('Headers Sent');

  // Required for some browsers
  if (ini_get('zlib.output_compression'))
    ini_set('zlib.output_compression', 'Off');

  // File Exists?
  if (file_exists($fullPath)) {

    // Parse Info / Get Extension
    $fsize = filesize($fullPath);
    $path_parts = pathinfo($fullPath);
    $ext = strtolower($path_parts["extension"]);

    // Determine Content Type
    switch ($ext) {
      case "pdf": $ctype = "application/pdf";
        break;
      case "exe": $ctype = "application/octet-stream";
        break;
      case "zip": $ctype = "application/zip";
        break;
      case "doc": $ctype = "application/msword";
        break;
      case "xls": $ctype = "application/vnd.ms-excel";
        break;
      case "ppt": $ctype = "application/vnd.ms-powerpoint";
        break;
      case "gif": $ctype = "image/gif";
        break;
      case "png": $ctype = "image/png";
        break;
      case "jpeg":
      case "jpg": $ctype = "image/jpg";
        break;
      default: $ctype = "application/force-download";
    }

    header("Pragma: public"); // required
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private", false); // required for certain browsers
    header("Content-Type: $ctype");
    header("Content-Disposition: attachment; filename=\"" . basename($fullPath) . "\";");
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: " . $fsize);
    ob_clean();
    flush();
    readfile($fullPath);
  }
  else
    die('File Not Found');
}

class LeitorController extends LayoutController {

  public function index($parts = array()) {
    
    $this->setPageTitle('Edições');
    $this->showContents();
  }

  function open_edition($parts = array()) {

    try {

      @session_start();

      $chapters = array();
      $chapters_names = array();

      $facebook = new Facebook(array(
        'appId' => DJCK_FACEBOOK_APP_ID,
        'secret' => _DJCK_FACEBOOK_APP_SECRET,
      ));

      $cookie_votante = new Cookie('votante');

      $user_profile = array();
      $user_profile['id'] = $cookie_votante->get();

      if (!$user_profile['id']) {

        $user = $facebook->getUser();

        if ($user) {
          try {
            // Proceed knowing you have a logged in user who's authenticated.
            $user_profile = $facebook->api('/me');

            $votante = new Model();
            $votante->setTable('cn_votantes');
            $votante->setFields('id', 'facebook_id', 'nome', 'email', 'nivel');
            $votante->setConstraint('email', $user_profile['email']);

            if ($votante->select()) {

              $cookie_votante->set($votante->getData('id'));

              $user_profile['id'] = $votante->getData('id');
              $user_profile['username'] = $votante->getData('facebook_id');
              $user_profile['email'] = $votante->getData('email');
              $user_profile['name'] = $votante->getData('nome');
            } else {
              $votante->setFields(null);
              $votante->setFields('nome', 'email', 'facebook_id', 'ip');
              $votante->setData('nome', $user_profile['name']);
              $votante->setData('email', $user_profile['email']);
              $votante->setData('facebook_id', $user);
              $votante->setData('ip', getRealIp());
              if ($votante->insert()) {

                $cookie_votante->set($votante->id);

                $user_profile['id'] = $votante->id;
              } else {
                echo 'Houve um problema ao cadastrar seu e-mail. Tente novamente. Caso o problema persista, avise os editores.';
              }
            }

            $user_profile['username'] = $user;
          } catch (FacebookApiException $e) {
            $user = null;
          }
        }
      } else {
        // já está logado

        $votante = new Model();
        $votante->setTable('cn_votantes');
        $votante->setFields('id', 'facebook_id', 'nome', 'email', 'nivel');
        $votante->setConstraint('id', $user_profile['id']);

        if ($votante->select()) {

          $user_profile['username'] = $votante->getData('facebook_id');
          $user_profile['email'] = $votante->getData('email');
          $user_profile['name'] = $votante->getData('nome');
        }
      }

      $edicao = new Edicao(array('numero' => $parts[':edition'] . '/' . $parts[':year']));
      $edicao->select();

      $edition = $edicao->getData();

      $edicao_id = $edition['id'];
      list($edition['numero'], $edition['ano']) = explode('/', $edition['numero']);

      if ($edition['lancada'] != 1 && $_SESSION['ger'] !== 'chefia') {
        $this->showContents();
        return;
      }

      //pegando os votos do votante desta edicao
      $voto = new VotoCollection();
      $voto->setConstraint('id_votantes', $user_profile['id']);
      $voto->setConstraint('id_edicoes', $edicao_id);

      // setando os votos já votados
      if ($voto->select()) {
        foreach ($voto as $row_voto) {
          $chapters_names[$row_voto['id_capitulos']] = $row_voto['grade'];
        }
      }

      // criando paginas
      $paginas = $edicao->selectPaginas();
      $numero_paginas = count($paginas);

      $i = 1;
      foreach ($paginas as $pagina) {

        $row = $pagina->getData();

        $title = $row['titulo_serie'];
        if (empty($title))
          $title = $row['titulo_cap'];

        $pages[] = array(
            'img' => $row['arq_pagina'],
            'title' => $title,
            'cap_title' => $title != $row['titulo_cap'] ? $row['titulo_cap'] : '',
            'titlekey' => $row['id_capitulos'],
            'seriekey' => $row['id_series'],
            'folder' => $edicao->getData('folder'),
            'votable' => $row['extra'] < 1,
            'pagina' => $i,
            'html' => ( $row['html'] ? $row['html'] : null ),
            'pagina_prev' => $i - 1 <= 0 ? $numero_paginas : $i - 1,
            'pagina_next' => ($i % $numero_paginas) + 1
        );

        // adicionar capitulos ao indice
        if ($row['extra'] < 2) {
          if (!array_key_exists($title, $chapters)) {
            $chapters[$title] = array(
                'name' => htmlentities(utf8_decode($title)),
                'page' => $i
            );
          }
        }
        ++$i;
      }

      $this->assign("siteName", ' - Edição #' . $edition['numero'] . ' (' . $edition['dtlanc'] . ')');

      $this->assign("leitor", true);
      $this->assign("pages", $pages);
      $this->assign("edition", $edition);

      $this->assign("logged", $user_profile['email'] ? true : false);
      $this->assign("expired", strtotime($edicao->getData('dtencerr')) < time() && ($edicao->getData('dtencerr') ? true : false ));
      $this->assign("facebookAppId", $facebook->getAppID());
      $this->assign("profile", $user_profile);
      $this->assign("chapters", $chapters);
      $this->assign("jsonvotes", json_encode($chapters_names));

      $this->showContents('/editions.tpl');
    } catch (Exception $e) {
      echo $e->getMessage(), $e->getTrace();
    }
  }

  function download($parts = array()) {
    // grava download no banco
    $mysqli = new mysqli('dbmy0021.whservidor.com', 'conexaonan', 'lkglby90', 'conexaonan');

    $mysqli->query('INSERT INTO cn_downloads (
						edicao, 
						ip,
						dthr
						) 
						values 
						(
						\'' . $parts[':edition'] . '/' . $parts[':year'] . '\', 
						\'' . getRealIp() . '\', 
						CURRENT_TIMESTAMP
						)');

    $mysqli->close();

    $pasta = 'fevereiro';
    if ($parts[':edition'] == 2) {
      $pasta = 'marco';
    }

    if ($parts[':which'] == 'pdf') {
      downloadFile(_path('images.editions.' . $parts[':year'] . '.fevereiro.') . 'ConexaoNanquim' . $parts[':edition'] . '-2013.pdf');
    } else {
      downloadFile(_path('images.editions.' . $parts[':year'] . '.fevereiro.') . 'ConexaoNanquim' . $parts[':edition'] . '-2013.cbr');
    }
  }

  function logout() {
    // deslogar
    $cookie_votante = new Cookie('votante');
    $cookie_votante->delete();

    GlobalFunc::go_to('leitor/edicoes/' . $_GET['edicao']);
  }

  function login($parts = array()) {

    $email = $_POST['email'];
    $nome = $_POST['nome'];

    $cookie_votante = new Cookie('votante');
    $votante = $cookie_votante->get(); // TODO: pegar o id logado
    // se ja tiver logado, nao logar de novo
    if (!empty($votante)) {
      echo 'OK';
      return;
    }

    // validar email
    if (!validEmail($email)) {
      echo 'EMAILINVALIDO';
      return;
    }

    $votante = new Model();
    $votante->setTable('cn_votantes');
    $votante->setFields('id', 'nome', 'email', 'nivel');
    $votante->setConstraint('email', $email);

    if ($votante->select()) {

      $cookie_votante->set($votante->getData('id'));
      echo 'OK';
    } else {

      // se nao digitou o nome ainda, pedir
      if (empty($nome)) {
        echo 'NAOEXISTE';
        return;
      }

      $votante->setFields(null);
      $votante->setFields('nome', 'email', 'ip');
      $votante->setData('nome', $nome);
      $votante->setData('email', $email);
      $votante->setData('ip', getRealIp());
      if ($votante->insert()) {

        $cookie_votante->set($votante->id);

        echo 'OK';
      } else {
        echo 'Houve um problema ao cadastrar seu e-mail. Tente novamente. Caso o problema persista, avise os editores.';
      }
    }
  }

  function vote($parts = array()) {

    //$edicao_id = $parts[':edition'];

    $edicao = new Edicao(array('numero' => $parts[':edition'] . '/' . $parts[':year']));
    $edicao->select();

    $edition = $edicao->getData();

    $edicao_id = $edition['id'];

    $serie = $_POST['serie'];
    $capt = $_POST['capt'];
    $grade = $_POST['grade'];


    $cookie_votante = new Cookie('votante');
    $votante = $cookie_votante->get(); // TODO: pegar o id logado


    $voto = new Voto();
    $voto->setFields('id_votantes', 'id_edicoes', 'id_capitulos', 'id_series', 'ip', 'grade');
    $voto->setData('id_votantes', $votante, 'i');
    $voto->setData('id_edicoes', $edicao_id, 'i');
    $voto->setData('id_capitulos', $capt, 'i');
    $voto->setData('id_series', $serie, 'i');
    $voto->setData('ip', getRealIp(), 's');
    $voto->setData('grade', $grade, 'i');

    if ($voto->insert()) {
      echo '1';
    } else {
      echo 'Houve um problema ao votar. Tente novamente. Caso o problema persista, avise os editores.';
    }

    return;
  }

}