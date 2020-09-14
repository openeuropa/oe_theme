<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_organisation\Plugin\PageHeaderMetadata;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\oe_theme_helper\Plugin\PageHeaderMetadata\NodeViewRoutesBase;

/**
 * Page header metadata for the OpenEuropa Organisation content entity.
 *
 * @PageHeaderMetadata(
 *   id = "organisation_content_type",
 *   label = @Translation("Metadata extractor for the OE Organisation Content content type"),
 *   weight = -1
 * )
 */
class OrganisationContentType extends NodeViewRoutesBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function applies(): bool {
    $node = $this->getNode();

    return $node && $node->bundle() === 'oe_organisation';
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
    if (!$node->get('oe_organisation_acronym')->isEmpty()) {
      $metadata['metas'] = [
        $node->get('oe_organisation_acronym')->value,
      ];
    }

    return $metadata;
  }

}
