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
 *   id = "images_on_paragraphs"
 * )
 */
class ImagesOnParagraphs extends PluginBase implements ReferencedEntityDiscoveryPluginInterface {

  /**
   * {@inheritdoc}
   */

  public function discover(EntityInterface $entity, PluginManagerInterface $plugin_manager) {
    $discovered = [];

    if (! $entity instanceof FieldableEntityInterface) {
      return $discovered;
    }

    // Iterate over the field definition of the given entitiy
    foreach ($entity->getFieldDefinitions() as $definition) {
      // Fields have FieldConfig. Let's assume our media is referenced within a
      // field
      if (! $definition instanceof FieldConfig) { continue; }
      // Only care about entity reference fields
      if ('entity_reference_revisions' !== $definition->getType()) { continue; }
      $settings = $definition->getSettings();

      // We can't be sure that a target type is defined. Deal with it.
      $target_type = isset($settings['target_type'])?$settings['target_type']:NULL;

      // Only care about field referencing media
      if ('paragraph' !== $target_type) { continue; }

      $field = $definition->getName();
dpm($field);
      $referenced_entities = $entity->$field->referencedEntities();

      if (empty($referenced_entities)) { continue; }

      // use the entity_reference_field plugin to search the paragraph entities
      $entity_reference_field_discovery = $plugin_manager->createInstance('entity_reference_field');
      foreach ($referenced_entities as $referenced_entity) {
        $discovered += $entity_reference_field_discovery->discover($referenced_entity);
      }
    }

    return $discovered;
  }
}
