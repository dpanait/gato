<?php

class ControlBase {
  
  protected $title = "";
  protected $description = "";
  protected $keywords = "";
  protected $device = "";  
  
  public function  __construct ( ) {
	  
  }
  
  public function getTitle ( ) {
    return $this->title;
  }
  
  public function getDescription ( ) {
    return $this->description;
  }
  
  public function getKeywords ( ) {
    return $this->keywords;
  }
  
  public function getDevice ( ) {
    return $this->device;
  }
  
}