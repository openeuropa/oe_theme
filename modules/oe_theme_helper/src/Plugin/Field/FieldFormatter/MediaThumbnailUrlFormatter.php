<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\FileInterface;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Plugin implementation of the 'media_thumbnail_url' formatter.
 *
 * The formatter simply renders the final thumbnail URL.
 *
 * @FieldFormatter(
 *   id = "oe_theme_helper_media_thumbnail_url",
 *   label = @Translation("Thumbnail URL"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class MediaThumbnailUrlFormatter extends ImageFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'image_style' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);
    unset($element['image_link']);
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    // This formatter is only available for entity types that reference
    // media items.
    return ($field_definition->getFieldStorageDefinition()->getSetting('target_type') === 'media');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $media_items = $this->getEntitiesToView($items, $langcode);

    // Early opt-out if the field is empty.
    if (empty($media_items)) {
      return $elements;
    }

    $image_style_setting = $this->getSetting('image_style');

    /** @var \Drupal\media\MediaInterface[] $media_items */
    foreach ($media_items as $delta => $media) {
      // Create a new CacheableMetadata instance as it needs to be applied
      // to each element.
      $cache = new CacheableMetadata();
      $cache->addCacheableDependency($media);

      // Run access checks on the media entity.
      $access = $media->access('view', NULL, TRUE);
      $cache->addCacheableDependency($access);
      if (!$access->isAllowed()) {
        continue;
      }

      if ($media->get('thumbnail')->isEmpty()) {
        continue;
      }

      // Get default URL.
      $thumbnail_file = $media->get('thumbnail')->entity;
      if (!$thumbnail_file instanceof FileInterface) {
        continue;
      }
      $uri = $thumbnail_file->getFileUri();
      $url = file_create_url($uri);

      // Get processed URL if image style is set.
      if ($image_style_setting) {
        /* @var \Drupal\image\Entity\ImageStyle $image_style */
        $image_style = $this->imageStyleStorage->load($image_style_setting);
        $url = file_url_transform_relative($image_style->buildUrl($uri));
        $cache->addCacheableDependency($image_style);
      }
      $elements[$delta] = [
        '#markup' => $url,
      ];

      // Add cacheability of each item in the field.
      $cache->applyTo($elements[$delta]);
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   *
   * This has to be overridden because FileFormatterBase expects $item to be
   * of type \Drupal\file\Plugin\Field\FieldType\FileItem and calls
   * isDisplayed() which is not in FieldItemInterface.
   */
  protected function needsEntityLoad(EntityReferenceItem $item) {
    return !$item->hasNewEntity();
  }

}
