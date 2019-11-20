<?php

/**
 * @file
 * OpenEuropa theme helper post updates.
 */

declare(strict_types = 1);

use Drupal\block\Entity\Block;
use Drupal\image\Entity\ImageStyle;

/**
 * Use retina image styles on medium and small image styles.
 */
function oe_theme_helper_post_update_use_retina_image_styles(array &$sandbox): void {
  \Drupal::service('plugin.manager.image.effect')->clearCachedDefinitions();

  $image_styles = [
    'oe_theme_medium_2x_no_crop',
    'oe_theme_small_2x_no_crop',
  ];

  foreach ($image_styles as $image_style_name) {
    $image_style = ImageStyle::load($image_style_name);
    $effects = $image_style->getEffects();
    /** @var \Drupal\image\ImageEffectInterface $effect */
    foreach ($effects as $effect) {
      if ($effect->getPluginId() == 'image_scale') {
        $image_style->deleteImageEffect($effect);
        $configuration = $effect->getConfiguration();
        $new_configuration = [
          'id' => 'retina_image_scale',
          'data' => $configuration['data'],
          'weight' => $configuration['weight'],
        ];
        $new_configuration['data']['multiplier'] = 2;
        $image_style->addImageEffect($new_configuration);
        $image_style->save();
        break;
      }
    }
  }
}

/**
 * Delete the block oe_theme_site_switcher.
 */
function oe_theme_helper_post_update_00001() {
  // The OpenEuropa Theme 2.x was released with a hook update originally named
  // as follows. Here we restore its original name, so we are sure that this is
  // not executed twice on sites where oe_theme_helper_post_update_00001()
  // was already ran. We also keep an empty oe_theme_helper_post_update_20002()
  // since, in all cases, we need to invalidate that hook.
  $original_name = 'oe_theme_helper_post_update_20002';
  /** @var Drupal\Core\KeyValueStore\KeyValueStoreInterface $post_update_store */
  $post_update_store = \Drupal::service('keyvalue')->get('post_update');
  $executed_updates = $post_update_store->get('existing_updates');

  if (in_array($original_name, $executed_updates)) {
    return t('Original post update hook "@name" has been already executed.', ['@name' => $original_name]);
  }
  $block = Block::load('oe_theme_site_switcher');

  if (!$block) {
    return t('The oe_site_switcher block was not found.');
  }

  $block->delete();
}

/**
 * Change the region of the search block.
 */
function oe_theme_helper_post_update_20001() {
  $block = Block::load('oe_theme_search_form');

  if (!$block) {
    return t('The oe_search block was not found.');
  }

  if ($block->getTheme() == 'oe_theme') {
    $block->setRegion('site_header_secondary');
    $block->save();
  }
}

/**
 * Empty hook. Moved to oe_theme_helper_post_update_00001().
 */
function oe_theme_helper_post_update_20002() {
  // @see oe_theme_helper_post_update_00001() for more information.
}

/**
 * Add default component library theme setting.
 */
function oe_theme_helper_post_update_20003() {
  \Drupal::configFactory()->getEditable('oe_theme.settings')
    ->set('component_library', 'ec')
    ->save();
}
