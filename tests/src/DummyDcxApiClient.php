<?php

namespace Drupal\Tests\dcx_integration;

/**
 * Dummy API client class which allows introspection of called methods and
 * arguments for testing.
 */
class DummyDcxApiClient /* implement DcxApiClientInterface */{

  /**
   *
   * @var string name of the last method call on this object
   */
  public $method;
  /**
   *
   * @var mixed arguments of the last call
   */
  public $args;

  /**
   * Magic method which is triggered when invoking inaccessible methods in an
   * object context.
   */
  public function __call($method, $args) {
    if (isset($this->$method)) {
      $this->method = $method;
      $this->args = $args;

      $func = $this->$method;
      return call_user_func_array($func, $args);
    }
  }

  public function createObject($url, array $params, array $data, &$response_body) {
    $this->method = __FUNCTION__; // __METHOD__ would return namespaced value
    $this->args = func_get_args();

    // Provides means to manipulate $response_body
    $response_body = isset($this->expected_response_body)?$this->expected_response_body:$response_body;
  }
}
