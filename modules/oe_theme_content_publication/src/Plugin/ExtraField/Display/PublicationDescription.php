<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_publication\Plugin\ExtraField\Display;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;
use Drupal\media\Plugin\media\Source\Image;
use Drupal\media_avportal\Plugin\media\Source\MediaAvPortalPhotoSource;
use Drupal\oe_theme\ValueObject\ImageValueObject;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Display EU contribution and its percentage of the total budget.
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
   * The entity type manager.
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
    $body = $this->entityTypeManager->getViewBuilder('node')->viewField($entity->get('body'), [
      'label' => 'hidden',
    ]);

    $build = [
      '#theme' => 'oe_theme_content_publication_description',
      '#body' => $body,
    ];

    // Extract the image.
    $media_image = $entity->get('oe_publication_thumbnail')->entity;
    if ($media_image instanceof MediaInterface) {
      $media_image = \Drupal::service('entity.repository')->getTranslationFromContext($media_image);
      $cacheability = CacheableMetadata::createFromRenderArray([]);
      $cacheability->addCacheableDependency($media_image);

      // Get the media source.
      $source = $media_image->getSource();
      if ($source instanceof MediaAvPortalPhotoSource || $source instanceof Image) {
        $thumbnail = $media_image->get('thumbnail')->first();
        $build['#image'] = ImageValueObject::fromStyledImageItem($thumbnail, 'oe_theme_publication_thumbnail');
        $cacheability->addCacheableDependency($thumbnail);
      }
      $cacheability->applyTo($buld);
    }

    return $build;
  }

}
