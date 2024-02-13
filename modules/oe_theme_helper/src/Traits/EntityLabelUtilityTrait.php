<?php

declare(strict_types=1);

namespace Drupal\oe_theme_helper\Traits;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;

/**
 * Utility trait for entity labels.
 *
 * @package Drupal\oe_theme_helper\Traits
 */
trait EntityLabelUtilityTrait {

  /**
   * Format a list of entity references into a comma separated string.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   * @param \Drupal\Core\Field\EntityReferenceFieldItemListInterface $items
   *   Field item list object.
   *
   * @return string
   *   Comma separated string.
   */
  protected function getCommaSeparatedReferencedEntityLabels(EntityRepositoryInterface $entity_repository, EntityReferenceFieldItemListInterface $items): string {
    $list = [];
    $entities = $items->referencedEntities();
    foreach ($entities as $entity) {
      $list[] = $entity_repository->getTranslationFromContext($entity)->label();
    }
    return implode(', ', $list);
  }

}
