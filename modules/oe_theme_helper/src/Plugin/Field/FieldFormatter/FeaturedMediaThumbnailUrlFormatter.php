<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;
use Drupal\file\FileInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Display the featured media thumbnail URL.
 *
 * @FieldFormatter(
 *   id = "oe_theme_helper_featured_media_thumbnail_url_formatter",
 *   label = @Translation("Thumbnail URL"),
 *   description = @Translation("Display the featured media thumbnail URL."),
 *   field_types = {
 *     "oe_featured_media"
 *   }
 * )
 */
class FeaturedMediaThumbnailUrlFormatter extends EntityReferenceFormatterBase {

  /**
   * The image style entity storage.
   *
   * @var \Drupal\image\ImageStyleStorageInterface
   */
  protected $imageStyleStorage;

  /**
   * Constructs a FeaturedMediaThumbnailUrlFormatter object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Entity\EntityStorageInterface $image_style_storage
   *   The image style storage.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, EntityStorageInterface $image_style_storage) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->imageStyleStorage = $image_style_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager')->getStorage('image_style'));
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    // Early opt-out if the field is empty.
    if (empty($items)) {
      return $elements;
    }

    $image_style_setting = $this->getSetting('image_style');

    foreach ($items as $delta => $item) {
      $media = $item->entity;
      // Create a new CacheableMetadata instance as it needs to be applied
      // to each element.
      $cache = new CacheableMetadata();
      $cache->addCacheableDependency($media);
      $cache->addCacheableDependency($items->get($delta)->_accessCacheability);

      if ($media->get('thumbnail')->isEmpty()) {
        // In case the thumbnail is missing from the media entity, we should
        // apply the cache metadata of the media to the render array.
        $cache->applyTo($elements[$delta]);
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

}
