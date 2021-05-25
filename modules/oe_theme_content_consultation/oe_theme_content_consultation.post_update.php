<?php

/**
 * @file
 * OpenEuropa theme content consultation post updates.
 */

declare(strict_types = 1);

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Entity\Entity\EntityViewDisplay;

/**
 * Updates the teaser view display.
 */
function oe_theme_content_consultation_post_update_00001(): void {
  $storage = new FileStorage(drupal_get_path('module', 'oe_theme_content_consultation') . '/config/post_updates/00001_update_teaser_view_display');

  $display_values = $storage->read('core.entity_view_display.node.oe_consultation.teaser');
  $storage = \Drupal::entityTypeManager()->getStorage('entity_view_display');

  $view_display = EntityViewDisplay::load($display_values['id']);
  if ($view_display) {
    $display = $storage->updateFromStorageRecord($view_display, $display_values);
    $display->save();
    return;
  }

  $display = $storage->createFromStorageRecord($display_values);
  $display->save();
}
