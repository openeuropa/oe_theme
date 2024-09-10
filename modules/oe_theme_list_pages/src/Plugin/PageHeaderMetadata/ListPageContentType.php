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

}
