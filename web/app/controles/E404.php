<?php
class E404 extends ControlBase {  

  function  __construct () {
    parent::__construct();
  }
  
  public function index () {    
		$this->title = "Página no encontrada";
    $this->description = "No encontramos la Página que busca.";
    $this->keywords = "404,no encontramos,no existe";
	Mostrar::setMarco("e404");
    Mostrar::setPagina("e404");    
  }
	
}