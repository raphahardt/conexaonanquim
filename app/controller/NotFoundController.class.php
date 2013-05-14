<?php

final class NotFoundController extends LayoutController {

  public function index($parts = array()) {

    $this->setPageTitle('404 Não encontrado - ');
    //$this->setJavascripts('models/home/home.js');

    $this->showContents();
  }

}