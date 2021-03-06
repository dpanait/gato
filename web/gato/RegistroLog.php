<?php

class RegistroLog {
  
  public static function log( $texto = "@", $tipo = "log" ) {
    
    try {
      $f = fopen(dirname(__FILE__) . DIRECTORY_SEPARATOR . "log" . DIRECTORY_SEPARATOR . date("Ymd").".html", "a+");
      fwrite($f, "[" . date("Y-m-d H:i:s.u") ." ". self::get_IP(). " - $tipo ] ". $texto . "\n" );
      fclose($f);
    } catch ( Exception $e ) {
      //# ...
    }

  }
  
  private static function get_IP(){
    
    if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if( isset( $_SERVER ['HTTP_VIA'] ))  $ip = $_SERVER['HTTP_VIA'];
    else if( isset( $_SERVER ['REMOTE_ADDR'] ))  $ip = $_SERVER['REMOTE_ADDR'];
    else $ip = null ;
    return $ip;
  }

}