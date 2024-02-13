<?php

declare(strict_types=1);

namespace Drupal\oe_theme_helper\Event;

use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Drupal\node\NodeInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event used for retrieving the node entity for the page header metadata.
 */
class NodeMetadataEvent extends Event {

  use RefinableCacheableDependencyTrait;

  /**
   * The node entity.
   *
   * @var \Drupal\node\NodeInterface|null
   */
  protected $node = NULL;

  /**
   * Returns the node entity.
   *
   * @return \Drupal\node\NodeInterface|null
   *   The node entity if applicable, null otherwise.
   */
  public function getNode(): ?NodeInterface {
    return $this->node;
  }

  /**
   * Sets the node entity.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node entity.
   */
  public function setNode(NodeInterface $node): void {
    $this->node = $node;
  }

}
