<?php

declare(strict_types=1);

namespace Drupal\oe_theme_list_pages\Plugin\PageHeaderMetadata;

use Drupal\oe_theme_helper\Plugin\PageHeaderMetadata\NodeViewRoutesBase;

/**
 * Page header metadata for the OpenEuropa Page content entity.
 *
 * @PageHeaderMetadata(
 *   id = "list_page_content_type",
 *   label = @Translation("Metadata extractor for the OE List Page content type"),
 *   weight = -1
 * )
 */
class ListPageContentType extends NodeViewRoutesBase {

  /**
   * {@inheritdoc}
   */
  public function applies(): bool {
    $node = $this->getNode();

    return $node && $node->bundle() === 'oe_list_page';
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(): array {
    $metadata = parent::getMetadata();

    $node = $this->getNode();
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
