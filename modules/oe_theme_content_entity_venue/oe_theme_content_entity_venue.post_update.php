<?php

/**
 * @file
 * OpenEuropa theme content entity venue post updates.
 */

declare(strict_types = 1);

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Config\FileStorage;

/**
 * Create the oe_venue 'full' view mode.
 */
function oe_theme_content_entity_venue_post_update_00001(): void {
  $storage = new FileStorage(drupal_get_path('module', 'oe_theme_content_entity_venue') . '/config/post_updates/00001_create_full_view_display');

  $config_manager = \Drupal::service('config.manager');
  $entity_type_manager = \Drupal::entityTypeManager();

  // View display configurations to update.
  $names = [
    'core.entity_view_mode.oe_venue.full',
    'core.entity_view_display.oe_venue.oe_default.full',
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
