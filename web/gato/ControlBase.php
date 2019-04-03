<?php

class ControlBase {

  protected $title = "";
  protected $description = "";
  protected $keywords = "";
  protected $device = "";
  protected $db = "";

  public function __construct(){
  }

  public function get_title(){
    return $this->title;
  }

  public function get_description(){
    return $this->description;
  }

  public function get_keywords(){
    return $this->keywords;
  }

  public function get_device(){
    return $this->device;
  }
  public function get_db(){
    $config =  parse_ini_file(RUTA_CONFIG . "config.ini", true);
    $connection = new PDO('mysql:host='.$config['database']['host'].';dbname='. $config['database']['dbname'], $config['database']['dbuser'], $config['database']['dbpassword']);

    $db = new \ClanCats\Hydrahon\Builder('mysql', function($query, $queryString, $queryParameters) use($connection)
    {
        $statement = $connection->prepare($queryString);
        $statement->execute($queryParameters);
    
        if ($query instanceof \ClanCats\Hydrahon\Query\Sql\FetchableInterface)
        {
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        }
    });
      $this->db = $db;
    return $this->db;
  }

}