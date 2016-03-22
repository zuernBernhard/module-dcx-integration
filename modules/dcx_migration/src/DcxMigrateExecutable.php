<?php

/**
 * @file
 * Contains \Drupal\dcx_migration\DcxMigrateExecutable.
 */

namespace Drupal\dcx_migration;


use Drupal\dcx_integration\ClientInterface;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\MigrateMessageInterface;
use Drupal\migrate\Event\MigrateImportEvent;

/**
 * Custom MigrationExecutable which is able to take an idlist just like the
 * drush migate-import command. Thus heavily inspired by
 * \Drupal\migrate_tools\MigrateExecutable.
 */
class DcxMigrateExecutable extends MigrateExecutable implements MigrateMessageInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(MigrationInterface $migration) {
    parent::__construct($migration, $this);

    if (isset($options['idlist'])) {
      $this->idlist = explode(',', $options['idlist']);
    }

    $this->listeners[MigrateEvents::PRE_IMPORT] = [$this, 'onPreImport'];
    $this->listeners[MigrateEvents::POST_IMPORT] = [$this, 'onPostImport'];

    foreach ($this->listeners as $event => $listener) {
      \Drupal::service('event_dispatcher')->addListener($event, $listener);
    }
  }

  /**
   * Implements \Drupal\migrate\MigrateMessageInterface::display
   *
   * This also act as MigrateMessage providerer for now. 
   */
  public function display($message, $type = 'status') {
    drupal_set_message($message, $type);
  }

  /**
   * React to migration completion.
   *
   * @param \Drupal\migrate\Event\MigrateImportEvent $event
   *   The map event.
   */
  public function onPreImport(MigrateImportEvent $event) {
    $migration = $event->getMigration();

  }

  /**
   * React to migration completion.
   *
   * @param \Drupal\migrate\Event\MigrateImportEvent $event
   *   The map event.
   */
  public function onPostImport(MigrateImportEvent $event) {
    $migrate_last_imported_store = \Drupal::keyValue('migrate_last_imported');
    $migrate_last_imported_store->set($event->getMigration()->id(), round(microtime(TRUE) * 1000));
    $this->removeListeners();
  }

  /**
   * Clean up all our event listeners.
   */
  protected function removeListeners() {
    foreach ($this->listeners as $event => $listener) {
      \Drupal::service('event_dispatcher')->removeListener($event, $listener);
    }
  }

  public function importItemWithUnknownStatus($id) {
    $this->getEventDispatcher()->dispatch(MigrateEvents::PRE_IMPORT, new MigrateImportEvent($this->migration));

    $source = $this->getSource();
    $row = $source->getRowById($id);

    dpm($row, "ROW");



    $this->getEventDispatcher()->dispatch(MigrateEvents::POST_IMPORT, new MigrateImportEvent($this->migration));
  }
}
