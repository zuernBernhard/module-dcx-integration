<?php

/**
 * @file
 * Contains \Drupal\dcx_integration_debug\MockClient.
 */

namespace Drupal\dcx_integration_debug;

use Drupal\dcx_integration\Asset\Image;
use Drupal\dcx_integration\Asset\Article;
use Drupal\dcx_integration\ClientInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Class Client.
 *
 * @package Drupal\dcx_integration_debug
 */
class MockClient implements ClientInterface {
  use StringTranslationTrait;


  public function getObject($url, $params = []) {
    return new \stdClass(['mocked' => true]);
  }

}
