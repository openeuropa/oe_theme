<?php

/**
 * @file
 * OpenEuropa theme helper post updates.
 */

declare(strict_types = 1);

use Drupal\block\Entity\Block;
use Drupal\Component\Utility\Crypt;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\Entity\Entity\EntityViewMode;
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
  $config_path = \Drupal::service('extension.list.theme')->getPath('oe_theme') . '/config/optional';
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

/**
 * Invalidate container after changing twig_extension service definition.
 */
function oe_theme_helper_post_update_20010() {
  \Drupal::service('kernel')->invalidateContainer();
}

/**
 * Enable Twig field value module.
 */
function oe_theme_helper_post_update_20011(): void {
  \Drupal::service('module_installer')->install(['twig_field_value']);
  \Drupal::service('kernel')->invalidateContainer();
}

/**
 * Change the region of the language switcher block.
 */
function oe_theme_helper_post_update_20012() {
  $block = Block::load('oe_theme_language_switcher');

  if (!$block) {
    return t('The oe_theme_language_switcher block was not found.');
  }

  if ($block->getTheme() == 'oe_theme') {
    $block->setRegion('site_header_secondary');
    $block->save();
  }
}

/**
 * Use navigation block plugin for main navigation.
 */
function oe_theme_helper_post_update_20013() {
  if (!Block::load('oe_theme_main_navigation')) {
    return 'The oe_theme_main_navigation block was not found.';
  }

  // Clear block definitions so the new block is discoverable.
  \Drupal::service('plugin.manager.block')->clearCachedDefinitions();

  // Use the new navigation block as main navigation.
  $config = \Drupal::configFactory()->getEditable('block.block.oe_theme_main_navigation');
  $label = $config->get('settings.label');
  $config->set('plugin', 'oe_theme_helper_site_navigation:main');
  $config->set('settings', [
    'id' => 'oe_theme_helper_site_navigation:main',
    'label' => $label,
    'provider' => 'oe_theme_helper',
    'label_display' => '0',
    'level' => 1,
  ]);
  $config->save();
}

/**
 * Set default visibility condition of main navigation block.
 */
function oe_theme_helper_post_update_20014() {
  /** @var \Drupal\block\Entity\Block $block */
  $block = Block::load('oe_theme_main_navigation');

  if (!$block) {
    return t('The oe_theme_main_navigation block was not found.');
  }

  if ($block->getTheme() == 'oe_theme') {
    $block->setVisibilityConfig('oe_theme_helper_current_branding', [
      'id' => 'oe_theme_helper_current_branding',
      'branding' => 'standardised',
    ]);
    $block->save();
  }
}

/**
 * Create oe_theme_main_content view mode for Iframe media.
 */
function oe_theme_helper_post_update_20015() {
  if (!\Drupal::moduleHandler()->moduleExists('oe_media_iframe')) {
    // Since core.entity_view_display.media.iframe.oe_theme_main_content is
    // optional config we have to ensure that module is enabled.
    return t('Skipping since the oe_media_iframe module is not enabled.');
  }

  $file_storage = new FileStorage(\Drupal::service('extension.list.theme')->getPath('oe_theme') . '/config/post_updates/20015_create_view_display_media_iframe');
  $view_display_values = $file_storage->read('core.entity_view_display.media.iframe.oe_theme_main_content');
  $entity_view_display_storage = \Drupal::entityTypeManager()->getStorage('entity_view_display');
  $view_display = $entity_view_display_storage->load($view_display_values['id']);
  if (!$view_display) {
    $view_display_values['_core']['default_config_hash'] = Crypt::hashBase64(serialize($view_display_values));
    $entity_view_display_storage->create($view_display_values)->save();
  }
}

/**
 * Create the OpenEuropa: Compact teaser view mode for node entities.
 */
function oe_theme_helper_post_update_20016() {
  if (EntityViewMode::load('node.oe_compact_teaser')) {
    // We bail out if it already exists.
    return t('Skipping since the view mode node.oe_compact_teaser already exists.');
  }
  EntityViewMode::create([
    'id' => 'node.oe_compact_teaser',
    'targetEntityType' => 'node',
    'status' => TRUE,
    'enabled' => TRUE,
    'label' => 'OpenEuropa: Compact teaser',
  ])->save();
}
