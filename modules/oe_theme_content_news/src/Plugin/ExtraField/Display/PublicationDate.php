<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_news\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\oe_theme_helper\Plugin\ExtraField\Display\PublicationDateExtraFieldBase;

/**
 * Displays publication date and last update date fields.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_news_date",
 *   label = @Translation("Publication date"),
 *   bundles = {
 *     "node.oe_news",
 *   },
 *   visible = true
 * )
 */
class PublicationDate extends PublicationDateExtraFieldBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity): array {
    return $this->renderPublicationDateExtraField($entity, 'oe_theme_news_date');
  }

}
