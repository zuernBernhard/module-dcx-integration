<?php

/**
 * @file
 * Contains \Drupal\dcx_integration\JsonClient.
 */

namespace Drupal\dcx_integration;

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

  public function getDocument($url, $params = []) {
    $data = NULL;

    $http_status = $this->api_client->getObject($url, $params, $data);

    if (200 !== $http_status) {
      $message = $this->t('Error getting %url. Status code was %code.', ['%url' => $url, '%code' => $http_status]);
      throw new \Exception($message);
    }

    return $data;
  }

}
