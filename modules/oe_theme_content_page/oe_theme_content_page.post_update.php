<?php

/**
 * @file
 * OpenEuropa theme Organisation post updates.
 */

declare(strict_types = 1);

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Config\FileStorage;

/**
 * Create the 'full' entity view display on the page CT.
 */
function oe_theme_content_page_post_update_00001(): void {
  $storage = new FileStorage(drupal_get_path('module', 'oe_theme_content_page') . '/config/post_updates/00001_create_full_view_display');

  $entity_type_manager = \Drupal::entityTypeManager();
  $config = $storage->read('core.entity_view_display.node.oe_page.full');
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
