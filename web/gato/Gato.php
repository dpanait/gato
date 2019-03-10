<?php

class Gato {
 
    private $peticionUrl = "/";
	private $expiracionCookie = 3600;
	private $notificarErrores = true;
	private $controlador = "Home";
	private $accion = "index";
	private $parametros = array();
	private $control = NULL;
	
    // Define las constantes de las rutas en la aplicación
	// Autocarca de clases, Cookies, Notificación de errores
	// Inicializa la petición web
	public function __construct() {
		define( 'RUTA_GATO', dirname(__FILE__) . DIRECTORY_SEPARATOR );
		define( 'RUTA_APP', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR );
		define( 'RUTA_PUBLIC', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "public" . DIRECTORY_SEPARATOR );
		define( 'RUTA_LIB', RUTA_GATO . "lib" . DIRECTORY_SEPARATOR );
		spl_autoload_register(array(__CLASS__,'_autocarga_de_clases'));
		$this->set_expiracion_cookie();
		$this->set_notificar_errores();
		$this->_get_peticion_url();
	} 
	
	////////////////////////////////////////////////
	// FUNCIONES PRIVADAS
	////////////////////////////////////////////////
	
	// recoge la petición del usuario desde la url
	// inicializa la peticion en Mostrar
	private function _get_peticion_url() {
		$this->peticionUrl = isset($_GET['_peticion_']) ? $_GET['_peticion_'] : $this->peticionUrl;
		Mostrar::setPeticion( $this->peticionUrl );
	}	
	
	// Para cargar las clases segun se necesite
	private function _autocarga_de_clases( $class ) {
		if ( is_file( RUTA_GATO . "$class.php" ) ) {
			return require_once RUTA_GATO . "$class.php";
		}
		if ( is_file( RUTA_LIB . "$class.php" ) ) {
			return require_once RUTA_LIB . "$class.php";
		}
	}	
	
	// Comprobar que existe un resultado para la petición solicitada
	// Si existe controlador para la peticion se inicializa el "Controlador"
	// Se comprueba que exista la acción pedida en ese controlador
	// Se guardan los parametros si existen
	# TODO si se quiere routear¿?...
	private function _existe_url() {
		// url sin nada
		if( $this->peticionUrl == '/' ){
			include_once ( RUTA_APP . "controles" . DIRECTORY_SEPARATOR . $this->controlador . '.php' );        
			
			//ob_start(); 
			$this->control = new $this->controlador();
			//$contenido = ob_get_contents();
			//ob_end_clean();
			//Mostrar::addCuerpo($contenido);
			//echo $contenido . " peticionUrl / ";
			//exit('lol');
			return true;
		}
		// url con algo
		$partes_url = explode( '/', $this->peticionUrl );
		// el controlador
		//var_dump($partes_url);
		$this->controlador = current($partes_url);
    if ( !is_file( RUTA_APP . "controles" . DIRECTORY_SEPARATOR . $this->controlador . '.php') ){
			# TODO meter en un log...
			//echo RUTA_APP . "controles" . DIRECTORY_SEPARATOR . $this->controlador . '.php';
			//RegistroLog::log(RUTA_APP . "controles" . DIRECTORY_SEPARATOR . $this->controlador . '.php');
			return false;
		}else{
			include_once ( RUTA_APP . "controles" . DIRECTORY_SEPARATOR . $this->controlador . '.php' );        
			$this->control = new $this->controlador();
		}		
		// la accion
		// Si no hay accion fuera
		if ( next($partes_url) === false) return true;
		
		$this->accion = current($partes_url);
    if ( $this->accion == "__construct" ) return false;
		
		if (!is_callable( array( $this->control, $this->accion ) )){
			return false;
		}		
		// Si no hay parametros sale
		if ( next( $partes_url ) === false) return true;
		$this->parametros = array_slice( $partes_url, key( $partes_url ) );
		return true;
	}
	
	
	// Comprueba si el número de parametros es el correcto para la accion
	// devuelve true si todo es correcto o false en caso contrario.
	private function _ejecutar() {
		
		$reflectionMethod = new ReflectionMethod($this->control, $this->accion);
		$num_params = count( $this->parametros );
		// Distinto número de parametros
		if ( $num_params < $reflectionMethod->getNumberOfRequiredParameters() ||
         $num_params > $reflectionMethod->getNumberOfParameters() ) {
				return false;
    }else{
				try {
						//echo "OK"; 
						//cuando el controlador hace echos...
						ob_start(); 
						
						$reflectionMethod->invokeArgs($this->control, $this->parametros);
						$contenido = ob_get_contents();
						ob_end_clean();
						Mostrar::addCuerpo($contenido);
						//exit('YA');
						
						
						return true;
						
						
						
				} catch (ReflectionException $e) {
						return false;
				}
		}
		
	}
	
