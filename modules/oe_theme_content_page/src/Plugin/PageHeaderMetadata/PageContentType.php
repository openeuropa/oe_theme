<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_page\Plugin\PageHeaderMetadata;

use Drupal\node\NodeInterface;
use Drupal\oe_theme_helper\Plugin\PageHeaderMetadata\EntityCanonicalRoutePage;

/**
 * Page header metadata for the OpenEuropa Page content entity.
 *
 * @PageHeaderMetadata(
 *   id = "page_content_type",
 *   label = @Translation("Metadata extractor for the OE Content Page content type"),
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

    return $entity instanceof NodeInterface && $entity->bundle() === 'oe_page';
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(): array {
    $metadata = parent::getMetadata();

    $metadata['identity'] = '';

    $entity = $this->getEntityFromCurrentRoute();
    if ($entity->get('oe_page_summary')->isEmpty()) {
      return $metadata;
    }

    $metadata['introduction'] = [
      // We strip the tags because the component expects only one paragraph of
      // text and the field is using a text format which adds paragraph tags.
      '#markup' => strip_tags($entity->get('oe_page_summary')->value, '<strong><a><em>'),
    ];

    return $metadata;
  }

}
