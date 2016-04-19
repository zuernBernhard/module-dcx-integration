<?php


namespace Drupal\dcx_track_media_usage;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Entity\EntityInterface;

interface ReferencedEntityDiscoveryPluginInterface extends PluginInspectionInterface {

  /**
   * Find referenced entities on the given entity.
   *
   * @param EntityInterface $entity
   *
   * @return array list of referenced entities keyed by entity id
   */
  public function discover(EntityInterface $entity);

}
