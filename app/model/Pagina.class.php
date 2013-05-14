<?php

class Pagina extends Model {

  public function __construct($constraints = array()) {
    parent::__construct($constraints);
    // nome da tabela
    $this->setTable('cn_paginas');
    // campos
    $this->setFields('id', 'id_edicoes', 'id_capitulos', 'arq_pagina', 'ordenacao', 'ordenacao_indiv', 'html', 'zoom', 'flags');
  }

}