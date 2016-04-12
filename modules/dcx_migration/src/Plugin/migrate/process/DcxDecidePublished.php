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
    $kill_date = $row->getSourceProperty('kill_date');
    if (empty($kill_date)) { // if no kill date is set, there's no need to kill.
      return TRUE;
    }
    $time = time();
    $kill_date = \DateTime::createFromFormat('Y-m-d', $kill_date)->getTimestamp();

    return (int)($time < $kill_date);
  }

}
