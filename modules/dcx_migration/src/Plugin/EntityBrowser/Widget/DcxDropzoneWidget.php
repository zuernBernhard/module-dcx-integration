<?php

/**
 * Contains \Drupal\dcx_migration\Plugin\EntityBrowser\Widget\DcxDropzoneWidget.
 */

namespace Drupal\dcx_migration\Plugin\EntityBrowser\Widget;

use Drupal\Component\Utility\Bytes;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\entity_browser\WidgetBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Provides an Entity Browser widget that imports files from dcx.
 *
 * @EntityBrowserWidget(
 *   id = "dcx_dropzone",
 *   label = @Translation("DCX Dropzone"),
 *   description = @Translation("Adds DCX Dropzone import integration.")
 * )
 */
class DcxDropzoneWidget extends WidgetBase {


  /**
   * Current user service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs widget plugin.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   Event dispatcher service.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EventDispatcherInterface $event_dispatcher, EntityManagerInterface $entity_manager, AccountProxyInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $event_dispatcher, $entity_manager);
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('event_dispatcher'),
      $container->get('entity.manager'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'dropzone_description' => t('Drop files here to upload them'),
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$original_form, FormStateInterface $form_state, array $aditional_widget_parameters) {
    $config = $this->getConfiguration();

    $form['dropzone'] = [
      '#title' => '',
      '#dropzone_description' => $config['settings']['dropzone_description'],
      '#type' => 'dcxdropzone',
    ];

    return $form;
  }
}
