<?php

  class Mostrar {

  private static $peticion = '/';
  private static $control = NULL;
  private static $respuesta = '';
  private static $marco = 'base';
  private static $vista = 'index';
  private static $cabecera_pintada = FALSE;
  private static $elementos = [];
  private static $cuerpos = [];
  private static $list_js = [];
  private static $list_css = [];

  // recibir que petición se ha hecho
  public static function set_peticion( $peticion = "/" ) {
    self::$peticion = $peticion;
  }

  // Añade a la respuesta más contenidos
  public static function add_respuesta( $contenido = "" ) {
    self::$respuesta .= $contenido;
  }

  // Envía la respuesta
  public static function respuesta( $es_404 ) {

    self::$respuesta = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', self::$respuesta);
    self::$respuesta = trim(preg_replace('!\s+!', ' ', self::$respuesta),"\s\t\n\r\0\x0B");

    $etagFile = md5( self::$respuesta );
    $etagHeader = ( isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false );

    if ( $es_404 ) {
      header("HTTP/1.1 404 Not Found");
      header("Status: 404 Not Found");
    } else if ( $etagHeader == $etagFile ) {
      header("HTTP/1.1 304 Not Modified");
      exit(0);
    }

    header('X-Powered-By: gato v1');

    if ( ob_start("ob_gzhandler") ) {
      header('Content-Encoding: gzip');
    }

    header("Etag: $etagFile");
    header('Cache-Control: public');

    // un dia 86400 segundos
    // un mes 2592000 segundos
    header('Expires: ' . gmdate('D, d M Y H:i:s ', time() + 2592000) . 'GMT');
    // Set the correct MIME type, because Apache won't set it for us
    header("Content-type: text/html; charset=utf-8");

    // quedan cabeceras...
    if( count(self::$elementos) > 0 ){
      self::$respuesta = preg_replace('/<\/head>/', implode('',self::$elementos).'</head>', self::$respuesta);
    }

    echo self::$respuesta;
    exit(0);
  }

  public static function draw_js(){
    if ( count(self::$list_js) > 0 ) {
      foreach(self::$list_js as $js){
        echo $js;
      }
    }
  }

  public static function add_js( $elemento ) {
    if(!empty($elemento)){
      self::$list_js[] = $elemento;
    }
  }

  public static function draw_css(){
    if ( count(self::$list_css) > 0 ) {
      foreach(self::$list_css as $css){
        echo $css;
      }
    }
  }

  public static function add_css( $elemento ) {
    if ( !empty($elemento)) {
      self::$list_css[] = $elemento;
    }
  }
  // Añade etiquetas en el <head></head> del documento html
  // ejemplo
  public static function add_cabecera( $elemento ) {

    if( !empty($elemento) ) {
      self::$elementos[] = $elemento;
    }
    
  }

  public static function add_parte( $parte ) {

    $parte = RUTA_APP . 'marcos' . DIRECTORY_SEPARATOR . "partes". DIRECTORY_SEPARATOR . $parte . '.phtml';

    if ( is_file($parte) ) {
      extract(get_object_vars(self::$control), EXTR_OVERWRITE);
      include $parte;
    } else {
      //# log...
    }

  }

  public static function add_cuerpo( $cuerpo ) {
    
    if ( !empty($cuerpo) ) {
      self::$cuerpos[] = $cuerpo;
    }
    
  }

  public static function cabecera(){

    self::$cabecera_pintada = TRUE;
    $title = self::$control->get_title();
    $description = self::$control->get_description();
    $keywords = self::$control->get_keywords();
    self::$elementos[] = "<title>$title</title>";
    self::$elementos[] = '<meta content="' . htmlspecialchars($description, ENT_QUOTES, "UTF-8") . '" name="description">';
    self::$elementos[] = '<meta content="' . $keywords . '" name="keywords">';
    
    if ( count(self::$elementos) > 0 ) {
      foreach(self::$elementos as $elemento){
        echo $elemento . PHP_EOL;
      }
      self::$elementos = [];
    }
    
  }

  public static function cuerpo(){
    
    if ( count(self::$cuerpos) > 0 ) {
      foreach(self::$cuerpos as $cuerpo){
        echo $cuerpo;
      }
    }
    
  }

  public static function get_marco(){
    return self::$marco;
  }

  public static function get_pagina(){
    return self::$vista;
  }

  public static function set_marco( $marco = 'base' ) {
    self::$marco = $marco;
  }

  public static function set_pagina( $pagina = 'index' ) {
    self::$vista = $pagina;
  }

  public static function set_control( $control ) {
    self::$control = $control;
  }

}