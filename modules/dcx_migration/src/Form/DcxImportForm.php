<?php

/**
 * @file
 * Contains \Drupal\dcx_migration\Form\DcxImportForm.
 */

namespace Drupal\dcx_migration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\dcx_migration\DcxImportServiceInterface;
use Drupal\dcx_migration\Exception\AlreadyMigratedException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DcxImportForm.
 *
 * @package Drupal\dcx_migration\Form
 */
class DcxImportForm extends FormBase {

  protected $importService;

  /**
   * Constructor.
   *
   * @param \Drupal\dcx_migration\DcxImportServiceInterface $importService
   *   The DCX Import Service actually processing the input.
   */
  public function __construct(DcxImportServiceInterface $importService) {
    $this->importService = $importService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('dcx_migration.import'));
  }


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
    $form['dropzone'] = [
      '#title' => t('DC-X Dropzone element'),
      '#dropzone_description' => 'Custom description goes here.',
      '#type' => 'dcxdropzone',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $data = $form_state->getValue('dropzone');

    $ids = [];
    // Data might be a simple string, which is technically not JSON ... so
    // we need to check
    $json = json_decode($data);

    if ($json === NULL) { // decoding failed -> single item URL as string
      preg_match('|dcx/(document/doc.*)\?|', $data, $matches);
      if (!empty($matches)) {
        $ids[] = "dcxapi:" .  $matches[1];
      }
    }
    else { // decoding was successfull -> data is JSON -> data is multiple ids
      $data = $json;
      foreach($data as $val) {
        $ids[] = "dcxapi:" .  current($val);
      }
    }

    if (empty($ids)) {
      return;
    }

    $this->importService->import($ids);
  }

}
