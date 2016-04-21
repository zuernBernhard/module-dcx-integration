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
   * JSON client settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;
  /**
   * Constructor.
   */

  /**
   * Publication ID from 'dcx_integration.jsonclientsettings'
   *
   * @var string
   */
  protected $publication_id;

  /**
   * Constructor.
   */
  public function __construct(ConfigFactory $config_factory, TranslationInterface $string_translation, $override_client_class = NULL) {

    $this->stringTranslation = $string_translation;

    $this->config = $config_factory->get('dcx_integration.jsonclientsettings');

    if (!$override_client_class) {
      $url = $this->config->get('url');
      $username = $this->config->get('username');
      $password = $this->config->get('password');
      $this->api_client = new \DCX_Api_Client($url, $username, $password);
    }
    else {
      $this->api_client = $override_client_class;
    }

    $this->publication_id = $this->config->get('publication');
  }

  /**
   * This is public only for debugging purposes.
   *
   * It's not part of the interface, it should be protected.
   * It really shouldn't be called directly.
   */
  public function getJson($id, $params = NULL) {
    $json = NULL;

    if ($params == NULL) {
      $params = [
        's[pubinfos]' => '*',
        // All fields
        's[fields]' => '*',
        // All properties
        's[properties]' => '*',
        // All files
        's[files]'=> '*',
        // attribute _file_absolute_url of all referenced files in the document
        's[_referenced][dcx:file][s][properties]' => '_file_url_absolute',

        's[_referenced][dcx:pubinfo][s]' => '*',
      ];
    }

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

  /**
   * {@inheritdoc}
   */
  public function trackUsage($dcx_ids, $path, $published) {
    $dcx_status = $published?'pubstatus-published':'pubstatus-planned';

    $dateTime = new \DateTime();
    $date = $dateTime->format(\DateTime::W3C);
    // 1. Find all documents with a usage of on url.
    // non yet

    $dcx_publication = $this->publication_id;

    $known_publication = pubinfoOnPath($path);

    // Expand given relative URL to absolute URL.

    foreach($dcx_ids as $id) {
      $data = [
        "_type" => "dcx:pubinfo",
        "properties" => [
          "doc_id" => [
              "_id" => $id,
              "_type" => "dcx:document"
          ],
          "uri" => $path,
          "date" => $date,
          "status_id" => [
              "_id" => "dcxapi:tm_topic/$dcx_status",
              "_type" => "dcx:tm_topic",
              "value" => "Published"
          ],
          "publication_id" => [
              "_id" => "dcxapi:tm_topic/$dcx_publication",
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

      //$pubinfo = $this->getRelevantPubinfo($id, $url);

      if (count($pubinfo) > 1) {
        throw new \Exception($this->t('For document !id exists more that one '
          . 'pubinfo refering to %url. This should not be the case and cannot '
          . 'be resolved manually. Please fix this in DC-X.',
          ['%id' => $id, '%url' => $path]));
      }
      if (0 == count($pubinfo)) {
        $http_status = $this->api_client->createObject('pubinfo', [], $data, $response_body);
        if (201 !== $http_status) {
          $message = $this->t('Error creating object %url. Status code was %code.', ['%url' => pubinfo, '%code' => $http_status]);
          throw new \Exception($message);
        }
      }
      else { // 1 == count($pubinfo)
        $pubinfo = current($pubinfo);
        $dcx_api_url = preg_replace('/dcxapi:/', '', $pubinfo['_id']);

        $modcount = $pubinfo['properties']['_modcount'];
        $data['properties']['_modcount'] = $modcount;
        $data['_id'] = $pubinfo['_id'];

        $http_status = $this->api_client->setObject($dcx_api_url, [], $data, $response_body);
        if (200 !== $http_status) {
          $message = $this->t('Error setting object %url. Status code was %code.', ['%url' => $dcx_api_url, '%code' => $http_status]);
          throw new \Exception($message);
        }
      }
    }

  }

  /**
   * PROBABLY OBSOLETE
   *
   * Retrieve pubinfo of the given DC-X id, which is relevant
   * for the given article url.
   *
   * As no one can prevent users from adding a pubinfo manually for our URL
   * this will always return a list of relevant pubinfo entries, even if there's
   * suppose to be only one.
   *
   * @param string $dcx_id DC-X document ID
   * @param string $url absolute canonical URL of the article
   *
   * @return array list of relevant pubinfo as it comes from DC-X
   */
  protected function getRelevantPubinfo($dcx_id, $url) {
    $json = $this->getJson($dcx_id, ['s[pubinfos]' => '*', 's[_referenced][dcx:pubinfo][s]' => '*'] );

    $relevant_entries = [];
    foreach($json['_referenced']['dcx:pubinfo'] as $pubinfo_id => $pubinfo) {
      // We're not interested in pubinfo without uri.
      if (! isset($pubinfo['properties']['uri'])) { continue; }

      // We're not interested in pubinfo on any other than our URI
      if ($url !== $pubinfo['properties']['uri'] ) { continue; }
      $relevant_entries[$pubinfo_id] = $pubinfo;
    }
    return $relevant_entries;
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

    if (NULL != $dcx_id) {
      $json = $this->getJson($dcx_id);
      $modcount = $json['properties']['_modcount'];
      $data['properties']['_modcount'] = $modcount;
      $data['_id'] = '/dcx/api/' . $dcx_id;
      $dcx_api_url = $dcx_id;
      $this->api_client->setObject($dcx_api_url, [], $data, $response_body);
    }
    else {
      $dcx_api_url = 'document';
      $this->api_client->createObject($dcx_api_url, [], $data, $response_body);
    }

    $error = FALSE;

    if (!$response_body) {
      $message = $this->t('The operation yielded no result.');
      $error = TRUE;
    }

    if (!$error && !isset($response_body['_type'])) {
      $message = $this->t('The result operation has no type.');
      $error = TRUE;
    }

    if (!$error && $response_body['_type'] !== 'dcx:success') {
      $message = $response_body['_type'];
      if (isset($response_body['title'])) {
        $message .= ":: " . $response_body['title'];
      }
      $error = TRUE;
    }

    if (!$error && !isset($response_body['location'])) {
      $message = $this->t('The operation was successful, but key location was not found.');
      $error = TRUE;
    }

    if (!$error && preg_match('|/dcx/api/(document/doc.*)|', $response_body['location'], $matches)) {
      $dcx_id = $matches[1];
    }
    else {
      if (!$error) {
      $message = $this->t('The operation was successful, but the location was not parseable.');
      $error = TRUE;
      }
    }

    if ($error) {
      throw new \Exception($this->t("Unable to archive %url, %message ", ['%url' => $url, '%message' => $message]));
    }

    return $dcx_id;
  }


  /**
   * {{@inheritdoc}}
   */
  public function pubinfoOnPath($path) {
      $params = [
        'q[uri]' => $path,
        's[properties]' => '*',
        'q[_limit]' => '*',
      ];

    $http_status = $this->api_client->getObject('pubinfo', $params, $json);
    if (200 !== $http_status) {
      $message = $this->t('Error setting object %url. Status code was %code.', ['%url' => $dcx_api_url, '%code' => $http_status]);
      throw new \Exception($message);
    }

    $pubinfo = [];
    foreach ($json['entries'] as $entry) {
      // ignore entry, if the publication id of this entry does not match ours.
      if ("dcxapi:tm_topic/" . $this->publication_id !== $entry['properties']['publication_id']['_id']) {
        continue;
      }
      $doc_id = $entry['properties']['doc_id']['_id'];
      $id = $entry['_id'];
      $pubinfo[$doc_id][$id] = $entry;
    }

    return $pubinfo;
  }
}
