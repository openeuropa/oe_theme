<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper;

use Drupal\Component\Plugin\PluginBase;
use Drupal\media\MediaInterface;
use Drupal\oe_theme\ValueObject\ImageValueObject;
use Drupal\oe_theme\ValueObject\ImageValueObjectInterface;

/**
 * Base class for oe_theme_media_data_extractor plugins.
 *
 * @internal
 */
abstract class MediaDataExtractorPluginBase extends PluginBase implements MediaDataExtractorInterface {

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getThumbnail(MediaInterface $media): ?ImageValueObjectInterface {
    $source = $media->getSource();
    $uri = $source->getMetadata($media, 'thumbnail_uri');

    if (empty($uri)) {
      return NULL;
    }

    /** @var \Drupal\oe_theme\ValueObject\ImageValueObject $thumbnail */
    return ImageValueObject::fromArray([
      'src' => \Drupal::service('file_url_generator')->generateString($uri),
      'alt' => $source->getMetadata($media, 'thumbnail_alt_value') ?? $media->label(),
      'name' => $media->getName(),
    ]);
  }

}
