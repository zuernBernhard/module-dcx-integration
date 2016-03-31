<?php

namespace Drupal\dcx_migration\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;

/**
 * Provides a Dropzone for DC-X import

 * @FormElement("dcxdropzone")
 */
class DcxDropzone extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);

    return [
      '#input' => TRUE,
      '#process' => [[$class, 'processElement']],
      '#pre_render' => [[$class, 'preRenderElement']],
      '#theme' => 'dcxdropzone',
      '#theme_wrappers' => ['form_element'],
      '#attached' => [
        'library' => ['dcx_migration/dropzone']
      ],
      '#tree' => TRUE,
    ];
  }

  public static function processElement($element, FormStateInterface $form_state, $complete_form) {
    $element['dropvalue'] = [
      '#type' => 'hidden',
      '#default_value' => '',
    ];
    return $element;
  }

  public static function preRenderElement($element) {
    $element['#attached']['drupalSettings']['dcx_dropzone'] = [
      'dropzone_id' => $element['#id'],
      'value_name' => $element['dropvalue']['#name'],
    ];
    return $element;
  }
}
