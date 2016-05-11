<?php

namespace Drupal\Tests\dcx_integration\Unit;

use Drupal\dcx_integration\JsonClient;
use Drupal\Tests\dcx_integration\DummyClass;
use Drupal\Tests\UnitTestCase;

/**
 * @group dcx
 */
class DcxJsonClientTest extends UnitTestCase {
  protected $client;

  protected $api_client;
  /*
//(ConfigFactory $config_factory,
//AccountProxy $user,
//TranslationInterface
//$string_translation,
//$override_client_class = NULL) {
  */

  function setUp() {
    $jsonclientsettings = ['publication' => 'dummy_publication'];
    $config_factory = $this->getConfigFactoryStub(['dcx_integration.jsonclientsettings' => $jsonclientsettings]);
    $user = $this->getMock('\Drupal\Core\Session\AccountProxyInterface');
    $stringTranslation = $this->getStringTranslationStub();
    $this->api_client = new DummyClass();
    $this->api_client->getObject = function() {
      return 200;
    };

    $this->client = new JsonClient($config_factory, $user, $stringTranslation, $this->api_client);
  }

  function testGetJson() {
     $retval = $this->client->getJson('dcxapi:id');
     list($url, $params, $bogus_response_reference) = $this->api_client->args;
     $this->assertEquals($url, 'id', 'Client disposes "dcxapi:" part of the id.');
     $this->assertNotEmpty($params, 'If no params are given, default params are used.');

     $retval = $this->client->getJson('dcxapi:id', ['params']);
     list($url, $params, $bogus_response_reference) = $this->api_client->args;
     $this->assertArrayEquals(['params'], $params, 'If params are given, they are actually used');
  }
}
