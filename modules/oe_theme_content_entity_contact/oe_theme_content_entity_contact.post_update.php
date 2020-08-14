<?php

/**
 * @file
 * OpenEuropa theme content entity contact post updates.
 */

declare(strict_types = 1);

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Config\FileStorage;

/**
 * Create "oe_details" Contact entity view displays.
 */
function oe_theme_content_entity_contact_post_update_00001(): void {
  // Create a file storage instance for reading configurations.
  $storage = new FileStorage(drupal_get_path('module', 'oe_theme_content_entity_contact') . '/config/post_updates/00001_create_view_display');

  // Create new configurations.
  \Drupal::service('config.installer')->installOptionalConfig($storage);
}

/**
 * Update "default" Contact entity view display.
 */
function oe_theme_content_entity_contact_post_update_00002(): void {
  $storage = new FileStorage(drupal_get_path('module', 'oe_theme_content_entity_contact') . '/config/post_updates/00002_update_view_display');

  // View display configurations to update.
  $displays = [
    'core.entity_view_display.oe_contact.oe_general.default',
    'core.entity_view_display.oe_contact.oe_press.default',
  ];
  foreach ($displays as $display) {
    $values = $storage->read($display);
    $config = EntityViewDisplay::load($values['id']);
    if ($config) {
      foreach ($values as $key => $value) {
        $config->set($key, $value);
      }
      $config->save();
    }
  }
}
