<?php
	
	class Mostrar {

	private static $peticion = '/';
    private static $respuesta = '';
    private static $marco = 'base';
    private static $vista = 'index';
    private static $elementos = [];
	private static $cuerpos = [];
	private static $control = NULL;
    private static $cabecera_pintada = FALSE;

	private static $list_js = [];
	private static $list_css = [];

  //public static function pagina404 () {
		/*$_SESSION['_peticion_'] = $_GET['_peticion_'];
		header("HTTP/1.0 404 Not Found");
		header("Status: 404 Not Found");
		header ("Location: /e404");*/
		//include  RUTA_APP . "paginas" . DIRECTORY_SEPARATOR . 'e404' . DIRECTORY_SEPARATOR . 'e404.phtml';
  //}

	// recibir que petición se ha hecho
	public static function setPeticion( $peticion = "/" ) {
		self::$peticion = $peticion;
	}

	// Añade a la respuesta más contenidos
	public static function addRespuesta( $contenido = "" ){
		self::$respuesta .= $contenido;
	}

	// Envia la respuesta
	public static function respuesta ( $es_404 ) {

		self::$respuesta = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', self::$respuesta);
		self::$respuesta = trim(preg_replace('!\s+!', ' ', self::$respuesta),"\s\t\n\r\0\x0B");

		$etagFile = md5( self::$respuesta );
		$etagHeader = ( isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false );

		if( $es_404 ){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
		}else if ( $etagHeader == $etagFile ) {
			header("HTTP/1.1 304 Not Modified");
			exit(0);
		}

		header('X-Powered-By: gato v1');

		if ( ob_start("ob_gzhandler") ) {
			header('Content-Encoding: gzip');
		}
      //exit('22');
		header("Etag: $etagFile");
		header('Cache-Control: public');

		// un dia 86400 segundos
		// un mes 2592000 segundos
		header('Expires: ' . gmdate('D, d M Y H:i:s ', time() + 2592000) . 'GMT');
		// Set the correct MIME type, because Apache won't set it for us
        header("Content-type: text/html; charset=utf-8");

        // quedan cabeceras...
        //if( count(self::$elementos) > 0 ){
            //self::$respuesta = preg_replace('/<\/head>/', implode('',self::$elementos).'</head>', self::$respuesta);
      //}

		echo self::$respuesta;
		exit(0);
	}

	public static function drawJs(){
		if( count(self::$list_js) > 0 ){
			foreach(self::$list_js AS $js){
				echo $js;
			}
		}
	}
	public static function addJs( $elemento ){
		if(!empty($elemento)){
			self::$list_js[] = $elemento;
		}
	}
	public static function drawCss(){
		if( count(self::$list_css) > 0 ){
			foreach(self::$list_css AS $css){
				echo $css;
			}
		}
	}
	public static function addCss( $elemento ){
		if(!empty($elemento)){
			self::$list_css[] = $elemento;
		}
	}

    // Añade etiquetas en el <head></head> del documento html
    // ejemplo
	public static function addCabecera( $elemento ){
        //echo $elemento;
        if( !empty($elemento) ){
            self::$elementos[] = $elemento;
            if( self::$cabecera_pintada ){
                // añadir al final de la cabecera
            }
		}
	}

	public static function addParte($parte){
		$parte = RUTA_APP . 'marcos' . DIRECTORY_SEPARATOR . "partes". DIRECTORY_SEPARATOR . $parte . '.phtml';

        if ( is_file($parte) ) {
                extract(get_object_vars(self::$control), EXTR_OVERWRITE);
                include $parte;
        }
	}

	public static function addCuerpo( $cuerpo ){
		if ( !empty($cuerpo) ) {
			self::$cuerpos[] = $cuerpo;
		}
	}

	public static function cabecera(){
        //var_dump(self::$elementos);
        self::$cabecera_pintada = TRUE;
        $title = self::$control->getTitle();
		$description = self::$control->getDescription();
		$keywords = self::$control->getKeywords();
		self::$elementos[] = "<title>$title</title>";
		self::$elementos[] = '<meta content="' . htmlspecialchars($description, ENT_QUOTES, "UTF-8") . '" name="description">';
		self::$elementos[] = '<meta content="' . $keywords . '" name="keywords">';

		if (count(self::$elementos) > 0) {
			foreach(self::$elementos AS $elemento) {
                echo $elemento . PHP_EOL;// chr(13) . chr(10);
                //Mostrar::addRespuesta($elemento);
            }
            self::$elementos = [];
        }
    }

	public static function cuerpo () {
		if (count(self::$cuerpos) > 0) {
			foreach(self::$cuerpos AS $cuerpo) {
                echo $cuerpo;
                //Mostrar::addRespuesta($cuerpo);
			}
    }
  }

	public static function marco() {
		return self::$marco;
	}

	public static function pagina() {
		return self::$vista;
	}

	public static function setMarco( $marco = 'base' ) {
		self::$marco = $marco;
	}

	public static function setPagina( $pagina = 'index' ) {
		self::$vista = $pagina;
	}

	public static function setControl( $control ) {
		self::$control = $control;
	}


	public static function cleanCss($filename){

	}
}
