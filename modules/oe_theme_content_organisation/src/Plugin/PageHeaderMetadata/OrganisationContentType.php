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

    if (!$node->get('oe_organisation_acronym')->isEmpty()) {
      $metadata['metas'] = [
        $node->get('oe_organisation_acronym')->value,
      ];
    }

    return $metadata;
  }

}
