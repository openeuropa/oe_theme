<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\MediaDataExtractor;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\media\MediaInterface;
use Drupal\oe_theme\ValueObject\GalleryItemValueObject;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Media data extractor for iframe medias.
 *
 * @MediaDataExtractor(
 *   id = "iframe",
 * )
 *
 * @internal
 */
class Iframe extends Thumbnail {

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs an Iframe object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, RendererInterface $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entityTypeManager);

    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getGalleryMediaType(): string {
    return GalleryItemValueObject::TYPE_VIDEO;
  }

  /**
   * {@inheritdoc}
   */
  public function getSource(MediaInterface $media): ?string {
    $source_field = $media->getSource()->getSourceFieldDefinition($media->bundle->entity)->getName();
    $build = $this->entityTypeManager->getViewBuilder('media')->viewField($media->get($source_field), 'oe_theme_main_content');

    // Bubble the cacheability information in the current render context.
    $this->renderer->renderPlain($build);

    return $build[0]['#attributes']['src'] ?? NULL;
  }

}
