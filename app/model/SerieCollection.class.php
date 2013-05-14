<?php

class SerieCollection extends ModelCollection {

  public function __construct($constraints = array()) {
    parent::__construct($constraints);
    
    $this->setModel('Serie');
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

}