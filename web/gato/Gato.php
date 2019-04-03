<?php
/*
* El animalillo que carga con 
* buena parte del trabajo...
*/
class Gato {

  private $peticion_url = "/";
  private $expiracion_cookie = 3600;
  private $notificar_errores = TRUE;
  private $controlador = "Home";
  private $accion = "index";
  private $parametros = array();
  private $control = NULL;

  /*
  * Define las constantes de las rutas en la aplicación
  * Autocarga de clases
  */
  public function __construct(){
    
    define( 'RUTA_GATO', dirname(__FILE__) . DIRECTORY_SEPARATOR );
    define( 'RUTA_APP', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR );
    define( 'RUTA_PUBLIC', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "public" . DIRECTORY_SEPARATOR );
    define( 'RUTA_LIB', RUTA_GATO . "lib" . DIRECTORY_SEPARATOR );
    spl_autoload_register([__CLASS__,'_autocarga_de_clases']);
    define( 'RUTA_VENDOR', RUTA_APP .'vendor' . DIRECTORY_SEPARATOR);
    spl_autoload_register([__CLASS__,'_autocarga_de_clases']);
    define( 'RUTA_CONFIG', RUTA_APP.'config' .DIRECTORY_SEPARATOR);
    $composer = RUTA_VENDOR . DIRECTORY_SEPARATOR . "autoload.php";

    if ( is_file($composer) ) {
        require_once($composer);
    }   
    
  }

  ////////////////////////////////////////////////
  // FUNCIONES PRIVADAS
  ////////////////////////////////////////////////

  /*
  * Recoge la petición del usuario desde la url
  * Inicializa la peticion en "Mostrar"
  */
  private function _get_peticion_url(){
    
    $this->peticion_url = $_GET['_peticion_'] ?? $this->peticion_url;
    Mostrar::set_peticion( $this->peticion_url );
    
  }

  // Para cargar las clases segun se necesite
  private function _autocarga_de_clases( $class ) {
    
    if ( is_file( RUTA_GATO . "$class.php") ) {
      return require_once RUTA_GATO . "$class.php";
    }

    if ( is_file( RUTA_LIB . "$class.php" ) ) {
      return require_once RUTA_LIB . "$class.php";
    }
    
  }

  /*
  * Comprobar que existe un resultado para la petición solicitada
  * Si existe controlador para la peticion se inicializa el "Controlador"
  * Se comprueba que exista la "acción" pedida en ese "controlado"r
  * Se guardan los parametros si existen
  */
  private function _existe_url(){
    // url sin nada
    if ( $this->peticion_url == '/' ) {
      include_once ( RUTA_APP . "controles" . DIRECTORY_SEPARATOR . $this->controlador . '.php' );        
      $this->control = new $this->controlador();
      return true;
    }
    // url con algo
    $partes_url = explode( '/', $this->peticion_url );
    // el controlador
    $this->controlador = current($partes_url);
    
    if ( !is_file( RUTA_APP . "controles" . DIRECTORY_SEPARATOR . $this->controlador . '.php') ){
      return false;
    } else {
      include_once ( RUTA_APP . "controles" . DIRECTORY_SEPARATOR . $this->controlador . '.php' );        
      $this->control = new $this->controlador();
    }
    // la accion
    if ( next($partes_url) === false ) {
      // Si no hay accion fuera...
      return true;
    }

    $this->accion = current($partes_url);
    if ( $this->accion == "__construct" ) {
      return false;
    }

    if ( !is_callable([$this->control, $this->accion]) ) {
      return false;
    }
    // Si no hay parametros sale
    if ( next( $partes_url ) === false ) {
      return true;
    }

    $this->parametros = array_slice( $partes_url, key( $partes_url ) );
    return true;

  }

  /*
  * Comprueba si el número de parametros es el correcto para la accion
  * devuelve true si todo es correcto o false en caso contrario.
  */
  private function _ejecutar(){
    
    $reflection_method = new ReflectionMethod($this->control, $this->accion);
    $num_params = count( $this->parametros );
    // Distinto número de parametros
    if ( $num_params < $reflection_method->getNumberOfRequiredParameters() ||
         $num_params > $reflection_method->getNumberOfParameters() ) {
        return false;
    } else {
      try {
        //cuando el controlador hace echos...
        ob_start(); 
        $reflection_method->invokeArgs($this->control, $this->parametros);
        $contenido = ob_get_contents();
        ob_end_clean();
        Mostrar::add_cuerpo( $contenido );
        return true;
      } catch ( ReflectionException $e ) {
        return false;
      }
    }
    
  }

