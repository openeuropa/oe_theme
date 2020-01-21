<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\PageHeaderMetadata;

use Drupal\oe_theme_helper\Plugin\PageHeaderMetadata\NodeViewRoutesBase;

/**
 * Page header metadata for the OpenEuropa Event content entity.
 *
 * @PageHeaderMetadata(
 *   id = "event_content_type",
 *   label = @Translation("Metadata extractor for the OE Content Event content type"),
 *   weight = -1
 * )
 */
class EventContentType extends NodeViewRoutesBase {

  /**
   * {@inheritdoc}
   */
  public function applies(): bool {
    $node = $this->getNode();

    return $node && $node->bundle() === 'oe_event';
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(): array {
    $metadata = parent::getMetadata();

    // Use business logic from "list_default" formatter.
    $node = $this->getNode();
    $provider = $node->get('oe_event_type')->getFieldDefinition()
      ->getFieldStorageDefinition()
      ->getOptionsProvider('value', $node);

    $metadata['metas'] = [
      $provider->getPossibleOptions()[$node->get('oe_event_type')->value],
    ];

    return $metadata;
  }

}
