<?php

class PaginaCollection extends ModelCollection {

  public function __construct() {
    parent::__construct();
    
    $this->setModel('Pagina');
    // campos
    $this->setFields('p.id', 'p.id_edicoes', 'p.id_capitulos', 'p.arq_pagina', 'p.ordenacao', 'p.ordenacao_indiv', 'p.html', 'p.zoom', 'p.flags', 'c.titulo titulo_cap', 'c.extra', 's.id id_series', 's.titulo titulo_serie');
    
    $tables = array(
        array('#name' => 'cn_paginas', '#alias' => 'p'),
        array('#name' => 'cn_capitulos', '#alias' => 'c', '#join' => 'LEFT', '#on' => 'c.id = p.id_capitulos'),
        array('#name' => 'cn_series', '#alias' => 's', '#join' => 'LEFT', '#on' => 's.id = c.id_series')
    );

    $this->setTables($tables);
  }

}