  ////////////////////////////////////////////////
  // FUNCIONES PUBLICAS
  ////////////////////////////////////////////////

  public function lanzar(){

    $this->set_expiracion_cookie();
    $this->set_notificar_errores();
    $this->_get_peticion_url();
    session_name("Gato");
    session_start();
    $es_404 = false;
    // Si existe la URL en la web
    if ( $this->_existe_url() && $this->_ejecutar() ) {
      extract(get_object_vars($this->control), EXTR_OVERWRITE);
      $_pagina = RUTA_APP . "paginas" . DIRECTORY_SEPARATOR . $this->controlador . DIRECTORY_SEPARATOR .  Mostrar::get_pagina() . '.phtml';
      $_marco = RUTA_APP . 'marcos' . DIRECTORY_SEPARATOR . Mostrar::get_marco() . '.phtml';
      Mostrar::set_control($this->control);

      if ( !is_file($_pagina) ) {
        RegistroLog::log("Linea:" . __LINE__ . ") Vista no encomtrada: $_pagina");
      } else {
        ob_start(); 
        include $_pagina;
        $contenido = ob_get_contents();
        ob_end_clean();
        Mostrar::add_cuerpo( $contenido );
      }

      if ( !is_file($_marco) ) {
          RegistroLog::log("Linea:" . __LINE__ . ") Marco no encomtrado: $_marco");
      }else{
        ob_start();
        include $_marco;
        $contenido = ob_get_contents();
        ob_end_clean();
        Mostrar::add_respuesta( $contenido );
      }
    // Es un ERROR 404
    }else{
      
      $es_404 = true;
      $this->controlador = "E404";
      include_once ( RUTA_APP . "controles" . DIRECTORY_SEPARATOR . $this->controlador . '.php' );        
      $this->control = new $this->controlador();
      $reflection_method = new ReflectionMethod($this->control, $this->accion);
      $num_params = count( $this->parametros );
    // Distinto número de parametros
      if ( $num_params < $reflection_method->getNumberOfRequiredParameters() ||
        $num_params > $reflection_method->getNumberOfParameters() ) {
        RegistroLog::log("roto num_params 404");
      }else{
        try {
            $reflection_method->invokeArgs($this->control, $this->parametros);
        } catch (ReflectionException $e) {
            RegistroLog::log("roto reflectionMethod 404");
        }
      }
      
      extract(get_object_vars($this->control), EXTR_OVERWRITE);
      $_pagina = RUTA_APP . "paginas" . DIRECTORY_SEPARATOR . $this->controlador . DIRECTORY_SEPARATOR .  Mostrar::get_pagina() . '.phtml';
      $_marco = RUTA_APP . 'marcos' . DIRECTORY_SEPARATOR . Mostrar::get_marco() . '.phtml';
      Mostrar::set_control($this->control);

      if ( !is_file($_pagina) ) {
        RegistroLog::log("Linea:" . __LINE__ . ") 404 Vista no encomtrada: $_pagina");
      } else {
        ob_start(); 
        include $_pagina;
        $contenido = ob_get_contents();
        ob_end_clean();
        Mostrar::add_cuerpo($contenido);
      }

      if ( !is_file($_marco) ) {
          RegistroLog::log("Linea:" . __LINE__ . ") 404 Marco no encomtrado: $_marco");
      }else{
          ob_start();
          include $_marco;
          $contenido = ob_get_contents();
          ob_end_clean();
          Mostrar::add_respuesta($contenido);
      }
    }
    Mostrar::respuesta( $es_404 );
  }

  // URL
  public function get_peticion_url(){
    return $this->peticion_url;
  }

  // SESION
  public function set_expiracion_cookie( $segundos = 3600 ) {
    ini_set("session.cookie_lifetime", $segundos);
    ini_set("session.gc_maxlifetime", $segundos);
    $this->expiracion_cookie = $segundos;
  }

  public function get_expiracion_cookie(){
    return $this->expiracion_cookie;
  }

  // ERRORES
  public function set_notificar_errores( $notificar = true ) {
    if ( $notificar ) {
      error_reporting( E_ALL | E_STRICT );
      ini_set('display_errors', "On");
    }else{
      error_reporting(0);
      ini_set('display_errors', "Off");
    }
    $this->notificar_errores = $notificar;
  }

  public function get_notificar_errores(){
    return $this->notificar_errores;
  }

} 