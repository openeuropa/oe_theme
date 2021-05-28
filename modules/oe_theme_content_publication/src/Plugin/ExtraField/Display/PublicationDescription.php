<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_publication\Plugin\ExtraField\Display;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\file\FileInterface;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;
use Drupal\image\Plugin\Field\FieldType\ImageItem;
use Drupal\media\MediaInterface;
use Drupal\media\Plugin\media\Source\Image;
use Drupal\media_avportal\Plugin\media\Source\MediaAvPortalPhotoSource;
use Drupal\oe_theme\ValueObject\ImageValueObject;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Displays publication body and thumbnail fields.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_publication_description",
 *   label = @Translation("Publication description"),
 *   bundles = {
 *     "node.oe_publication",
 *   },
 *   visible = true
 * )
 */
class PublicationDescription extends ExtraFieldDisplayFormattedBase implements ContainerFactoryPluginInterface {

  /**
   * The entity view builder.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * PublicationDescription constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
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
  public function viewElements(ContentEntityInterface $entity) {
    if ($entity->get('body')->isEmpty() && $entity->get('oe_publication_thumbnail')->isEmpty()) {
      return [];
    }

    $build = [
      '#theme' => 'oe_theme_content_publication_description',
    ];

    if (!$entity->get('body')->isEmpty()) {
      $build['#body'] = $this->entityTypeManager->getViewBuilder('node')->viewField($entity->get('body'), [
        'label' => 'hidden',
      ]);
    }

    $media = $this->getThumbnailFieldMedia($entity);
    if (!$media) {
      return $build;
    }

    $cacheability = CacheableMetadata::createFromRenderArray($build);
    $cacheability->addCacheableDependency($media);

    // Run access checks on the media entity.
    $access = $media->access('view', NULL, TRUE);
    $cacheability->addCacheableDependency($access);
    if (!$access->isAllowed()) {
      $cacheability->applyTo($build);
      return [];
    }

    $thumbnail = !$media->get('thumbnail')->isEmpty() ? $media->get('thumbnail')->first() : NULL;

    if (!$thumbnail instanceof ImageItem || !$thumbnail->entity instanceof FileInterface) {
      $cacheability->applyTo($build);
      return $build;
    }

    $image = ImageValueObject::fromStyledImageItem($thumbnail, 'oe_theme_publication_thumbnail');
    $build['#image'] = $image;

    $cacheability->addCacheableDependency($image);
    $cacheability->addCacheableDependency($thumbnail->entity);
    $cacheability->addCacheableDependency($this->entityTypeManager->getStorage('media_type')->load($media->bundle()));
    $cacheability->applyTo($build);

    return $build;
  }

  /**
   * Get media from the Thumbnail field.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Publication node instance.
   *
   * @return \Drupal\media\MediaInterface|null
   *   Media entity or NULL.
   */
  protected function getThumbnailFieldMedia(ContentEntityInterface $entity): ?MediaInterface {
    if ($entity->get('oe_publication_thumbnail')->isEmpty()) {
      return NULL;
    }

    $media = $entity->get('oe_publication_thumbnail')->entity;
    if (!$media instanceof MediaInterface) {
      // The media entity is not available anymore, bail out.
      return NULL;
    }

    // Ensure that media has correct content.
    $source = $media->getSource();
    if (!$source instanceof MediaAvPortalPhotoSource && !$source instanceof Image) {
      return NULL;
    }

    return $media;
  }

}
