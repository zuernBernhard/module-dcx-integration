<?php

namespace Drupal\dcx_migration;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\dcx_migration\DcxMigrateExecutable;
use Drupal\dcx_migration\Exception\AlreadyMigratedException;

class DcxImportService implements DcxImportServiceInterface {
  use StringTranslationTrait;

  protected $migration_executable;

  public function __construct(TranslationInterface $string_translation, EntityTypeManager $entity_type_manager) {
    $this->stringTranslation = $string_translation;
    $this->entity_type_manager = $entity_type_manager;
  }

  protected function getMigrationExecutable() {
    if (! $this->migration_executable) {
      $migration = $this->entity_type_manager
        ->getStorage('migration')
        ->load('dcx_migration');

      $this->migration_executable = new DcxMigrateExecutable($migration);
    }

    return $this->migration_executable;
  }

  public function import($json) {
    $data = json_decode($json, TRUE);
    if (is_string(current($data))) { // single
      $ids[] = "dcxapi:" . current($data);
    }

    if (is_array(current($data))) { // multiple
      foreach($data as $val) {
        $ids[] = "dcxapi:" .  current($val);
      }
    }

    $executable = $this->getMigrationExecutable();

    if (1 == count($ids)) {
      try {
        $row = $executable->importItemWithUnknownStatus($ids[0]);
      }
      catch (AlreadyMigratedException $ame) {
        drupal_set_message($ame->getMessage(), 'message');
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
    $executable->importItemWithUnknownStatus($id);
  }

  public static function batchFinished($success, $results, $operations, $elapsed) {
    dpm(func_get_args());
  }

}
