<?php

namespace Drupal\dcx_article_archive;

use Drupal\dcx_integration\ClientInterface;
use Drupal\dcx_track_media_usage\ReferencedEntityDiscoveryServiceInterface;

class ArticleArchiver implements ArticleArchiverInterface {

  /**
   * The referenced entity discovery service.
   *
   * @var \Drupal\dcx_track_media_usage\ReferencedEntityDiscoveryServiceInterface
   */
  protected $discovery;

  /**
   * The DC-X Integration Client.
   *
   * @var \Drupal\dcx_integration\ClientInterface
   */
  protected $client;

  public function __construct(ReferencedEntityDiscoveryServiceInterface $discovery, ClientInterface $client) {
    $this->discovery = $discovery;
    $this->client = $client;
  }

  public function archive($entity) {
    $data = [];
    $data['title'] = $entity->title->value;

    // Todo: Should probably use a custom view mode
    $paragraphs = $entity->field_paragraphs->view("default");
    $rendered = \Drupal::service('renderer')->render($paragraphs);
    $data['text'] = strip_tags($rendered);

    // Find attached images
    $used_media = $this->discovery->discover($entity, 'return_entities');

    foreach ($used_media as $dcx_id => $media_entity) {
      $caption = $media_entity->field_description->value;
      $media_entity = $media_entity->field_dcx_id->value;

      $data['media'][$dcx_id] = ['caption' => $caption, 'id' => $dcx_id];
    }

    $url = $entity->toUrl()->setAbsolute()->toString();

    // This is NULL for new article and that's perfectly fine.
    $existing_dcx_id = $entity->field_dcx_id->value;

    try {
      $dcx_id = $this->client->archiveArticle($url, $data, $existing_dcx_id);
    } catch (\Exception $e) {
      \Drupal::logger('dcx_article_archive')->error($e->getMessage());
      drupal_set_message($e->getMessage(), 'error');
      return;
    }

    // If a non-null id has changed while archiving something is severly wrong.
    // Yet another case of "this should never happen".
    if (NULL !== $existing_dcx_id && $existing_dcx_id != $dcx_id) {
      $message = t('Node %url changed its DC-X ID from %from to %to while archiving to DC-X.',[
        '%url' => $url,
        '%from' => $existing_dcx_id,
        '%to' => $dcx_id,
      ]);
      \Drupal::logger('dcx_article_archive')->error($message);
      drupal_set_message($message, 'error');
      return;
    }

    // If the DC-X ID has changed, we need to save the id to the entity.
    if ($existing_dcx_id !== $dcx_id) {
      $entity->set('field_dcx_id', $dcx_id, FALSE);
      $entity->save();
    }
  }
}
