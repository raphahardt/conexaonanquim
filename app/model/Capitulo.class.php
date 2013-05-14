<?php

class Capitulo extends Model {

  public function __construct($constraints = array()) {
    parent::__construct($constraints);
    $this->setTable('cn_capitulos');
    // campos
    $this->setFields('id', 
    'titulo', 
    'id_series', 
    'descr', 
    'extra');
  }

}