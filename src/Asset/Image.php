<?php

namespace Drupal\dcx_integration\Asset;

class Image extends BaseAsset {
  static $attributes = ['id', 'filename', 'title', 'url'];

  public function __construct($data) {
    parent::__construct(self::$attributes, $data);
  }
}
