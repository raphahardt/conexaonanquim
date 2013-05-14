<?php

final class HomeController extends LayoutController {

  public function index($parts = array()) {

    $this->setPageTitle('');
    //$this->setJavascripts('models/home/home.js');

    $this->showContents();
  }

}