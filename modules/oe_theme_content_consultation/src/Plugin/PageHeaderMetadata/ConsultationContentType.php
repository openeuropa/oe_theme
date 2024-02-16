<?php

declare(strict_types=1);

namespace Drupal\oe_theme_content_consultation\Plugin\PageHeaderMetadata;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\oe_content_consultation\ConsultationNodeWrapper;
use Drupal\oe_theme_helper\Plugin\PageHeaderMetadata\NodeViewRoutesBase;

/**
 * Page header metadata for the OpenEuropa "Consultation" content type.
 *
 * @PageHeaderMetadata(
 *   id = "oe_consultation_content_type",
 *   label = @Translation("Metadata extractor for the OE Consultation content type"),
 *   weight = -1
 * )
 */
class ConsultationContentType extends NodeViewRoutesBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function applies(): bool {
    $node = $this->getNode();

    return $node && $node->bundle() === 'oe_consultation';
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(): array {
    $metadata = parent::getMetadata();

    $node = $this->getNode();
    $node_wrapper = ConsultationNodeWrapper::getInstance($node);
    $metadata['metas'] = [$this->t('Consultation')];
    if ($node_wrapper->hasStatus()) {
      $metadata['metas'][] = $node_wrapper->getStatusLabel();
    }

    return $metadata;
  }

}
