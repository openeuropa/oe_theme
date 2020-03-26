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
      $provider->getPossibleOptions()[$node->get('oe_event_type')->target_id],
    ];

    if ($node->get('oe_summary')->isEmpty()) {
      return $metadata;
    }

    $summary = $node->get('oe_summary')->first();
    $metadata['introduction'] = [
      // We strip the tags because the component expects only one paragraph of
      // text and the field is using a text format which adds paragraph tags.
      '#type' => 'inline_template',
      '#template' => '{{ summary|render|striptags("<strong><a><em>")|raw }}',
      '#context' => [
        'summary' => [
          '#type' => 'processed_text',
          '#text' => $summary->value,
          '#format' => $summary->format,
          '#langcode' => $summary->getLangcode(),
        ],
      ],
    ];

    return $metadata;
  }

}
