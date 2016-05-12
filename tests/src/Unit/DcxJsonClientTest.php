<?php

namespace Drupal\Tests\dcx_integration\Unit;

use Drupal\dcx_integration\JsonClient;
use Drupal\Tests\dcx_integration\DummyDcxApiClient;
use Drupal\Tests\UnitTestCase;

/**
 * @group dcx
 */
class DcxJsonClientTest extends UnitTestCase {
  protected $client;

  protected $api_client;

  function setUp() {
    $jsonclientsettings = ['publication' => 'dummy_publication'];
    $config_factory = $this->getConfigFactoryStub(['dcx_integration.jsonclientsettings' => $jsonclientsettings]);
    $user = $this->getMock('\Drupal\Core\Session\AccountProxyInterface');
    $stringTranslation = $this->getStringTranslationStub();
    $this->api_client = new DummyDcxApiClient();
    $this->api_client->getObject = function() {
      return 200;
    };
    $this->client = new JsonClient($config_factory, $user, $stringTranslation, $this->api_client);
  }

  function testGetJson() {
    $retval = $this->client->getJson('dcxapi:id');
    list($url, $params, $bogus_response_reference) = $this->api_client->args;
    $this->assertEquals($this->api_client->method, 'getObject', 'getObject method of API client is called.');
    $this->assertEquals($url, 'id', 'Client disposes "dcxapi:" part of the id.');
    $this->assertNotEmpty($params, 'If no params are given, default params are passed to the API client.');

    $retval = $this->client->getJson('dcxapi:id', ['params']);
    list($url, $params, $bogus_response_reference) = $this->api_client->args;
    $this->assertArrayEquals(['params'], $params, 'If params are given, they are passed to the API client');

    $this->api_client->getObject = function() {
     return 23;
    };

    $this->setExpectedException('Exception', 'Error getting "id". Status code was 23.');
    $retval = $this->client->getJson('dcxapi:id');
  }

  function testArchiveArticle_emptyResponse() {
    // Expect empty response -> Exception
    $this->api_client->expected_response_body = NULL;
    $this->setExpectedException('Exception', 'Unable to archive node/1, "The operation yielded no result."');
    $this->client->archiveArticle('node/1', [], NULL);
  }

  function testArchiveArticle_invalidResponse() {
    // Expect invalid response -> Exception
    $this->api_client->expected_response_body = 'invalid';
    $this->setExpectedException('Exception', 'Unable to archive node/1, "The result operation has no type."');
    $this->client->archiveArticle('node/1', [], NULL);
  }

  function testArchiveArticle_noSuccess() {
    // Expect response without _type == success -> Exception
    $this->api_client->expected_response_body = ['_type' => 'no success'];
    $this->setExpectedException('Exception', 'Unable to archive node/1, "no success"');
    $this->client->archiveArticle('node/1', [], NULL);
  }

  function testArchiveArticle_noLocation() {
    // Expect response without key location -> Exception
    $this->api_client->expected_response_body = ['_type' => 'dcx:success'];
    $this->setExpectedException('Exception', 'Unable to archive node/1, "The operation was successful, but key location was not found."');
    $this->client->archiveArticle('node/1', [], NULL);
  }

  function testArchiveArticle_invalidLocation() {
    // Expect response without key location -> Exception
    $this->api_client->expected_response_body = ['_type' => 'dcx:success', 'location' => 'invalid'];
    $this->setExpectedException('Exception', 'Unable to archive node/1, "The operation was successful, but the location was not parseable."');
    $this->client->archiveArticle('node/1', [], NULL);
  }

  function testArchiveArticle_newArticle() {
    // Expect response without key location -> Exception
    $this->api_client->expected_response_body = ['_type' => 'dcx:success', 'location' => '/dcx/api/document/docABC'];
    $dcx_id = $this->client->archiveArticle('node/1', [], NULL);
    $this->assertEquals($this->api_client->method, 'createObject');
    $this->assertEquals($dcx_id, 'document/docABC');
  }

  // @TODO fails for strange reason
  function X_testArchiveArticle_exisitingArticle() {
    // Expect response without key location -> Exception
    $this->api_client->expected_response_body = ['_type' => 'dcx:success', 'location' => '/dcx/api/document/docABC'];
    $dcx_id = $this->client->archiveArticle('node/1', [], '123');
    $this->assertEquals($this->api_client->method, 'setObject');
    $this->assertEquals($dcx_id, 'document/docABC');
  }
}
