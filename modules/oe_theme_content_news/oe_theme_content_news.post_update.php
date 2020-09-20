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
 * Update news teaser view display.
 */
function oe_theme_content_news_post_update_00004(): void {
  $storage = new FileStorage(drupal_get_path('module', 'oe_theme_content_news') . '/config/post_updates/00004_update_teaser_view_display');
  $display_values = $storage->read('core.entity_view_display.node.oe_news.teaser');
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
