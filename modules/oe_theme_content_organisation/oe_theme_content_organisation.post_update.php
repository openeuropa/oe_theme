<?php

/**
 * @file
 * OpenEuropa theme Organisation post updates.
 */

declare(strict_types = 1);

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\Entity\Entity\EntityViewDisplay;

/**
 * Create the 'full' entity view display on the organisation CT.
 */
function oe_theme_content_organisation_post_update_00001() {
  $storage = new FileStorage(drupal_get_path('module', 'oe_theme_content_organisation') . '/config/post_updates/00001_create_full_view_display');

  $entity_type_manager = \Drupal::entityTypeManager();
  $config = $storage->read('core.entity_view_display.node.oe_organisation.full');
  // We are creating the config which means that we are also shipping
  // it in the config/install folder so we want to make sure it gets the hash
  // so Drupal treats it as a shipped config. This means that it gets exposed
  // to be translated via the locale system as well.
  $config['_core']['default_config_hash'] = Crypt::hashBase64(serialize($config));
  /** @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface $entity_storage */
  $entity_storage = $entity_type_manager->getStorage('entity_view_display');
  $existing = $entity_storage->load('node.oe_organisation.full');
  if ($existing) {
    return t('Full entity view display already exists, skipping.');
  }

  $entity = $entity_storage->createFromStorageRecord($config);
  $entity->save();
}

/**
 * Update the 'full' entity view display on the organisation CT.
 */
function oe_theme_content_organisation_post_update_00002(): void {
  $storage = new FileStorage(drupal_get_path('module', 'oe_theme_content_organisation') . '/config/post_updates/00002_update_full_view_display');

  $display_values = $storage->read('core.entity_view_display.node.oe_organisation.full');
  $view_display = EntityViewDisplay::load($display_values['id']);
  if ($view_display) {
    $updated_display = \Drupal::entityTypeManager()
      ->getStorage($view_display->getEntityTypeId())
      ->updateFromStorageRecord($view_display, $display_values);
    $updated_display->save();
  }
}
