<?php

namespace Drupal\dcx_integration\Asset;

class Article extends BaseAsset {
  static $attributes = ['id', 'title', 'headline', 'body'];

  public function __construct($data) {
    parent::__construct(self::$attributes, $data);
  }
}
