<?php

/**
 * @file
 * Contains \Drupal\dcx_integration\JsonClient.
 */

namespace Drupal\dcx_integration;

use Drupal\dcx_integration\Asset\Image;
use Drupal\dcx_integration\Asset\Article;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

require drupal_get_path('module', 'dcx_integration') . '/api_client/dcx_api_client.class.php';

/**
 * Class Client.
 *
 * @package Drupal\dcx_integration
 */
class JsonClient implements ClientInterface {
  use StringTranslationTrait;

  /* Instance of the low level PHP JSON API Client provided by digicol.
   *
   * See file api_client/dcx_api_client.class.php
   *
   * @var \DCX_Api_Client
   */
  protected $api_client;

  /**
   * Constructor.
   */
  public function __construct(ConfigFactory $config_factory, TranslationInterface $string_translation) {
    $this->stringTranslation = $string_translation;

    $config = $config_factory->get('dcx_integration.jsonclientsettings');

    $url = $config->get('url');
    $username = $config->get('username');
    $password = $config->get('password');

    $this->api_client = new \DCX_Api_Client($url, $username, $password);

  }

  public function getObject($url, $params = []) {
    $json = NULL;

    // @TODO: Default params for now. Handle custom params.
    //$params = 's[fields]=*&s[files]=*&s[_referenced][dcx%3Afile][s][properties]=*';
    $params = [
      // All fields
      's[fields]' => '*',
      // All files
      's[files]'=> '*',
      // attribute _file_absolute_url of all referenced files in the document
      's[_referenced][dcx:file][s][properties]' => '_file_url_absolute',
    ];

    $http_status = $this->api_client->getObject($url, $params, $json);
    if (200 !== $http_status) {
      $message = $this->t('Error getting %url. Status code was %code.', ['%url' => $url, '%code' => $http_status]);
      throw new \Exception($message);
    }

    if (preg_match('/^doc/', $url)) {
      $type = $this->extractData(['fields', 'Type', 0, '_id'], $json);

      // Evaluate data and decide what kind of asset we have here
      if ("dcxapi:tm_topic/documenttype-image" == $type) {
        return $this->buildImageAsset($json);
      }
      if ("dcxapi:tm_topic/documenttype-story" == $type) {
        return $this->buildStoryAsset($json);
      }
    }
    else {
      throw new \Exception('No handler for URL type $url.');
    }

  }

  protected function buildImageAsset($json) {
    $data = [];

    /**
     * Maps an asset attribute to
     *  - the keys of a nested array, or
     *  - to a callback with arguments for further processing
     */
    $attribute_map = [
      'id' => ['_id'],
      'filename' => ['fields', 'Filename', 0, 'value'],
      'title' => ['fields', 'Title', 0, 'value'],
      'url' => [[$this, 'extractUrl'], ['files', 0, '_id']],
    ];

    foreach ($attribute_map as $target_key => $source) {
      if (is_array($source[0]) && method_exists($source[0][0], $source[0][1])) {
        $data[$target_key] = call_user_func($source[0], $source[1], $json);
      }
      elseif (is_array($source)) {
        $data[$target_key] = $this->extractData($source, $json);
      }
    }

    return new Image($data);
  }

  protected function extractData($keys, $json) {
    foreach ($keys as $key) {
      $json = $json[$key];
    }
    return $json;
  }

  protected function extractUrl($keys, $json) {
    $file_id = $this->extractData($keys, $json);

    $file_url = $this->extractData(['_referenced', 'dcx:file', $file_id, 'properties', '_file_url_absolute'], $json);
    return $file_url;
  }

  protected function buildStoryAsset($json) {
    // @TODO
    throw new \Exception(__METHOD__ . " is not implemented yet");
  }

}
