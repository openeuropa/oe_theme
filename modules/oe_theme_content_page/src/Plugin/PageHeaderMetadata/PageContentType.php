<?php

declare(strict_types=1);

namespace Drupal\oe_theme_content_page\Plugin\PageHeaderMetadata;

use Drupal\oe_theme_helper\Plugin\PageHeaderMetadata\NodeViewRoutesBase;

/**
 * Page header metadata for the OpenEuropa Page content entity.
 *
 * @PageHeaderMetadata(
 *   id = "page_content_type",
 *   label = @Translation("Metadata extractor for the OE Content Page content type"),
 *   weight = -1
 * )
 */
class PageContentType extends NodeViewRoutesBase {

  /**
   * {@inheritdoc}
   */
  public function applies(): bool {
    $node = $this->getNode();

    return $node && $node->bundle() === 'oe_page';
  }

}
