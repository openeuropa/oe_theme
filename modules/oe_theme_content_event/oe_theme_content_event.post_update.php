<?php

/**
 * @file
 * OpenEuropa theme event post updates.
 */

declare(strict_types = 1);

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Config\FileStorage;

/**
 * Override event teaser view display.
 */
function oe_theme_content_event_post_update_00001(): void {
  $storage = new FileStorage(drupal_get_path('module', 'oe_theme_content_event') . '/config/post_updates/00001_override_teaser_view_display');
  $display_values = $storage->read('core.entity_view_display.node.oe_event.teaser');
  $storage = \Drupal::entityTypeManager()->getStorage('entity_view_display');

  // Take over teaser view display, regardless if it already exists or not.
  $view_display = EntityViewDisplay::load($display_values['id']);
  if ($view_display) {
    $display = $storage->updateFromStorageRecord($view_display, $display_values);
    $display->save();
    return;
  }

  $display = $storage->createFromStorageRecord($display_values);
  $display->save();
}

/**
 * Enable Twig field value module.
 */
function oe_theme_content_event_post_update_00002(): void {
  \Drupal::service('module_installer')->install(['twig_field_value']);
  \Drupal::service('kernel')->invalidateContainer();
}
