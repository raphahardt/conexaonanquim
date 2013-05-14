<?php

class SeriesController extends LayoutController {

  public function index($parts = array()) {
    
    $series = new SerieCollection();
    $series->setOrder('titulo');
    if (!$series->select()) {
      $this->show404();
      return;
    }
    
    $series_array = array();
    foreach ($series as $serie) {
      
      if ($autores = $serie->getAutores()) {
        $autores->select();
        $autores_array = $autores->getArray();
      } else {
        $autores_array = array(array('nome'=> 'Autor desconhecido'));
      }
      
      $capitulos = $serie->getCapitulos();
      $capitulos->select();
      
      $series_array[] = array(
        'nome' => $serie['titulo'],
        'key' => $serie['urlkey'],
        'tipo' => $serie['tipo'],
        'tipo_nome' => $serie->getTipoSerie(),
        'rating_percent' => round(($serie['rating_sum'] / max(1,$serie['rating_quant']) * 20), 1),
        'rating' => round($serie['rating_sum'] / max(1,$serie['rating_quant']), 1),
          
        'autores' => $autores_array,
        
        'numcapitulos' => count($capitulos),
      );
    }
    
    $this->assign('series', $series_array);
    
    $this->setPageTitle('Séries');
    $this->addBreadcrumb('Séries', 'series');
    $this->showContents();
  }
  
  public function open($parts = array()) {
    
    $serie = new Serie(array('urlkey' => $parts[':serie']));

    if (!$serie->select()) {
      $this->show404();
      return;
    }
    
    if ($autores = $serie->getAutores()) {
      $autores->select();
      $autores_array = $autores->getArray();
    } else {
      $autores_array = array(array('nome'=> 'Autor desconhecido'));
    }
    
    $capitulos = $serie->getCapitulos();
    $capitulos->select();
    
    $this->setPageTitle($serie['titulo'] . ' - Séries');
    $this->addBreadcrumb('Séries', 'series');
    $this->addBreadcrumb($serie['titulo'], 'series/'.$serie['urlkey']);
    
    $this->assign('serie', array(
      'nome' => $serie['titulo'],
      'key' => $serie['urlkey'],
      'tipo' => $serie['tipo'],
      'tipo_nome' => $serie->getTipoSerie(),
      'rating_percent' => round(($serie['rating_sum'] / max(1,$serie['rating_quant']) * 20), 1),
      'rating' => round($serie['rating_sum'] / max(1,$serie['rating_quant']), 1),
      'sinopse' => $serie['sinopse'],
      'autores' => $autores_array,
        
      'numcapitulos' => count($capitulos),
      
    ));
    
    $this->showContents('/open.tpl');
  }
  
}