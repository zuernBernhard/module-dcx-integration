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

  public function import($data) {
    $executable = $this->getMigrationExecutable();
    try {
      $row = $executable->importItemWithUnknownStatus($id);
    }
    catch (AlreadyMigratedException $ame) {
      drupal_set_message($ame->getMessage(), 'message');
    }
    catch (\Exception $e) {
      $executable->display($e->getMessage());
    }
  }

}
