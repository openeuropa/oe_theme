<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper;

use Drupal\Core\Field\EntityReferenceFieldItemListInterface;

/**
 * Utility class for content data.
 */
class ContentUtility {

  /**
   * Format a list of entity references into a comma separated string.
   *
   * @param \Drupal\Core\Field\EntityReferenceFieldItemListInterface $items
   *   Field item list object.
   *
   * @return string
   *   Comma separated string.
   */
  public static function getCommaSeparatedReferencedEntityLabels(EntityReferenceFieldItemListInterface $items): string {
    $list = [];
    $entities = $items->referencedEntities();
    foreach ($entities as $entity) {
      $list[] = \Drupal::service('entity.repository')->getTranslationFromContext($entity)->label();
    }
    return implode(', ', $list);
  }

}
