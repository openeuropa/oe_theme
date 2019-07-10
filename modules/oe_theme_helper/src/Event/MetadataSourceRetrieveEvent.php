<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Event;

use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Drupal\node\NodeInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Metadata source retriever class for events related to metadata.
 */
class MetadataSourceRetrieveEvent extends Event implements MetadataSourceRetrieveEventInterface {

  use RefinableCacheableDependencyTrait;

  /**
   * The node object for passing to metadata building.
   *
   * @var \Drupal\node\NodeInterface|null
   */
  public $node = NULL;

  /**
   * {@inheritdoc}
   */
  public function getNode(): ?NodeInterface {
    return $this->node;
  }

  /**
   * {@inheritdoc}
   */
  public function setNodeMetadataSource(NodeInterface $node): void {
    $this->addCacheableDependency($node);
    $this->node = $node;
  }

}
