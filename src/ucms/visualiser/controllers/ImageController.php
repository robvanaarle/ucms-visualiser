<?php

namespace ucms\visualiser\controllers;

abstract class ImageController extends \ultimo\mvc\Controller {
  	/**
   * @var ucms\visualiser\managers\VisualiserManager
   */
  protected $visualiserMgr;
  
  /**
   *
   * @var type ucms\visualiser\helpers\Visualiser
   */
  protected $vHelper;
  
  protected $visualisedControllerName;
  protected $visualisedModelName;
  protected $imageModelName;
  
  protected function init() {
    $this->visualisedModelName = $this->detectVisualisedModelName();
    $this->visualisedControllerName = strtolower($this->visualisedModelName);
    $this->imageModelName = $this->visualisedModelName . 'Image';
    
    $this->view->visualisedControllerName = strtolower($this->imageModelName);
    $this->view->imageControllerName = strtolower($this->getName());
    
  }
  
  protected function beforeAction($actionName) {
    $this->vHelper = $this->module->getPlugin('helper')->getHelper('Visualiser');
    $this->visualiserMgr = $this->vHelper->getManager($this->imageModelName);
    
  	$this->application->getPlugin('viewRenderer')->setController('image');
  }
  
  protected function detectVisualisedModelName() {
    $controllerName = strtolower($this->getName());
    
    // strip off 'image' postfix
    return ucfirst(substr($controllerName, 0, strlen($controllerName)-5));
  }
  
  abstract protected function getVisualised($visualised_id);
  
  public function actionIndex() {
    $visualised_id = $this->request->getParam('visualised_id');
    $visualised = $this->getVisualised($visualised_id);
    if ($visualised === null) {
      throw new \ultimo\mvc\exceptions\DispatchException("Visualied with id {$visualised_id} does not exist", 404);
    }
    
    $this->view->images = $this->visualiserMgr->getImages($this->imageModelName, $visualised_id);
    $this->view->visualised_id = $visualised_id;
   }
  
  public function actionCreate() {
    $visualised_id = $this->request->getParam('visualised_id');
    $visualised = $this->getVisualised($visualised_id);
    if ($visualised === null) {
      throw new \ultimo\mvc\exceptions\DispatchException("Visualied with id {$visualised_id} does not exist", 404);
    }
    
    if ($this->vHelper->getViewConfig($this->imageModelName, 'max_images') <= $this->visualiserMgr->getImagesCount($this->imageModelName, $visualised_id)) {
      throw new \ultimo\mvc\exceptions\DispatchException("Max number of images reached.", 404);
    }
    
    $imageForm = $this->module->getPlugin('formBroker')->createForm(
      'image\CreateForm',
      $this->request->getParam('image', array())
    );
    

    if ($this->request->isPost()) {
      $returnUrl = $this->request->getParam('return_url');
      
      if ($imageForm->validate()) {
        $file = $imageForm['image'];

        $config = $this->vHelper->getViewConfig($this->imageModelName);

        $extension = \ultimo\graphics\gd\Image::getExtensionByFile($file->tmp_name);
        
        $image = $this->visualiserMgr->create($this->imageModelName);
        $pathInfo = pathinfo($file->name);
        $image->label = $pathInfo['filename'];
        $image->extension = $extension;
        $image->visualised_id = $visualised_id;
        $image->save();
        
        $this->vHelper->saveUploadedImageFile($this->imageModelName, $file, $image->toArray());
        
        if ($returnUrl === null) {
          $this->getPlugin('redirector')->redirect(array('action' => 'index', 'visualised_id' => $visualised_id));
        } else {
          $this->getPlugin('redirector')->setRedirectUrl($returnUrl);
        }
      }
    } else {
      $returnUrl = $this->request->getParam('return_url', $this->request->getHeader('Referer'));
    }
    
    $this->view->visualised_id = $visualised_id;
    
    $this->view->imageForm = $imageForm;
    $this->view->return_url = $returnUrl;
  }
  
  public function actionDelete() {
    $id = $this->request->getParam('id', 0);
    
    $image = $this->visualiserMgr->get($this->imageModelName, $id);
    if ($image === null) {
      throw new \ultimo\mvc\exceptions\DispatchException("Image with id {$id} does not exist", 404);
    }
    
    $this->vHelper->deleteImageFiles($this->imageModelName, $image->toArray());
		$image->delete();
    
    $returnUri = $this->request->getParam('return_url', $this->request->getHeader('Referer'));
    if ($returnUri === null) {
			$this->getPlugin('redirector')->redirect(array('action' => 'index', 'visualised_id' => $image->visualised_id));
		} else {
			$this->getPlugin('redirector')->setRedirectUrl($returnUri);
		}
  }
  

}