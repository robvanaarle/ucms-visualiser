<?php

namespace ucms\visualiser\views\general\helpers;

class Visualiser extends \ultimo\phptpl\mvc\Helper {

  protected $imageModelName;
  
  
  
  public function __invoke($imageModelName) {
    $this->imageModelName = $imageModelName;
    return $this;
  }
  
  public function imageUri(array $image, $category) {
    return $this->module->getPlugin('helper')->getHelper('Visualiser')->getImageUri($this->imageModelName, $image, $category);
  }
  
  public function config($key=null) {
    return $this->module->getPlugin('helper')->getHelper('Visualiser')->getViewConfig($this->imageModelName, $key);
  }
}