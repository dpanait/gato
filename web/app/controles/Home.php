<?php

class Home extends ControlBase {

  function __construct () {
    parent::__construct();
  }

  public function index () {
    $this->title = "gato";
    $this->description = "gato";
    $this->keywords = "gato";
    var_dump($this->db);
  }

}