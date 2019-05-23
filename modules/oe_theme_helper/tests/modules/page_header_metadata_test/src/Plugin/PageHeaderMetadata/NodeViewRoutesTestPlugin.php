<?php

declare(strict_types = 1);

namespace Drupal\page_header_metadata_test\Plugin\PageHeaderMetadata;

use Drupal\oe_theme_helper\Plugin\PageHeaderMetadata\NodeViewRoutesBase;

/**
 * Test implementation of a metadata plugin for nodes of bundle "test".
 *
 * @PageHeaderMetadata(
 *   id = "node_view_routes_test_plugin",
 *   label = @Translation("Node view routes metadata test plugin")
 * )
 */
class NodeViewRoutesTestPlugin extends NodeViewRoutesBase {

  /**
   * {@inheritdoc}
   */
  public function applies(): bool {
    $node = $this->getNode();

    return $node && $node->getType() === 'test';
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(): array {
    $metadata = parent::getMetadata();

    $metadata['introduction'] = $this->getNode()->get('body')->value;

    return $metadata;
  }

}
