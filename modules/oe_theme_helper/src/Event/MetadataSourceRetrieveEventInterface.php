<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Event;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\node\NodeInterface;

/**
 * Provides an interface for MetadataSourceRetrieveEvent.
 */
interface MetadataSourceRetrieveEventInterface extends RefinableCacheableDependencyInterface {

  /**
   * Get Node object for metadata.
   *
   * @return \Drupal\node\NodeInterface|null
   *   The node entity if applicable, null otherwise.
   */
  public function getNode(): ?NodeInterface;

  /**
   * Set the node entity for metadata source.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node entity for metadata.
   */
  public function setNodeMetadataSource(NodeInterface $node): void;

}
