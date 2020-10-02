<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_policy\Plugin\PageHeaderMetadata;

use Drupal\node\NodeInterface;
use Drupal\oe_theme_helper\Plugin\PageHeaderMetadata\NodeViewRoutesBase;

/**
 * Page header metadata for the OpenEuropa Policy content entity.
 *
 * @PageHeaderMetadata(
 *   id = "policy_content_type",
 *   label = @Translation("Metadata extractor for the OE Content Policy content type"),
 *   weight = -1
 * )
 */
class PolicyContentType extends NodeViewRoutesBase {

  /**
   * {@inheritdoc}
   */
  public function applies(): bool {
    $entity = $this->getNode();

    return $entity instanceof NodeInterface && $entity->bundle() === 'oe_policy';
  }

}
