<?php

/**
 * @file
 * Contains \Drupal\dcx_migration\Form\DcxImportForm.
 */

namespace Drupal\dcx_migration\Form;

use Drupal\dcx_migration\DcxMigrateExecutable;
use Drupal\dcx_migration\Exception\AlreadyMigratedException;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
/**
 * Class DcxImportForm.
 *
 * @package Drupal\dcx_migration\Form
 */
class DcxImportForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dcx_import_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    dpm(get_class(\Drupal::service('dcx_integration.client')), "active client");

    $form['dropzone'] = [
      '#title' => t('DC-X Dropzone element'),
      '#type' => 'dcxdropzone',
    ];

    $form['actions']['import'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    dpm($form_state->getValues());
    return;
    $migration = \Drupal::entityTypeManager()
      ->getStorage('migration')
      ->load('dcx_migration');
    $id = $form_state->getValue('dcx_id');

    $executable = new DcxMigrateExecutable($migration);
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
