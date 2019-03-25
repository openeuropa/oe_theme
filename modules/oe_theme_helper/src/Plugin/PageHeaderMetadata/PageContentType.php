<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\PageHeaderMetadata;

/**
 * Defines a page header metadata plugin that extracts data from current entity.
 *
 * @PageHeaderMetadata(
 *   id = "page_content_type",
 *   label = @Translation("Metadata extractor for the oe_content Page content type"),
 *   weight = -1
 * )
 */
class PageContentType extends EntityCanonicalRoutePage {

  /**
   * {@inheritdoc}
   */
  public function applies(): bool {
    $entity = $this->getEntityFromCurrentRoute();
    return $entity->bundle() == 'oe_page';
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(): array {
    $metadata = parent::getMetadata();
    $entity = $this->getEntityFromCurrentRoute();
    if ($entity->get('oe_page_summary')->value) {
      $metadata['introduction'] = [
        '#plain_text' => $entity->get('oe_page_summary')->value,
      ];
    }
    return $metadata;
  }

}
