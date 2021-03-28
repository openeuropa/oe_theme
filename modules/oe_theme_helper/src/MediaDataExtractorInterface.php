<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\media\MediaInterface;
use Drupal\oe_theme\ValueObject\ImageValueObjectInterface;

/**
 * Interface for oe_theme_media_data_extractor plugins.
 *
 * @internal
 */
interface MediaDataExtractorInterface extends ConfigurableInterface {

  /**
   * Returns the type of media to use in the media gallery.
   *
   * @return string
   *   The type to use in the media gallery. Either image or video.
   */
  public function getGalleryMediaType(): string;

  /**
   * Returns the thumbnail for a media entity.
   *
   * @param \Drupal\media\MediaInterface $media
   *   The media entity.
   *
   * @return \Drupal\oe_theme\ValueObject\ImageValueObjectInterface|null
   *   The thumbnail information wrapped in a value object.
   */
  public function getThumbnail(MediaInterface $media): ?ImageValueObjectInterface;

  /**
   * Returns the source URL of a media.
   *
   * @param \Drupal\media\MediaInterface $media
   *   The media entity.
   *
   * @return string|null
   *   The source URL if available.
   */
  public function getSource(MediaInterface $media): ?string;

}
