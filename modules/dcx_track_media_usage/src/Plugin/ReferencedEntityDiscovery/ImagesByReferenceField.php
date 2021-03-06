<?php


namespace Drupal\dcx_track_media_usage\Plugin\ReferencedEntityDiscovery;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\dcx_track_media_usage\ReferencedEntityDiscoveryPluginInterface;
use Drupal\field\Entity\FieldConfig;

/**
 * Returns list of referenced media:image entities on entity reference fields
 * attached to  the given entity.
 *
 * @TODO Replace hardcoded media:image with argument to make this a bit
 *   more flexible.
 *
 * @ReferencedEntityDiscovery(
 *   id = "images_by_reference_field"
 * )
 */
class ImagesByReferenceField extends PluginBase implements ReferencedEntityDiscoveryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function discover(EntityInterface $entity, PluginManagerInterface $plugin_manager) {
    $discovered = [];

    if (!$entity instanceof FieldableEntityInterface) {
      return $discovered;
    }

    // Iterate over the field definition of the given entitiy.
    foreach ($entity->getFieldDefinitions() as $definition) {
      // Fields have FieldConfig. Let's assume our media is referenced within a
      // field.
      if (!$definition instanceof FieldConfig) {
        continue;
      }

      // Only care about entity reference fields.
      if ('entity_reference' !== $definition->getType()) {
        continue;
      }
      $settings = $definition->getSettings();

      // We can't be sure that a target type is defined. Deal with it.
      $target_type = isset($settings['target_type']) ? $settings['target_type'] : NULL;

      // Only care about field referencing media.
      if ('media' !== $target_type) {
        continue;
      }

      $target_bundles = $settings['handler_settings']['target_bundles'];

      // Only care about refs allowing images.
      if (!in_array('image', $target_bundles)) {
        continue;
      }

      $field = $definition->getName();

      // Don't care about empty reference fields;.
      if (empty($entity->$field->target_id)) {
        continue;
      }

      $referenced_entities = $entity->$field->referencedEntities();
      foreach ($referenced_entities as $referenced_entity) {
        // Do not care about non-images.
        if ('image' !== $referenced_entity->bundle()) {
          continue;
        }

        $discovered[$referenced_entity->id()] = $referenced_entity;
      }
    }

    return $discovered;
  }

}
