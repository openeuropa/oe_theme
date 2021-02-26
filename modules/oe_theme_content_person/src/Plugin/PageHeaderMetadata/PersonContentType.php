<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_person\Plugin\PageHeaderMetadata;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\oe_content_person\PersonNodeWrapper;
use Drupal\oe_theme_helper\Plugin\PageHeaderMetadata\NodeViewRoutesBase;

/**
 * Page header metadata for the OpenEuropa "Person" content type.
 *
 * @PageHeaderMetadata(
 *   id = "oe_person_content_type",
 *   label = @Translation("Metadata extractor for the OE Person content type"),
 *   weight = -1
 * )
 */
class PersonContentType extends NodeViewRoutesBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function applies(): bool {
    $node = $this->getNode();

    return $node && $node->bundle() === 'oe_person';
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(): array {
    $metadata = parent::getMetadata();

    $node_wrapper = PersonNodeWrapper::getInstance($this->getNode());
    $metadata['metas'] = $node_wrapper->getPersonJobLabels();

    return $metadata;
  }

}
