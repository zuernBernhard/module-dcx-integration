<?php

/**
 * @file
 * Contains \Drupal\dcx_integration_debug\MockClient.
 */

namespace Drupal\dcx_integration_debug;

use Drupal\dcx_integration\Asset\Image;
use Drupal\dcx_integration\Asset\Article;
use Drupal\dcx_integration\ClientInterface;

/**
 * Class Client.
 *
 * @package Drupal\dcx_integration_debug
 */
class MockClient implements ClientInterface {

  /**
   * The mock client extracts an int from the first argument and evaluates it.
   * If it's divisible by 3 it's an article, if it's divisible by 2 it's an
   * image.
   */
  public function getObject($url, $params = []) {
    if (preg_match('/^doc/', $url)) {
      $type = filter_var($url, FILTER_SANITIZE_NUMBER_INT);
      $type += 2;

      // Evaluate data and decide what kind of asset we have here
      if (0 == $type%3) {
        return $this->buildStoryAsset($url);
      }
      if (0 == $type%2) {
        return $this->buildImageAsset($url);
      }
    }
    else {
      throw new \Exception('No handler for URL type $url.');
    }
  }

  protected function buildImageAsset($url) {
    global $base_url;

    $data['id'] = 'mock:' . $url;
    $data['title'] = "Mocked image $url";
    $data['filename'] = 'mockimg.png';
    $data['url'] = $base_url . '/core/themes/bartik/screenshot.png';

    return new Image($data);
  }

  protected function buildStoryAsset($url) {
    $data['id'] = 'mock:' . $url;
    $data['title'] = "Mocked article $url";
    $data['headline'] = 'Fake news!';
    $data['body'] = "Eine wunderbare Heiterkeit hat meine ganze Seele"
      . " eingenommen, gleich den süßen Frühlingsmorgen, die ich mit ganzem"
      . " Herzen genieße. Ich bin allein und freue mich meines Lebens in dieser"
      . " Gegend, die für solche Seelen geschaffen ist wie die meine.";

    return new Article($data);
  }
}
