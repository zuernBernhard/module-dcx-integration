<?php

namespace Drupal\Tests\dcx_integration;

interface DcxApiClientInterface {

  public function getObject($url, array $params, &$data);

  public function createObject($url, array $params, array $data, &$response_body);

  public function setObject($url, array $params, array $data, &$response_body);

  public function deleteObject($url, array $params, &$response_body);

}
