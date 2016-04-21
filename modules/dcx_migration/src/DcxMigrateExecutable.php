<?php

/**
 * @file
 * Contains \Drupal\dcx_migration\DcxMigrateExecutable.
 */

namespace Drupal\dcx_migration;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\dcx_migration\Exception\AlreadyMigratedException;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigrateImportEvent;
use Drupal\migrate\Event\MigratePostRowSaveEvent;
use Drupal\migrate\Event\MigratePreRowSaveEvent;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessageInterface;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate\Plugin\MigrateIdMapInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Custom MigrationExecutable which is able to take an idlist just like the
 * drush migate-import command. Thus heavily inspired by
 * \Drupal\migrate_tools\MigrateExecutable.
 */
class DcxMigrateExecutable extends MigrateExecutable implements MigrateMessageInterface {
  use DependencySerializationTrait;

  /**
   * {@inheritdoc}
   */
  public function __construct(MigrationInterface $migration, EventDispatcherInterface $event_dispatcher) {
    parent::__construct($migration, $this, $event_dispatcher);

    $this->listeners[MigrateEvents::PRE_IMPORT] = [$this, 'onPreImport'];
    $this->listeners[MigrateEvents::POST_IMPORT] = [$this, 'onPostImport'];

    foreach ($this->listeners as $event => $listener) {
      $event_dispatcher->addListener($event, $listener);
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
   * @param MigrateImportEvent $event
   *   The map event.
   */
  public function onPreImport(MigrateImportEvent $event) {
    $migration = $event->getMigration();

  }

  /**
   * React to migration completion.
   *
   * @param MigrateImportEvent $event
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
      $this->getEventDispatcher()->removeListener($event, $listener);
    }
  }

  public function importItemWithUnknownStatus($id) {
    $id_map = $this->migration->getIdMap();

    // @TODO This "knows" that the source id key is a single value called id.
    // Should be dynamic.
    $row_of_previous_migration = $id_map->getRowBySource(['id' => $id]);

    // @TODO What about previously migrated but meanwhile deleted items?
    if (!empty($row_of_previous_migration)) {
      throw new AlreadyMigratedException($id, $row_of_previous_migration['destid1']);
    }

    $this->getEventDispatcher()->dispatch(MigrateEvents::PRE_IMPORT, new MigrateImportEvent($this->migration));

    $source = $this->getSource();
    $row = $source->getRowById($id);
    $this->sourceIdValues = $row->getSourceIdValues();

    try {
      $this->processRow($row);
      $save = TRUE;
    }
    catch (MigrateException $e) {
      $this->migration->getIdMap()->saveIdMapping($row, array(), $e->getStatus());
      $this->saveMessage($e->getMessage(), $e->getLevel());
      $save = FALSE;
    }
    catch (MigrateSkipRowException $e) {
      $id_map->saveIdMapping($row, array(), MigrateIdMapInterface::STATUS_IGNORED);
      $save = FALSE;
    }

    if ($save) {
      try {
        $this->getEventDispatcher()->dispatch(MigrateEvents::PRE_ROW_SAVE, new MigratePreRowSaveEvent($this->migration, $row));
        $destination = $this->migration->getDestinationPlugin();
        $destination_id_values = $destination->import($row, $id_map->lookupDestinationId($this->sourceIdValues));
        $this->getEventDispatcher()->dispatch(MigrateEvents::POST_ROW_SAVE, new MigratePostRowSaveEvent($this->migration, $row, $destination_id_values));
        if ($destination_id_values) {
          // We do not save an idMap entry for config.
          if ($destination_id_values !== TRUE) {
            $id_map->saveIdMapping($row, $destination_id_values, $this->sourceRowStatus, $destination->rollbackAction());
          }
        }
        else {
          $id_map->saveIdMapping($row, array(), MigrateIdMapInterface::STATUS_FAILED);
          if (!$id_map->messageCount()) {
            $message = $this->t('New object was not saved, no error provided');
            $this->saveMessage($message);
            $this->message->display($message);
          }
        }
      }
      catch (MigrateException $e) {
        $this->migration->getIdMap()->saveIdMapping($row, array(), $e->getStatus());
        $this->saveMessage($e->getMessage(), $e->getLevel());
      }
      catch (\Exception $e) {
        $this->migration->getIdMap()->saveIdMapping($row, array(), MigrateIdMapInterface::STATUS_FAILED);
        $this->handleException($e);
      }
    }
    if ($high_water_property = $this->migration->get('highWaterProperty')) {
      $this->migration->saveHighWater($row->getSourceProperty($high_water_property['name']));
    }

    // Reset row properties.
    unset($sourceValues, $destinationValues);
    $this->sourceRowStatus = MigrateIdMapInterface::STATUS_IMPORTED;


    $this->getEventDispatcher()->dispatch(MigrateEvents::POST_IMPORT, new MigrateImportEvent($this->migration));

  }
}
