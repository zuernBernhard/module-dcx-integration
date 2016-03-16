<?php

/**
 * @file
 * Contains \Drupal\dcx_integration\Controller\DcxDebugController.
 */

namespace Drupal\dcx_integration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\dcx_integration\JsonClient;

/**
 * Class DcxDebugController.
 *
 * @package Drupal\dcx_integration\Controller
 */
class DcxDebugController extends ControllerBase {

  /**
   * Drupal\dcx_integration\ClientInterface definition.
   *
   * @var Drupal\dcx_integration\ClientInterface
   */
  protected $dcx_integration_client;
  /**
   * {@inheritdoc}
   */
  public function __construct(JsonClient $dcx_integration_client) {
    $this->dcx_integration_client = $dcx_integration_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dc_integration.client')
    );
  }

  /**
   * Return debug information on the response for the given object identifiers.
   */
  public function debug($type, $id) {
    try {
      $data = $this->dcx_integration_client->getDocument($type . '/' . $id);
    }
    catch(\Exception $e) {
      dpm($e->getMessage(), "Meh :(");
    }

    dpm($data, 'response');

    return [
      '#type' => 'markup',
      '#markup' => $this->t("$type, $id")
    ];

  }

}
