<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\MediaDataExtractor;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\media\MediaInterface;
use Drupal\oe_theme\ValueObject\GalleryItemValueObject;
use Drupal\oe_theme\ValueObject\ImageValueObject;
use Drupal\oe_theme\ValueObject\ImageValueObjectInterface;
use Drupal\oe_theme_helper\MediaDataExtractorPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Generic media data extractor for medias.
 *
 * @MediaDataExtractor(
 *   id = "thumbnail"
 * )
 *
 * @internal
 */
class Thumbnail extends MediaDataExtractorPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a Thumbnail object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'thumbnail_image_style' => '',
    ] + parent::defaultConfiguration();
  }

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
    if ($media->get('thumbnail') && !$media->get('thumbnail')->isEmpty() && $media->get('thumbnail')->entity) {
      $thumbnail->addCacheableDependency($media->get('thumbnail')->entity);
    }

    $configuration = $this->getConfiguration();
    if (empty($configuration['thumbnail_image_style'])) {
      return $thumbnail;
    }

    /* @var \Drupal\image\Entity\ImageStyle $image_style */
    $image_style = $this->entityTypeManager
      ->getStorage('image_style')
      ->load($configuration['thumbnail_image_style']);
    $source = $media->getSource();
    $url = $image_style->buildUrl($source->getMetadata($media, 'thumbnail_uri'));

    // Create a new image value object with the new src.
    $thumbnail = ImageValueObject::fromArray([
      'src' => file_url_transform_relative($url),
    ] + $thumbnail->getArray());
    $thumbnail->addCacheableDependency($image_style);

    return $thumbnail;
  }

  /**
   * {@inheritdoc}
   */
  public function getSource(MediaInterface $media): ?string {
    return $this->getThumbnail($media)->getSource();
  }

}
