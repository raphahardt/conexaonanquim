<?php

class VotanteCollection extends ModelCollection {

  public function __construct($constraints = array()) {
    parent::__construct($constraints);
    // nome da tabela
    $this->setTable('cn_votantes');
    // campos
    $this->setFields('id', 'facebook_id', 'nome', 'email', 'nivel', 'ip');
  }

}