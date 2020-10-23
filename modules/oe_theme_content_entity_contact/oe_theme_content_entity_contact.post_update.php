<?php

/**
 * @file
 * OpenEuropa theme content entity contact post updates.
 */

declare(strict_types = 1);

use Drupal\Component\Utility\Crypt;
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
    $display_values = $storage->read($display);
    $view_display = EntityViewDisplay::load($display_values['id']);
    if ($view_display) {
      $updated_display = \Drupal::entityTypeManager()
        ->getStorage($view_display->getEntityTypeId())
        ->updateFromStorageRecord($view_display, $display_values);
      $updated_display->save();
    }
  }
}

/**
 * Create the oe_contact 'full' view mode.
 */
function oe_theme_content_entity_contact_post_update_00002(): void {
  $storage = new FileStorage(drupal_get_path('module', 'oe_theme_content_entity_contact') . '/config/post_updates/00002_create_full_view_display');

  $config_manager = \Drupal::service('config.manager');
  $entity_type_manager = \Drupal::entityTypeManager();

  // View display configurations to update.
  $names = [
    'core.entity_view_mode.oe_contact.full',
    'core.entity_view_display.oe_contact.oe_general.full',
    'core.entity_view_display.oe_contact.oe_press.full',
  ];
  foreach ($names as $name) {
    $config = $storage->read($name);
    // We are creating the config which means that we are also shipping
    // it in the config/install folder so we want to make sure it gets the hash
    // so Drupal treats it as a shipped config. This means that it gets exposed
    // to be translated via the locale system as well.
    $config['_core']['default_config_hash'] = Crypt::hashBase64(serialize($config));
    $entity_type = $config_manager->getEntityTypeIdByName($name);
    /** @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface $entity_storage */
    $entity_storage = $entity_type_manager->getStorage($entity_type);
    $entity = $entity_storage->createFromStorageRecord($config);
    $entity->save();
  }
}
