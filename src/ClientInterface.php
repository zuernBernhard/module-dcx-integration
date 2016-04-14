<?php

/**
 * @file
 * Contains \Drupal\dcx_integration\ClientInterface.
 */

namespace Drupal\dcx_integration;

/**
 * Interface ClientInterface.
 *
 * @package Drupal\dcx_integration
 */
interface ClientInterface {

  public function getObject($id);

  /**
   * Track usage of DC-X Documents on the given URL.
   *
   * @param array $dcx_ids List of DC-X document IDs.
   * @param string $url absolute canonical URL where the documents are used.
   * @param bool $published status of the given URL
   *
   * @throws \Exception if somethings going wrong.
   */
  public function trackUsage($dcx_ids, $url, $published);

  /**
   * Archive an article.
   *
   * @param string $url The URL of the article, e.g. http://drupal.org/node/42.
   * @param string $title The title of the article.
   * @param string $text The body text of the article.
   * @param string $dcx_id
   *   The DC-X document ID of the article. If it's null a new one is created.
   *
   * @return int
   *   The DC-X document ID of the article
   *
   * @throws \Exception if somethings going wrong.
   */
  public function archiveArticle($url, $title, $text, $dcx_id);
}
