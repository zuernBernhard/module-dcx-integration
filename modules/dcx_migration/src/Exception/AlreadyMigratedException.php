<?php

/**
 * @file
 * Contains \Drupal\dcx_migration\Exception\AlreadyMigratedException
 */

namespace Drupal\dcx_migration\Exception;

/**
 * Exception thrown when the given item was already migrated before.
 */
class AlreadyMigratedException extends \Exception {

  protected $destid;

  /**
   * Constructor.
   *
   * @param array $source_id  source ids as in migration source
   * @param array $destid destination ids as in migration destination
   */
  public function __construct(array $source_id, array $destid) {
    $this->destid = $destid;

    $message = "Source item " . json_encode($source_id) . " was be migrated "
      . "before and should be available as " . json_encode($destid) . ".";
    parent::__construct($message);
  }

  public function getDestId() {
    return $this->destid;
  }
}
