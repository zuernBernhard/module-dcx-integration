<?php

namespace Drupal\Tests\dcx_integration;

class DummyClass {
  public $args;
  public function __call($method, $args) {
    if (isset($this->$method)) {
        $this->args = $args;

        $func = $this->$method;
        return call_user_func_array($func, $args);
    }
  }
}
