<?php

class Edicao extends Model {

  public function __construct($constraints = array()) {
    parent::__construct($constraints);
    // nome da tabela
    $this->setTable('cn_edicoes');
    // campos
    $this->setFields('id', 'numero', 'dtlanc', 'lancada', 'titulo', 'descricao', 'folder', 'file', 'dtencerr');
  }

  public function selectPaginas() {
    $pags = new PaginaCollection();

    //$pags->setField('id', 'i');
    $pags->setConstraint('id_edicoes', $this->id);
    $pags->setOrder('ordenacao', 'asc');
    $pags->select();

    return $pags;
  }

}