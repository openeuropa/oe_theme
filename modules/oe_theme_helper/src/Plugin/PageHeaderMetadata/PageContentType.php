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
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $this->getEntityFromCurrentRoute();

    return $entity !== NULL ? $entity->bundle() == 'oe_page' : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(): array {
    $metadata = parent::getMetadata();

    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $this->getEntityFromCurrentRoute();

    if ($entity !== NULL && $entity->get('oe_page_summary')->value) {
      $metadata['introduction'] = [
        '#plain_text' => $entity->get('oe_page_summary')->value,
      ];
    }

    return $metadata;
  }

}
