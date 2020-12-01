<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_call_proposals\Plugin\PageHeaderMetadata;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\oe_theme_helper\Plugin\PageHeaderMetadata\NodeViewRoutesBase;
use Drupal\oe_content_call_proposals\CallForProposalsNodeWrapper;

/**
 * Page header metadata for the OpenEuropa "Call for proposals" content type.
 *
 * @PageHeaderMetadata(
 *   id = "oe_call_proposals_content_type",
 *   label = @Translation("Metadata extractor for the OE Call for proposals content type"),
 *   weight = -1
 * )
 */
class CallForProposalsContentType extends NodeViewRoutesBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function applies(): bool {
    $node = $this->getNode();

    return $node && $node->bundle() === 'oe_call_proposals';
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(): array {
    $metadata = parent::getMetadata();

    $node = $this->getNode();
    $node_wrapper = CallForProposalsNodeWrapper::getInstance($node);
    $metadata['metas'] = [$this->t('Call for proposals')];
    if ($node_wrapper->hasStatus()) {
      $metadata['metas'][] = $node_wrapper->getStatusLabel();
    }

    return $metadata;
  }

}
