<?php

class ControlBase {

  protected $title = "";
  protected $description = "";
  protected $keywords = "";
  protected $device = "";

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

}