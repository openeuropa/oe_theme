<?php

/**
 * @file
 * OpenEuropa theme News post updates.
 */

declare(strict_types = 1);

use Drupal\image\Entity\ImageStyle;

/**
 * Update dimensions of 'List item' image style.
 */
function oe_theme_content_news_post_update_20001(array &$sandbox): void {
  $image_style = ImageStyle::load('oe_theme_list_item');
  $effects = $image_style->getEffects();
  /** @var \Drupal\image\ImageEffectInterface $effect */
  foreach ($effects as $effect) {
    if ($effect->getPluginId() === 'image_scale_and_crop') {
      $configuration = $effect->getConfiguration();
      $image_style->deleteImageEffect($effect);
      $configuration['data']['width'] = 180;
      $configuration['data']['height'] = 180;
      $image_style->addImageEffect($configuration);
      $image_style->save();
      break;
    }
  }
}
