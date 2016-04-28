<?php

namespace Drupal\dcx_migration;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\dcx_migration\DcxMigrateExecutable;
use Drupal\migrate\Plugin\MigrationPluginManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DcxImportService implements DcxImportServiceInterface {
  use StringTranslationTrait;

  protected $migration_executable;

  protected $event_dispatcher;

  public function __construct(TranslationInterface $string_translation, MigrationPluginManagerInterface $plugin_manager , EventDispatcherInterface $event_dispatcher) {
    $this->stringTranslation = $string_translation;
    $this->plugin_manager = $plugin_manager;
    $this->event_dispatcher = $event_dispatcher;
  }

  protected function getMigrationExecutable() {
    $migration = $this->plugin_manager->createInstance('dcx_migration');

    $this->migration_executable = new DcxMigrateExecutable($migration, $this->event_dispatcher);

    return $this->migration_executable;
  }

  /**
   * {@inheritdoc}
   */
  public function import($ids) {
    $executable = $this->getMigrationExecutable();

    if (1 == count($ids)) {
      try {
        $row = $executable->importItemWithUnknownStatus(current($ids));
      }
      catch (\Exception $e) {
        $executable->display($e->getMessage());
      }
    }
    else {
      foreach($ids as $id) {
        $operations[] = [[__CLASS__, 'batchImport'], [$id, $executable]];
      }
      $batch = array(
        'title' => t('Import media from DC-X'),
        'operations' => $operations,
        'finished' => [__CLASS__, 'batchFinished'],
      );

      batch_set($batch);
    }
  }

  public static function batchImport($id, $executable) {
    try {
      $row = $executable->importItemWithUnknownStatus($id);
    }
    catch (\Exception $e) {
      $executable->display($e->getMessage());
    }
  }

  public static function batchFinished($success, $results, $operations, $elapsed) {
  }

}
