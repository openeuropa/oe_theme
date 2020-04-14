<?php

namespace Drupal\oe_theme_helper\PatternGenerator;


use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\oe_theme\ValueObject\ImageValueObject;

trait PatternGeneratorMediaTrait {

  protected function processImageField(FieldItemInterface $item, CacheableMetadata $cache) {
    if ($item->getFieldDefinition()->getType() !== 'image') {
      return [];
    }

    $file = $item->get('entity')->getValue();
    $cache->addCacheableDependency($file);
    return [
      'src' => file_url_transform_relative(file_create_url($file->getFileUri())),
      'alt' => $item->get('alt')->getValue(),
    ];
  }

  protected function processImageBasedMediaField(FieldItemInterface $item, CacheableMetadata $cache, $image_style = NULL) {
    $media = $item->entity;
    $thumbnail = $media->get('thumbnail')->first();
    if (!$image_style) {
      return ImageValueObject::fromImageItem($thumbnail);
    }
    return ImageValueObject::fromStyledImageItem($thumbnail, $image_style);
  }

}
