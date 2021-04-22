<?php

/**
 * @file
 * OpenEuropa theme content person post updates.
 */

declare(strict_types = 1);

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Entity\Entity\EntityViewDisplay;

/**
 * Moves social media links on a separated fieldgroup.
 */
function oe_theme_content_person_post_update_20001(): void {
  $storage = new FileStorage(drupal_get_path('module', 'oe_theme_content_person') . '/config/post_updates/20001_full_view_display');
  $entity_type_manager = \Drupal::entityTypeManager();
  $display_values = $storage->read('core.entity_view_display.node.oe_person.full');

  /** @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface $entity_storage */
  $entity_storage = $entity_type_manager->getStorage('entity_view_display');
  $existing_display = EntityViewDisplay::load($display_values['id']);
  if ($existing_display) {
    $updated_display = $entity_storage->updateFromStorageRecord($existing_display, $display_values);
    $updated_display->save();
  }
}
