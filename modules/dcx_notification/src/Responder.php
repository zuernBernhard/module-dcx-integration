<?php

namespace Drupal\dcx_notification;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Drupal\dcx_migration\DcxImportServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Responder extends ControllerBase {

  /**
   * The DC-X Client.
   *
   * @var ClientInterface
   */
  public $importService;

  /**
   * Database connection.
   *
   * @var Connection
   */
  public $db_connection;


  /**
   * The Constructor.
   *
   * @param ClientInterface $dcx_integration_client
   */
  public function __construct(DcxImportServiceInterface $importService, Connection $connection) {
    $this->importService = $importService;
    $this->db_connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dcx_migration.import'),
      Database::getConnection()
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
    if (!isset($_GET['id'])) {
      throw new NotAcceptableHttpException($this->t('Parameter id is missing.'));
    }

    $id = $_GET['id'];

    /*
    if (isset($_GET['variant'])) {
      $variant = $_GET['variant'];
    }
    else {
      $variant = 'Original';
    }
    */

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
    $this->importService->import($result);

    return new Response(NULL, 204);
  }
}

