<?php

/**
 * @file
 * OpenEuropa theme helper post updates.
 */

declare(strict_types = 1);

use Drupal\block\Entity\Block;
use Drupal\Core\Config\FileStorage;
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
    return t('The oe_theme_site_switcher block was not found.');
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

/**
 * Clear condition plugin cache.
 */
function oe_theme_helper_post_update_20004() {
  \Drupal::service('plugin.manager.condition')->clearCachedDefinitions();
}

/**
 * Remove current corporate footer block.
 */
function oe_theme_helper_post_update_20005() {
  \Drupal::configFactory()->getEditable('block.block.oe_theme_corporate_footer')->delete();
}

/**
 * Add EC and EU corporate blocks to active configuration storage.
 */
function oe_theme_helper_post_update_20006() {
  $config_path = drupal_get_path('theme', 'oe_theme') . '/config/optional';
  $source = new FileStorage($config_path);
  $config_storage = \Drupal::service('config.storage');
  $config_factory = \Drupal::configFactory();

  $blocks = [
    'block.block.oe_theme_ec_corporate_footer',
    'block.block.oe_theme_eu_corporate_footer',
  ];

  foreach ($blocks as $block) {
    $config_storage->write($block, $source->read($block));
    $config_factory->getEditable($block)->save();
  }
}

/**
 * Add Ratio 3:2 medium image style.
 */
function oe_theme_helper_post_update_20007(array &$sandbox) {
  // If the image style already exists, we bail out.
  $style = \Drupal::entityTypeManager()->getStorage('image_style')->load('oe_theme_ratio_3_2_medium');
  if ($style) {
    return 'The image style was previously created.';
  }

  // Create image style.
  $image_style = ImageStyle::create([
    'name' => 'oe_theme_ratio_3_2_medium',
    'label' => 'Ratio 3:2 medium',
  ]);

  // Create effect.
  $effect = [
    'id' => 'image_scale_and_crop',
    'weight' => 1,
    'data' => [
      'anchor' => 'center-center',
      'width' => '600',
      'height' => '400',
    ],
  ];

  // Add effect to the image style and save.
  $image_style->addImageEffect($effect);
  $image_style->save();
}

/**
 * Install new theme dependency smart_trim module.
 */
function oe_theme_helper_post_update_20008(array &$sandbox) {
  \Drupal::service('module_installer')->install(['smart_trim']);
}

/**
 * Install new theme dependency oe_time_caching module.
 */
function oe_theme_helper_post_update_20009(array &$sandbox) {
  \Drupal::service('module_installer')->install(['oe_time_caching']);
}
