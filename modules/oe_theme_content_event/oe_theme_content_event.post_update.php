<?php

/**
 * @file
 * OpenEuropa theme event post updates.
 */

declare(strict_types = 1);

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Datetime\Entity\DateFormat;
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
 * Create the 'full' entity view display on the event CT.
 */
function oe_theme_content_event_post_update_00002() {
  $storage = new FileStorage(drupal_get_path('module', 'oe_theme_content_event') . '/config/post_updates/00002_create_full_view_display');

  $entity_type_manager = \Drupal::entityTypeManager();
  $config = $storage->read('core.entity_view_display.node.oe_event.full');
  // We are creating the config which means that we are also shipping
  // it in the config/install folder so we want to make sure it gets the hash
  // so Drupal treats it as a shipped config. This means that it gets exposed
  // to be translated via the locale system as well.
  $config['_core']['default_config_hash'] = Crypt::hashBase64(serialize($config));
  /** @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface $entity_storage */
  $entity_storage = $entity_type_manager->getStorage('entity_view_display');
  $existing = EntityViewDisplay::load('node.oe_event.full');
  if ($existing) {
    return t('Full entity view display already exists, skipping.');
  }

  $entity = $entity_storage->createFromStorageRecord($config);
  $entity->save();
}

/**
 * Updates the teaser view display.
 */
function oe_theme_content_event_post_update_00003(): void {
  $storage = new FileStorage(drupal_get_path('module', 'oe_theme_content_event') . '/config/post_updates/00003_update_teaser_view_display');

  $display_values = $storage->read('core.entity_view_display.node.oe_event.teaser');
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
 * Replace date fields separator.
 */
function oe_theme_content_event_post_update_30003(): void {
  $view_display = EntityViewDisplay::load('node.oe_event.full');
  foreach (['oe_event_dates', 'oe_event_online_dates'] as $field) {
    $component = $view_display->getComponent($field);
    $component['settings']['separator'] = '-';
    $view_display->setComponent($field, $component);
  }
  $view_display->save();
}

/**
 * Update the 'full' entity view display on the event CT.
 */
function oe_theme_content_event_post_update_30001() {
  \Drupal::service('module_installer')->install(['oe_content_event_event_programme']);
  $storage = new FileStorage(drupal_get_path('module', 'oe_theme_content_event') . '/config/post_updates/30001_update_full_view_display');
  $view_display_values = $storage->read('core.entity_view_display.node.oe_event.full');
  $view_display = EntityViewDisplay::load($view_display_values['id']);
  if ($view_display) {
    $updated_view_display = \Drupal::entityTypeManager()
      ->getStorage($view_display->getEntityTypeId())
      ->updateFromStorageRecord($view_display, $view_display_values);
    $updated_view_display->save();
  }

  $date_formats = [
    'core.date_format.oe_event_programme_date',
    'core.date_format.oe_event_programme_date_hour',
    'core.date_format.oe_event_programme_hour',
    'core.date_format.oe_event_programme_date_timezone',
    'core.date_format.oe_event_programme_date_hour_timezone',
    'core.date_format.oe_event_long_date_hour_timezone',
  ];
  foreach ($date_formats as $date_format_name) {
    $config = $storage->read($date_format_name);
    // We are creating the config which means that we are also shipping
    // it in the config/install folder so we want to make sure it gets the hash
    // so Drupal treats it as a shipped config. This means that it gets exposed
    // to be translated via the locale system as well.
    $config['_core']['default_config_hash'] = Crypt::hashBase64(serialize($config));
    $date_format = DateFormat::create($config);
    $date_format->save();
  }
}
