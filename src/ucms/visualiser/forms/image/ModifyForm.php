<?php

namespace ucms\visualiser\forms\image;

class ModifyForm extends \ultimo\form\Form {
  
  protected function init() {
    $this->appendValidator('image', 'NotEmpty');
    $this->appendValidator('label', 'StringLength', array(null, 10));
    $this->appendValidator('image', 'ImageType', array(array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG)));
  }
}