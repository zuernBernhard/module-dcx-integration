<?php

/**
 * @file
 * Contains \Drupal\dcx_migration\Plugin\migrate\process\DcxDecidePublished.
 */

namespace Drupal\dcx_migration\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Plugin to decide wheter an imported media item should be published or not.
 *
 * @MigrateProcessPlugin(
 *   id = "dcx_decide_published"
 * )
 */
class DcxDecidePublished extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $time = time();
    $kill_date = \DateTime::createFromFormat('Y-m-d', $row->getSourceProperty('kill_date'))->getTimestamp();

    return (int)($time < $kill_date);
  }

}
