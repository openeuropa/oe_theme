<?php

/**
 * @file
 * OpenEuropa theme content person post updates.
 */

declare(strict_types = 1);

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Component\Utility\Crypt;

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

/**
 * Create the 'oe_compact_teaser' entity view display on the person CT.
 */
function oe_theme_content_person_post_update_20002() {
  $storage = new FileStorage(drupal_get_path('module', 'oe_theme_content_person') . '/config/post_updates/20002_create_oe_compact_teaser_view_display');

  $entity_type_manager = \Drupal::entityTypeManager();
  $config = $storage->read('core.entity_view_display.node.oe_person.oe_compact_teaser');
  // We are creating the config which means that we are also shipping
  // it in the config/install folder so we want to make sure it gets the hash
  // so Drupal treats it as a shipped config. This means that it gets exposed
  // to be translated via the locale system as well.
  $config['_core']['default_config_hash'] = Crypt::hashBase64(serialize($config));
  /** @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface $entity_storage */
  $entity_storage = $entity_type_manager->getStorage('entity_view_display');
  $entity = $entity_storage->createFromStorageRecord($config);
  $entity->save();
}
