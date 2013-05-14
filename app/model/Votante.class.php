<?php

class Votante extends Model {

  public function __construct($constraints = array()) {
    parent::__construct($constraints);
    // nome da tabela
    $this->setTable('cn_votantes');
    // campos
    $this->setFields('id', 'facebook_id', 'nome', 'email', 'nivel', 'ip');
  }
  
  public function getSeries() {
    $has = new ModelCollection();
    $has->setTable('cn_series_has_votantes');
    
    $has->setConstraint('id_votantes', $this->id, 'i');
    
    if ($has->select()) {
      $series = new SerieCollection();
      foreach ($has as $h) {
        $series->setConstraintIn('id', $h['id_series']);
      }
      
      return $series;
    }
    
    // erro
    return array();
  }

}