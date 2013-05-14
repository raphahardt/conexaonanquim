<?php

class CapituloCollection extends ModelCollection {

  public function __construct($constraints = array()) {
    parent::__construct($constraints);
    
    $this->setModel('Capitulo');
    // nome da tabela
    $this->setTable('cn_capitulos');
    // campos
    $this->setFields('id', 
    'titulo', 
    'id_series', 
    'descr', 
    'extra');
  }

}