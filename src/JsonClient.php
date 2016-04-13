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

  /**
   * This is public only for debugging purposes.
   *
   * It's not part of the interface, it should be protected.
   * It really shouldn't be called directly.
   */
  public function getJson($id) {
    $json = NULL;

    $params = [
      // All fields
      's[fields]' => '*',
      // All files
      's[files]'=> '*',
      // attribute _file_absolute_url of all referenced files in the document
      's[_referenced][dcx:file][s][properties]' => '_file_url_absolute',
    ];

    $url = preg_replace('/^dcxapi:/', '', $id);
    $http_status = $this->api_client->getObject($url, $params, $json);

    if (200 !== $http_status) {
      $message = $this->t('Error getting %url. Status code was %code.', ['%url' => $url, '%code' => $http_status]);
      throw new \Exception($message);
    }

    return $json;
  }

  /**
   * Retrieve a DC-X object with the given id.
   *
   * Emits an HTTP request to the DC-X server and evaluates the response.
   * Depending on the document "Type" (an attribute stored within the fields,
   * not to be confused with the attribute "type") it returns subclasses of
   * BaseAsset which encapsulate a flat array representation of the data
   * retrieved.
   *
   * @param string $id
   *   A dcx object identifier. Something like "dcxapi:document/xyz".
   * @return Drupal\dcx_integration\Asset\BaseAsset
   *   An instance of BaseAsset depending on the retrieved data.
   * @throws \Exception Throws exceptions if anything fails.
   */
  public function getObject($id) {
    $json = $this->getJson($id);

    if (preg_match('/^dcxapi:doc/', $id)) {
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
      throw new \Exception("No handler for URL type $id.");
    }

  }

  /**
   * Builds an Image object from given json array.
   *
   * @return Drupal\dcx_integration\Asset\Image the Image object.
   */
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

  /**
   * Builds an Article object from given json array.
   *
   * @return Drupal\dcx_integration\Asset\Article the Article object.
   */
  protected function buildStoryAsset($json) {
    // @TODO
    throw new \Exception(__METHOD__ . " is not implemented yet");
  }

  /**
   * Descends in the nested array $json following the path of keys given in
   * keys.
   *
   * Returns whatever it finds there.
   *
   * @param array $keys
   * @param array $json
   * @return mixed $value
   */
  protected function extractData($keys, $json) {
    foreach ($keys as $key) {
      $json = $json[$key];
    }
    return $json;
  }

  /**
   * Returns the URL for the file reference described by $keys.
   *
   * This function "knows" where to look for the URL of the file in question.
   *
   * @param type $keys
   * @param type $json
   * @return type
   */
  protected function extractUrl($keys, $json) {
    $file_id = $this->extractData($keys, $json);

    $file_url = $this->extractData(['_referenced', 'dcx:file', $file_id, 'properties', '_file_url_absolute'], $json);
    return $file_url;
  }

  public function trackUsage($id, $url) {
    $data = [
      "_type" => "dcx:pubinfo",
      "properties" => [
        "doc_id" => [
            "_id" => $id,
            "_type" => "dcx:document"
        ],
        "uri" => $url,
        "status_id" => [
            "_id" => "dcxapi:tm_topic/pubstatus-published",
            "_type" => "dcx:tm_topic",
            "value" => "Published"
        ],
        "publication_id" => [
            "_id" => "dcxapi:tm_topic/publication-default",
            "_type" => "dcx:tm_topic",
            "value" => "Bunte"
        ],
        "type_id" => [
            "_id" => "dcxapi:tm_topic/pubtype-article",
            "_type" => "dcx:tm_topic",
            "value" => "Article"
        ]
      ]
    ];
    dpm($data);
  }

  public function archiveArticle($url, $title, $text, $dcx_id) {
    $params = [
      's[properties]' => '*',
      's[fields]' => '*'
    ];

    $data = [
      '_type' => 'dcx:document',
      'fields' => [
        'Title' => [
          0 => [
            'value' => $title,
          ],
        ],
        'body' => [
          0 => [
            '_type' => 'xhtml',
            'value' => $text,
          ],
        ],
      ],
      'properties' => [
        'pool_id' => [
          '_id' => '/dcx/api/pool/native',
          '_type' => 'dcx:pool',
        ],
      ],
    ];

    $this->api_client->createObject('document', $params, $data, $response_body);
    $type = $response_body['_type'];

    $error = FALSE;

    if (!$response_body) {
      $message = $this->t('The operation yielded no result.');
      $error = TRUE;
    }

    if (!isset($response_body['_type'])) {
      $message = $this->t('The result operation has no type.');
      $error = TRUE;
    }

    if ($response_body['_type'] !== 'dcx:success') {
      $message = $response_body['_type'];
      if (isset($response_body['title'])) {
        $message .= ":: " . $response_body['title'];
      }
      $error = TRUE;
    }

    if (!isset($response_body['location'])) {
      $message = $this->t('The operation was successful, but key location was not found.');
      $error = TRUE;
    }

    if (preg_match('|/dcx/api/(document/doc.*)|', $response_body['location'], $matches)) {
      $dcx_id = $matches[1];
    }
    else {
      $message = $this->t('The operation was successful, but the location was not parseable.');
      $error = TRUE;
    }

    if ($error) {
      throw new \Exception($this->t("Unable to archive %url, %message ", ['%url' => $url, '%message' => $message]));
    }

    return $dcx_id;
  }
}
