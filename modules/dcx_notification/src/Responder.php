<?php

namespace Drupal\dcx_notification;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\dcx_migration\DcxImportServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Responder extends ControllerBase {

  /**
   * The DC-X Client.
   *
   * @var \Drupal\dcx_migration\DcxImportServiceInterface $importService
   */
  public $importService;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection $connection
   */
  public $db_connection;

  /**
   *
   * @var \Symfony\Component\HttpFoundation\Request $request
   */
  public $request;

  /**
   *
   * The Constructor.
   *
   * @param \Drupal\dcx_migration\DcxImportServiceInterface $importService
   * @param \Drupal\Core\Database\Connection $connection
   * @param \Symfony\Component\HttpFoundation\Request $request
   */
  public function __construct(DcxImportServiceInterface $importService, Connection $connection, Request $request) {
    $this->importService = $importService;
    $this->db_connection = $connection;
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dcx_migration.import'),
      $container->get('database'),
      $container->get('request_stack')->getCurrentRequest()
    );
  }

  /**
   * Evaluates the GET parameters id and imports the respective
   * media:image entity.
   *
   * @return string a JSON string representing an empty object.
   * @throws NotAcceptableHttpException
   * @throws NotFoundHttpException
   */
  public function trigger() {
    $id = $this->request->query->get('id', NULL);

    if (NULL == $id) {
      throw new NotAcceptableHttpException($this->t('Parameter id is missing.'));
    }

    $query = $this->db_connection->select('migrate_map_dcx_migration', 'm')
      ->fields('m', ['destid1'])
      ->condition('sourceid1', $id);
    $result = $query->execute()->fetchAllKeyed(0, 0);

    if (0 == count($result)) {
      throw new NotFoundHttpException();
    }

    if (1 < count($result)) {
      throw new NotAcceptableHttpException($this->t('Parameters point to more than one entity.'));
    }

    // @TODO ->import() is handling Exceptions. How are we going to handle an
    // error here?
    $this->importService->import([$id]);

    return new Response(NULL, 204);
  }
}

