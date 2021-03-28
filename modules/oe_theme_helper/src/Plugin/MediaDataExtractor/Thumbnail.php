<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\MediaDataExtractor;

use Drupal\media\MediaInterface;
use Drupal\oe_theme\ValueObject\GalleryItemValueObject;
use Drupal\oe_theme\ValueObject\ImageValueObject;
use Drupal\oe_theme\ValueObject\ImageValueObjectInterface;
use Drupal\oe_theme_helper\MediaDataExtractorPluginBase;

/**
 * Generic media data extractor for medias.
 *
 * @MediaDataExtractor(
 *   id = "thumbnail"
 * )
 *
 * @internal
 */
class Thumbnail extends MediaDataExtractorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getGalleryMediaType(): string {
    return GalleryItemValueObject::TYPE_IMAGE;
  }

  /**
   * {@inheritdoc}
   */
  public function getThumbnail(MediaInterface $media): ?ImageValueObjectInterface {
    /** @var \Drupal\oe_theme\ValueObject\ImageValueObjectInterface $thumbnail */
    $thumbnail = parent::getThumbnail($media);

    if (!$thumbnail instanceof ImageValueObject) {
      return $thumbnail;
    }

    // Add thumbnail file entity cache information.
    if (!$media->get('thumbnail')->isEmpty() && $media->get('thumbnail')->entity) {
      $thumbnail->addCacheableDependency($media->get('thumbnail')->entity);
    }

    return $thumbnail;
  }

  /**
   * {@inheritdoc}
   */
  public function getSource(MediaInterface $media): ?string {
    return $this->getThumbnail($media)->getSource();
  }

}
