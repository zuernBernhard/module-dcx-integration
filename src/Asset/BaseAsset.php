<?php

namespace Drupal\dcx_integration\Asset;

abstract class BaseAsset {

  protected $data;
  private $attributes = [];

  public function __construct($attributes, $data) {
   $this->attributes = $attributes;

   foreach ($this->attributes as $attribute) {
     if (! isset($data[$attribute])) {
       throw new \Exception("Attribute $attribute is mandatory in " . __METHOD__);
     }
    }

    $this->data = $data;
  }

  public function __get($name) {
    if (isset($this->attributes) && isset($data[$name])) {
      return $data[$name];
    }
    throw new \Exception("Attribute $name is inaccessible.");
  }

}
