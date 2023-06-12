<?php

/**
 * @file
 * OpenEuropa theme Publication post updates.
 */

declare(strict_types = 1);

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\image\Entity\ImageStyle;

/**
 * Add a date format for the Publication page header metadata.
 */
function oe_theme_content_publication_post_update_00001_add_publication_date_format(array &$sandbox): void {
  $date_format_values = [
    'langcode' => 'en',
    'status' => TRUE,
    'dependencies' => [],
    'id' => 'oe_theme_publication_date',
    'label' => 'Publication date',
    'locked' => FALSE,
    'pattern' => 'd F Y',
  ];
  $publication_date_format = DateFormat::create($date_format_values);
  $publication_date_format->save();
}

/**
 * Update a date format for the Publication page header metadata.
 */
function oe_theme_content_publication_post_update_00002(array &$sandbox): void {
  $publication_date_format = DateFormat::load('oe_theme_publication_date');
  $publication_date_format->set('pattern', 'j F Y');
  $publication_date_format->save();
}

/**
 * Enable Extra field module.
 */
function oe_theme_content_publication_post_update_00003(): void {
  \Drupal::service('module_installer')->install([
    'extra_field',
    'oe_theme_helper',
  ]);
}

/**
 * Override publication teaser view display.
 */
function oe_theme_content_publication_post_update_00004(): void {
  $storage = new FileStorage(\Drupal::service('extension.list.module')->getPath('oe_theme_content_publication') . '/config/post_updates/00003_override_teaser_view_display');
  $display_values = $storage->read('core.entity_view_display.node.oe_publication.teaser');
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
 * Enable OpenEuropa Theme Content Entity Contact module.
 */
function oe_theme_content_publication_post_update_00005(): void {
  \Drupal::service('module_installer')->install(['oe_theme_content_entity_contact']);
}

/**
 * Add Publication thumbnail image style.
 */
function oe_theme_content_publication_post_update_00006() {
  // If the image style already exists, we bail out.
  $style = \Drupal::entityTypeManager()->getStorage('image_style')->load('oe_theme_publication_thumbnail');
  if ($style) {
    return 'The image style was previously created.';
  }

  // Create image style.
  $image_style = ImageStyle::create([
    'name' => 'oe_theme_publication_thumbnail',
    'label' => 'Publication thumbnail',
  ]);

  // Create effect.
  $effect = [
    'id' => 'image_scale',
    'weight' => 1,
    'data' => [
      'width' => 192,
      'height' => 192,
      'upscale' => FALSE,
    ],
  ];

  // Add effect to the image style and save.
  $image_style->addImageEffect($effect);
  $image_style->save();
}

/**
 * Create the 'full' entity view display on the publication CT.
 */
function oe_theme_content_publication_post_update_00007() {
  $storage = new FileStorage(\Drupal::service('extension.list.module')->getPath('oe_theme_content_publication') . '/config/post_updates/00007_create_full_view_display');

  $entity_type_manager = \Drupal::entityTypeManager();
  $config = $storage->read('core.entity_view_display.node.oe_publication.full');
  // We are creating the config which means that we are also shipping
  // it in the config/install folder so we want to make sure it gets the hash
  // so Drupal treats it as a shipped config. This means that it gets exposed
  // to be translated via the locale system as well.
  $config['_core']['default_config_hash'] = Crypt::hashBase64(serialize($config));
  /** @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface $entity_storage */
  $entity_storage = $entity_type_manager->getStorage('entity_view_display');
  $existing = $entity_storage->load('node.oe_publication.full');
  if ($existing) {
    return t('Full entity view display already exists, skipping.');
  }

  $entity = $entity_storage->createFromStorageRecord($config);
  $entity->save();
}

/**
 * Update publication teaser view display.
 */
function oe_theme_content_publication_post_update_00008(): void {
  $storage = new FileStorage(\Drupal::service('extension.list.module')->getPath('oe_theme_content_publication') . '/config/post_updates/00008_update_teaser_view_display');
  $display_values = $storage->read('core.entity_view_display.node.oe_publication.teaser');
  // We are creating the config which means that we are also shipping
  // it in the config/install folder so we want to make sure it gets the hash
  // so Drupal treats it as a shipped config. This means that it gets exposed
  // to be translated via the locale system as well.
  $display_values['_core']['default_config_hash'] = Crypt::hashBase64(serialize($display_values));
  $storage = \Drupal::entityTypeManager()->getStorage('entity_view_display');

  // Take over teaser view display, regardless if it already exists or not.
  $view_display = $storage->load($display_values['id']);
  if ($view_display) {
    $display = $storage->updateFromStorageRecord($view_display, $display_values);
    $display->save();
    return;
  }

  $display = $storage->createFromStorageRecord($display_values);
  $display->save();
}

/**
 * Set labels in teaser view mode to be hidden.
 */
function oe_theme_content_publication_post_update_00009() {
  $display = EntityViewDisplay::load('node.oe_publication.teaser');

  if (!$display instanceof EntityViewDisplayInterface) {
    return t('No publication teaser view mode found, skipping.');
  }

  $fields = [
    'oe_author',
    'oe_publication_date',
    'oe_publication_thumbnail',
    'oe_publication_type',
    'oe_teaser',
  ];
  foreach ($fields as $field) {
    $component = $display->getComponent($field);
    if ($component === NULL) {
      continue;
    }

    $component['label'] = 'hidden';
    $display->setComponent($field, $component);
  }
  $display->save();
}

/**
 * Updates the teaser view display.
 */
function oe_theme_content_publication_post_update_00010(): void {
  $storage = new FileStorage(\Drupal::service('extension.list.module')->getPath('oe_theme_content_publication') . '/config/post_updates/00010_update_teaser_view_display');

  $display_values = $storage->read('core.entity_view_display.node.oe_publication.teaser');
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
 * Updates the full view display.
 */
function oe_theme_content_publication_post_update_30001(): void {
  $storage = new FileStorage(\Drupal::service('extension.list.module')->getPath('oe_theme_content_publication') . '/config/post_updates/30001_publication_collection');

  $display_values = $storage->read('core.entity_view_display.node.oe_publication.full');
  $storage = \Drupal::entityTypeManager()->getStorage('entity_view_display');

  $view_display = EntityViewDisplay::load($display_values['id']);
  if ($view_display) {
    $display = $storage->updateFromStorageRecord($view_display, $display_values);
    $display->save();
  }
}
