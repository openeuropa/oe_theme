<?php

/**
 * @file
 * OpenEuropa theme content entity contact post updates.
 */

declare(strict_types = 1);

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Config\FileStorage;

/**
 * Update "Default" and "Details" Contact entity view displays.
 */
function oe_theme_content_entity_contact_post_update_00001(): void {
  $storage = new FileStorage(drupal_get_path('module', 'oe_theme_content_entity_contact') . '/config/post_updates/00001_update_view_display');

  // View display configurations to update.
  $displays = [
    'core.entity_view_display.oe_contact.oe_general.default',
    'core.entity_view_display.oe_contact.oe_general.oe_details',
    'core.entity_view_display.oe_contact.oe_press.default',
    'core.entity_view_display.oe_contact.oe_press.oe_details',
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
