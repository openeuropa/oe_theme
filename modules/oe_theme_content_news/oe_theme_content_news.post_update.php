<?php

/**
 * @file
 * OpenEuropa theme News post updates.
 */

declare(strict_types = 1);

use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Config\FileStorage;

/**
 * Add a date format for the News page header metadata.
 */
function oe_theme_content_news_post_update_00001(array &$sandbox): void {
  $news_date_format = DateFormat::load('oe_theme_news_date');
  $news_date_format->set('pattern', 'j F Y');
  $news_date_format->save();
}

/**
 * Enable Extra field module.
 */
function oe_theme_content_news_post_update_00002(): void {
  \Drupal::service('module_installer')->install(['extra_field', 'oe_theme_helper']);
}

/**
 * Override news teaser view display.
 */
function oe_theme_content_news_post_update_00003(): void {
  $storage = new FileStorage(drupal_get_path('module', 'oe_theme_content_news') . '/config/post_updates/00002_override_teaser_view_display');
  $display_values = $storage->read('core.entity_view_display.node.oe_news.teaser');
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
 * Enable UI Patterns field group module.
 */
function oe_theme_content_news_post_update_00004(): void {
  \Drupal::service('module_installer')->install(['ui_patterns_field_group']);
}

/**
 * Override news view displays.
 */
function oe_theme_content_news_post_update_00005(): void {
  $storage = new FileStorage(drupal_get_path('module', 'oe_theme_content_news') . '/config/post_updates/00005_override_view_displays');

  // View displays configurations to update.
  $displays = [
    'core.entity_view_display.node.oe_news.teaser',
    'core.entity_view_display.node.oe_news.default',
  ];
  foreach ($displays as $display) {
    $display_values = $storage->read($display);
    $view_display = EntityViewDisplay::load($display_values['id']);
    if ($view_display) {
      $updated_form_display = \Drupal::entityTypeManager()
        ->getStorage($view_display->getEntityTypeId())
        ->updateFromStorageRecord($view_display, $display_values);
      $updated_form_display->save();
    }
  }
}

/**
 * Enable oe_theme_content_entity_contact.
 */
function oe_theme_content_news_post_update_00006(): void {
  \Drupal::service('module_installer')->install(['oe_theme_content_entity_contact']);
}
