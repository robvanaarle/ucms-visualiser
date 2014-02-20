<?php

namespace ucms\visualiser\helpers;

class Visualiser extends \ultimo\mvc\plugins\ModuleHelper {
  
  public function getManager($imageModelName) {
  	$manager = $this->module->getPlugin('uorm')->getManager('Visualiser');
  	$manager->associateModel(
  	  $this->module->getPlugin('uorm')->getTableIdentifier($imageModelName),
  	  $imageModelName,
  	  $this->module->getPlugin('uorm')->getModelClass($imageModelName)
  	);
  	return $manager;
  }
  
  public function getImages($imageModelName, $visualised_id) {
    return $this->getManager($imageModelName)->getImages($imageModelName, $visualised_id);
  }
  
  protected function replaceVars($subject, array $replacements) {
    $search = array();
    $replace = array();
    foreach ($replacements as $name => $value) {
      $search[] = '%' . $name . '%';
      $replace[] = $value;
    }
    return str_replace($search, $replace, $subject);
  }
  
  public function getViewConfig($imageModelName, $key=null) {
  	$defaultConfig = $this->module->getPlugin('config')->getViewConfig('visualiser');
    $config = $this->module->getPlugin('config')->getViewConfig(strtolower($imageModelName));
    $config = $this->module->getPlugin('config')->mergeConfigs($defaultConfig, $config);
    
    $replacements = array(
        'module_name' => $this->module->getName(),
        'image_model_name' => strtolower($imageModelName),
        'application_dir' => $this->application->getApplicationDir()
    );
    
    $replaceConfigKeys = array('public_image_path', 'public_image_uri');
    foreach ($replaceConfigKeys as $configKey) {
      $config[$configKey] = $this->replaceVars($config[$configKey], $replacements);
    }
    
    if ($key !== null) {
      return $config[$key];
    }
    
    return $config;
  }
  
  public function resizeImageByProps(\ultimo\graphics\gd\Image $image, array $props) {
    
    // exact_width, exact_height, max_width, max_height, max_width_max_height
    
    
    $maxWidth = null;
    $fixedWidth = null;
    if (isset($props['width_type']) && isset($props['width'])) {
      if ($props['width_type'] == 'fixed') {
        $fixedWidth = $props['width'];
      } elseif ($props['width_type'] == 'max') {
        $maxWidth = $props['width'];
      }
    }
    
    $maxHeight = null;
    $fixedHeight = null;
    if (isset($props['height_type']) && isset($props['height'])) {
      if ($props['height_type'] == 'fixed') {
        $fixedHeight = $props['height'];
      } elseif ($props['height_type'] == 'max') {
        $maxHeight = $props['height'];
      }
    }
    
    if ($maxWidth !== null || $maxHeight !== null) {
      $image->scale($maxWidth, $maxHeight);
    } else {
      $image->resize($fixedWidth, $fixedHeight);
    }
    
    return $image;
  }
  
  public function normalizeLabel($label) {
    return preg_replace('/[^a-zA-Z0-9\-]*/', '', $label);
  }
  
  public function getImageUri($imageModelName, array $image, $category) {
    $config = $this->getViewConfig($imageModelName);
    $replacements = array('category' => $category, 'id' => $image['id'], 'label' => $this->normalizeLabel($image['label']), 'extension' => $image['extension']);
    return $this->replaceVars($config['public_image_uri'], $replacements);
  }
  
  public function getImagePath($imageModelName, array $image, $category) {
    $config = $this->getViewConfig($imageModelName);
    $replacements = array('category' => $category, 'id' => $image['id'], 'label' => $this->normalizeLabel($image['label']), 'extension' => $image['extension']);
    return $this->replaceVars($config['public_image_path'], $replacements);
  }
  
  public function saveUploadedImageFile($imageModelName, \ultimo\net\http\php\sapi\UploadedFile $file, array $image) {
    $config = $this->getViewConfig($imageModelName);
    
    if (!isset($config['category'])) {
      return;
    }
    
    $original = \ultimo\graphics\gd\Image::createFromFile($file->tmp_name);
    
    foreach ($config['category'] as $name => $props) {
      $filePath = $this->getImagePath($imageModelName, $image, $name);
      
      $dirPath = dirname($filePath);
      if (!file_exists($dirPath) && !mkdir($dirPath, 0777, true)) {
        throw new VisualiserException("Could not create image directory {$dirPath}.");
      }
      
      $clone = clone $original;
      $clone = $this->resizeImageByProps($clone, $props);
      
      $quality = null;
      if ($clone->getType() == \ultimo\graphics\gd\Image::TYPE_JPEG) {
        $quality = 100;
      }
      
      $clone->toRaw($filePath, $quality);
      unset($clone);
    }
  }
  
  public function deleteImageFiles($imageModelName, array $image) {
    $config = $this->getViewConfig($imageModelName);
    if (!isset($config['category'])) {
      return;
    }
    
    foreach ($config['category'] as $name => $props) {
      $filePath = $this->getImagePath($imageModelName, $image, $name);
      @unlink($filePath);
    }
    
  }
  
}