<?php

namespace ucms\visualiser\models;

abstract class Image extends \ultimo\orm\Model {
  public $id;
  public $label = '';
  public $extension = '';
  public $visualised_id = '';
  
  static protected $fields = array('id', 'label', 'extension', 'visualised_id');
  static protected $primaryKey = array('id');
  static protected $autoIncrementField = 'id';
  
  /* you can define something like below in your comment model
  static protected $relations = array(
    'commentor' => array('User', array('commentor_id' => 'id'), self::MANY_TO_ONE),
    'message' => array('Commente', array('commente_id' => 'id', 'locale' => 'locale'), self::MANY_TO_ONE)
  );
  */

}