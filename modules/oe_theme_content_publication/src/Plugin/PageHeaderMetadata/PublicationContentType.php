<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_publication\Plugin\PageHeaderMetadata;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\oe_theme_helper\ContentUtility;
use Drupal\oe_theme_helper\Plugin\PageHeaderMetadata\NodeViewRoutesBase;

/**
 * Page header metadata for the OpenEuropa Publication content entity.
 *
 * @PageHeaderMetadata(
 *   id = "publication_content_type",
 *   label = @Translation("Metadata extractor for the OE Content Publication content type"),
 *   weight = -1
 * )
 */
class PublicationContentType extends NodeViewRoutesBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function applies(): bool {
    $node = $this->getNode();

    return $node && $node->bundle() === 'oe_publication';
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(): array {
    $node = $this->getNode();

    $metadata = parent::getMetadata();
    $metadata['metas'][] = ContentUtility::getCommaSeparatedReferencedEntityLabels($node->get('oe_publication_type'));

    return $metadata;
  }

}