	////////////////////////////////////////////////
	// FUNCIONES PUBLICAS
	////////////////////////////////////////////////
	
	public function lanzar() {
		
		session_name("Gato");
		session_start();
		$es_404 = false;
		// Si existe la URL en la web
		if ( $this->_existe_url() && $this->_ejecutar() ) {
			
			//exit('33');
			
					extract(get_object_vars($this->control), EXTR_OVERWRITE);
					$_pagina = RUTA_APP . "paginas" . DIRECTORY_SEPARATOR . $this->controlador . DIRECTORY_SEPARATOR .  Mostrar::pagina() . '.phtml';
					$_marco = RUTA_APP . 'marcos' . DIRECTORY_SEPARATOR . Mostrar::marco() . '.phtml';
					Mostrar::setControl($this->control);
			
					if ( !is_file($_pagina) ) {
						RegistroLog::log("Linea:" . __LINE__ . ") Vista no encomtrada: $_pagina");
					} else {
						ob_start(); 
						include $_pagina;
						$contenido = ob_get_contents();
						ob_end_clean();
						Mostrar::addCuerpo($contenido);
					}
			
					if (!is_file($_marco)){
							RegistroLog::log("Linea:" . __LINE__ . ") Marco no encomtrado: $_marco");
					}else{
						ob_start();
						include $_marco;
						$contenido = ob_get_contents();
						ob_end_clean();
						Mostrar::addRespuesta($contenido);
					}
					
		// Es un ERROR 404
		}else{
			$es_404 = true;
			$this->controlador = "E404";
			
			//ob_start();
			
			include_once ( RUTA_APP . "controles" . DIRECTORY_SEPARATOR . $this->controlador . '.php' );        
			//$contenido = ob_get_contents();
				//	ob_end_clean();
				//	Mostrar::addCuerpo($contenido);
			
			$this->control = new $this->controlador();
			$reflectionMethod = new ReflectionMethod($this->control, $this->accion);
			$num_params = count( $this->parametros );
		// Distinto número de parametros
			if ( $num_params < $reflectionMethod->getNumberOfRequiredParameters() ||
									$num_params > $reflectionMethod->getNumberOfParameters() ) {
					RegistroLog::log("roto num_params 404");
			}else{
					try {
							$reflectionMethod->invokeArgs($this->control, $this->parametros);
					} catch (ReflectionException $e) {
							RegistroLog::log("roto reflectionMethod 404");
					}
			}			
			extract(get_object_vars($this->control), EXTR_OVERWRITE);
			$_pagina = RUTA_APP . "paginas" . DIRECTORY_SEPARATOR . $this->controlador . DIRECTORY_SEPARATOR .  Mostrar::pagina() . '.phtml';
			$_marco = RUTA_APP . 'marcos' . DIRECTORY_SEPARATOR . Mostrar::marco() . '.phtml';
			Mostrar::setControl($this->control);
			
			if ( !is_file($_pagina) ) {
                RegistroLog::log("Linea:" . __LINE__ . ") 404 Vista no encomtrada: $_pagina");
            } else {
                ob_start(); 
                include $_pagina;
                $contenido = ob_get_contents();
                ob_end_clean();
                Mostrar::addCuerpo($contenido);
            }

            if (!is_file($_marco)){
                RegistroLog::log("Linea:" . __LINE__ . ") 404 Marco no encomtrado: $_marco");
            }else{
                ob_start();
                include $_marco;
                $contenido = ob_get_contents();
                ob_end_clean();
                //Mostrar::addCuerpo($contenido);
                Mostrar::addRespuesta($contenido);
            }
		}
		
		Mostrar::respuesta( $es_404 );
		//exit('22');
	}
	
	// URL
	public function get_peticion_url(){
		return $this->peticionUrl;
	}
	
	// SESION
	public function set_expiracion_cookie( $segundos = 3600 ){
		ini_set("session.cookie_lifetime",$segundos);
		ini_set("session.gc_maxlifetime",$segundos);
		$this->expiracionCookie = $segundos;
	}
	
	public function get_expiracion_cookie(){
		return $this->expiracionCookie;
	}
	
	// ERRORES
	public function set_notificar_errores( $notificar = true ){
		if($notificar){
			error_reporting( E_ALL | E_STRICT );
			ini_set('display_errors', "On");
		}else{
			error_reporting(0);
			ini_set('display_errors', "Off");
		}
		$this->notificarErrores = $notificar;
	}
	
	public function get_notificar_errores(){
		return $this->notificarErrores;
	}
   
} 