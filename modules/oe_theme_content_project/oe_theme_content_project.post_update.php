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
 * Create the 'full' entity view display on the project CT.
 */
function oe_theme_content_project_post_update_00001() {
  $storage = new FileStorage(\Drupal::service('extension.list.module')->getPath('oe_theme_content_project') . '/config/post_updates/00001_create_full_view_display');

  $entity_type_manager = \Drupal::entityTypeManager();
  $config = $storage->read('core.entity_view_display.node.oe_project.full');
  // We are creating the config which means that we are also shipping
  // it in the config/install folder so we want to make sure it gets the hash
  // so Drupal treats it as a shipped config. This means that it gets exposed
  // to be translated via the locale system as well.
  $config['_core']['default_config_hash'] = Crypt::hashBase64(serialize($config));
  /** @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface $entity_storage */
  $entity_storage = $entity_type_manager->getStorage('entity_view_display');
  $existing = EntityViewDisplay::load('node.oe_project.full');
  if ($existing) {
    return t('Full entity view display already exists, skipping.');
  }

  $entity = $entity_storage->createFromStorageRecord($config);
  $entity->save();
}

/**
 * Updates the teaser view display.
 */
function oe_theme_content_project_post_update_00002(): void {
  $storage = new FileStorage(\Drupal::service('extension.list.module')->getPath('oe_theme_content_project') . '/config/post_updates/00002_update_teaser_view_display');

  $display_values = $storage->read('core.entity_view_display.node.oe_project.teaser');
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

/**
 * Update project displays.
 */
function oe_theme_content_project_post_update_00003(): void {
  $storage = new FileStorage(\Drupal::service('extension.list.module')->getPath('oe_theme_content_project') . '/config/post_updates/00003_update_view_displays');
  $config_names = [
    'core.entity_view_display.node.oe_project.full',
    'core.entity_view_display.node.oe_project.teaser',
  ];
  /** @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface $entity_storage */
  $entity_storage = \Drupal::entityTypeManager()->getStorage('entity_view_display');
  foreach ($config_names as $config_name) {
    $display_values = $storage->read($config_name);
    $view_display = EntityViewDisplay::load($display_values['id']);
    if ($view_display) {
      $display = $entity_storage->updateFromStorageRecord($view_display, $display_values);
      $display->save();
      continue;
    }
    $display = $entity_storage->createFromStorageRecord($display_values);
    $display->save();
  }
}

/**
 * Update project displays.
 */
function oe_theme_content_project_post_update_30001(): void {
  $storage = new FileStorage(\Drupal::service('extension.list.module')->getPath('oe_theme_content_project') . '/config/post_updates/30001_decimal_budget_fields');
  $display_values = $storage->read('core.entity_view_display.node.oe_project.full');
  $view_display = EntityViewDisplay::load($display_values['id']);
  if ($view_display) {
    $display = \Drupal::entityTypeManager()->getStorage('entity_view_display')->updateFromStorageRecord($view_display, $display_values);
    $display->save();
  }

  // Ensure new extra field is hidden on teaser display.
  $view_display = EntityViewDisplay::load('node.oe_project.teaser');
  if ($view_display) {
    $properties = $view_display->get('hidden');
    $properties['extra_field_oe_theme_content_project_budget'] = TRUE;
    $view_display->set('hidden', $properties);
    $view_display->save();
  }

}
