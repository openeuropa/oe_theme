<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\Traits;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Reconciles the node "body" field type with its storage in tests.
 *
 * Since Drupal 11.4 the "testing" profile overrides the node body storage
 * with text_long, while oe_content body fields declare text_with_summary,
 * breaking node saves in tests.
 *
 * @see https://www.drupal.org/node/3477043
 */
trait NodeBodyFieldTypeReconciliationTrait {

  /**
   * Aligns node "body" field instances with their storage type.
   */
  protected function reconcileNodeBodyFieldType(): void {
    $storage = FieldStorageConfig::loadByName('node', 'body');
    if (!$storage instanceof FieldStorageConfig) {
      return;
    }
    $storage_type = $storage->getType();
    $ids = \Drupal::entityQuery('field_config')
      ->condition('entity_type', 'node')
      ->condition('field_name', 'body')
      ->accessCheck(FALSE)
      ->execute();
    foreach (FieldConfig::loadMultiple($ids) as $field) {
      if ($field->getType() !== $storage_type) {
        $field->set('field_type', $storage_type)->save();
      }
    }
  }

}
