<?php

namespace ucms\visualiser\managers;

class VisualiserManager extends \ultimo\orm\Manager {
	
  public function init() {
    $this->registerModelNames(array('Image'));
  }
  
  public function getImages($modelName, $visualised_id) {
    $query = $this->selectAssoc($modelName)
                  ->calcFoundRows('image_count')
                  ->where('@visualised_id = :visualised_id')
                  ->order('@id', 'ASC');
    
    $images = $query->fetch(array(
      ':visualised_id' => $visualised_id)
    );
    
    return $images;
  }
  
  public function getImagesCount($modelName, $visualised_id) {
    return $this->selectAssoc($modelName)
                  ->where('@visualised_id = :visualised_id')
                  ->count(array(':visualised_id' => $visualised_id));
  }
  
}