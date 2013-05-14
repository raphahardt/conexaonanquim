<?php

class Serie extends Model {
  
  public function getTipoSerie() {
    
    if (!$this->id) return '';
    
    $tipos = array(
      0 => 'Série',
      1 => 'One-shot',
      2 => 'Light Novel',
      3 => '',
      4 => 'Yonkoma',
      5 => 'Especial'
    );
    
    return $tipos[ $this['tipo'] ];
  }

  public function __construct($constraints = array()) {
    parent::__construct($constraints);
    // nome da tabela
    $this->setTables(array(
        array('#name' => 'cn_series', '#alias' => 's'),
        array('#name' => 'vw_cn_votos', '#alias' => 'v', '#join' => 'LEFT', '#on' => 's.id = v.id_series')
    ));
    // campos
    $this->setFields(
    's.id', 
    's.titulo', 
    's.urlkey', 
    's.arq_logo', 
    's.arq_preview', 
    's.arq_facebook', 
    's.facebook_url', 
    's.autores', 
    's.aut_plural', 
    's.aut_femin', 
    's.genero', 
    's.tipo', 
    's.period', 
    's.ativo', 
    's.invisivel', 
    's.classif', 
    's.sinopse', 
    's.oficial', 
    's.exclusividade',
    'sum(v.grade) rating_sum',
    'count(v.grade) rating_quant');
    
    $this->setGroupBy(
    's.id', 
    's.titulo', 
    's.urlkey', 
    's.arq_logo', 
    's.arq_preview', 
    's.arq_facebook', 
    's.facebook_url', 
    's.autores', 
    's.aut_plural', 
    's.aut_femin', 
    's.genero', 
    's.tipo', 
    's.period', 
    's.ativo', 
    's.invisivel', 
    's.classif', 
    's.sinopse', 
    's.oficial', 
    's.exclusividade');
  }

  public function selectPaginas() {
    $pags = new PaginaCollection();

    //$pags->setField('id', 'i');
    $pags->setConstraint('id_edicoes', $this->id);
    $pags->setOrder('ordenacao', 'asc');
    $pags->select();

    return $pags;
  }
  
  public function getAutores() {
    $has = new ModelCollection();
    $has->setTable('cn_series_has_votantes');
    
    $has->setConstraint('id_series', $this->id, 'i');
    
    if ($has->select()) {
      $autores = new VotanteCollection();
      foreach ($has as $h) {
        $autores->setConstraintIn('id', $h['id_votantes']);
      }
      
      return $autores;
    }
    
    // erro
    return array();
  }
  
  public function getCapitulos() {
    $capitulos = new CapituloCollection();

    //$pags->setField('id', 'i');
    $capitulos->setConstraint('id_series', $this->id);

    return $capitulos;
  }

}