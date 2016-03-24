<?php

/**
 * @file
 * Contains \Drupal\dcx_integration\ClientInterface.
 */

namespace Drupal\dcx_integration;

/**
 * Interface ClientInterface.
 *
 * @package Drupal\dcx_integration
 */
interface ClientInterface {

  public function getObject($id);

  public function trackUsage($usage, $entity);
}
