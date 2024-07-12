<?php

/**
 * @file
 * The OE Theme Paragraphs Banner post updates.
 */

declare(strict_types=1);

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityFormMode;

/**
 * Update Banner paragraph form displays.
 */
function oe_theme_paragraphs_banner_post_update_00001(): void {
  // Update the oe_banner_image_shade form mode's label.
  $form_mode = EntityFormMode::load('paragraph.oe_banner_image_shade');
  $form_mode->set('label', 'Text overlay');
  $form_mode->save();

  // Create the simple banner form mode and form display.
  $file_storage = new FileStorage(\Drupal::service('extension.list.module')->getPath('oe_theme_paragraphs_banner') . '/config/post_updates/00001_simple_banner');
  $simple_banner = EntityFormMode::load('paragraph.oe_banner_simple');
  if (!$simple_banner) {
    $form_mode_values = $file_storage->read('core.entity_form_mode.paragraph.oe_banner_simple');
    $form_mode_values['_core']['default_config_hash'] = Crypt::hashBase64(serialize($form_mode_values));
    EntityFormMode::create($form_mode_values)->save();
  }
  $simple_banner = EntityFormDisplay::load('paragraph.oe_banner.oe_banner_simple');
  if (!$simple_banner) {
    $form_display_values = $file_storage->read('core.entity_form_display.paragraph.oe_banner.oe_banner_simple');
    $form_display_values['_core']['default_config_hash'] = Crypt::hashBase64(serialize($form_display_values));
    EntityFormDisplay::create($form_display_values)->save();
  }
}
