<?php

class VotoCollection extends ModelCollection {

  public function __construct() {
    parent::__construct();
    
    //$this->setModel('Pagina');
    // campos
    $this->setFields('id_votantes', 'id_series', 'id_capitulos', 'id_edicoes', 'grade', 'quant');

    $this->setTable('vw_cn_votos');
  }

}