<?php
/**
 * @file
 * Contains \Drupal\dcx_migration\Plugin\migrate\source\DcxImage
 */

namespace Drupal\dcx_migration\Plugin\migrate\source;

use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\MigrateException;
use Drupal\dcx_migration\Plugin\migrate\source\DcxSourcePluginBase;

/**
 * Source for DcxImage.
 *
 * @deprecated since right meow
 *
 *  * @MigrateSource(
 *   id = "dcx_image"
 * )
 */
class DcxImage extends DcxSourcePluginBase {

  /**
   * The migration.
   *
   * @var \Drupal\migrate\Entity\MigrationInterface
   */
  protected $migration;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
    $this->migration = $migration;

    $configuration += ['source_asset_class' => 'Drupal\dcx_integration\Asse\Image'];

    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
  }
}
