<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_tender\Plugin\PageHeaderMetadata;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\oe_theme_helper\Plugin\PageHeaderMetadata\NodeViewRoutesBase;
use Drupal\oe_content_tender\TenderNodeWrapper;

/**
 * Page header metadata for the OpenEuropa Call for tender content type.
 *
 * @PageHeaderMetadata(
 *   id = "oe_tender_content_type",
 *   label = @Translation("Metadata extractor for the OE Call for Tender content type"),
 *   weight = -1
 * )
 */
class TenderContentType extends NodeViewRoutesBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function applies(): bool {
    $node = $this->getNode();

    return $node && $node->bundle() === 'oe_tender';
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(): array {
    $metadata = parent::getMetadata();
    $node = $this->getNode();
    if (!($node->get('oe_summary')->isEmpty())) {
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
    }
    $node = TenderNodeWrapper::getInstance($node);
    $status = $node->getStatusLabel();
    $metadata['metas'] = [
      $this->t('Call for tenders'),
      $status,
    ];
    return $metadata;
  }

}
