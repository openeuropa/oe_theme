<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\file\FileInterface;
use Drupal\image\Plugin\Field\FieldType\ImageItem;
use Drupal\media\MediaInterface;
use Drupal\oe_content_event\EventNodeWrapper;
use Drupal\oe_theme\ValueObject\ImageValueObject;
use Drupal\oe_time_caching\Cache\TimeBasedCacheTagGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Extra field displaying the event description block.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_event_description",
 *   label = @Translation("Description"),
 *   bundles = {
 *     "node.oe_event",
 *   },
 *   visible = true
 * )
 */
class DescriptionExtraField extends DateAwareExtraFieldBase implements ContainerFactoryPluginInterface {

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * DescriptionExtraField constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   Time service.
   * @param \Drupal\oe_time_caching\Cache\TimeBasedCacheTagGeneratorInterface $cache_tag_generator
   *   Time based cache tag generator service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, TimeInterface $time, TimeBasedCacheTagGeneratorInterface $cache_tag_generator, EntityRepositoryInterface $entity_repository) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $time, $cache_tag_generator);
    $this->entityRepository = $entity_repository;
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
      $container->get('datetime.time'),
      $container->get('oe_time_caching.time_based_cache_tag_generator'),
      $container->get('entity.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Description');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    // Display event description using "text_featured_media" pattern.
    $build = [
      '#type' => 'pattern',
      '#id' => 'text_featured_media',
      '#fields' => [
        'title' => $this->getRenderableTitle($entity),
        'text' => $this->getRenderableText($entity),
      ],
    ];

    $this->addFeaturedMediaThumbnail($build, $entity);

    return $build;
  }

  /**
   * Get a renderable section title: either 'Description' or 'Report'.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Content entity.
   *
   * @return array
   *   Render array.
   */
  public function getRenderableTitle(ContentEntityInterface $entity): array {
    $event = EventNodeWrapper::getInstance($entity);

    // By default the title is 'Description'.
    $build = ['#markup' => t('Description')];

    // If the event is not over then apply time-based tags, so that it can be
    // correctly invalidated once the event is over.
    if (!$event->isOver($this->requestDateTime)) {
      $this->applyHourTag($build, $event->getEndDate());
      return $build;
    }

    // If the event is over and we have a report, then change the title.
    if (!$entity->get('oe_event_report_text')->isEmpty()) {
      $build = ['#markup' => t('Report')];
    }

    return $build;
  }

  /**
   * Get a renderable event description.
   *
   * By default, we show the event body field. If, however, the event has
   * passed AND an event report is available, we show that instead.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Content entity.
   *
   * @return array
   *   Render array.
   */
  public function getRenderableText(ContentEntityInterface $entity): array {
    $event = EventNodeWrapper::getInstance($entity);

    // By default 'body' is the event description field.
    // If the event is over and we have a report, we use 'oe_event_report_text'.
    $field_name = 'body';
    if ($event->isOver($this->requestDateTime) && !$entity->get('oe_event_report_text')->isEmpty()) {
      $field_name = 'oe_event_report_text';
    }

    /** @var \Drupal\Core\Entity\EntityViewBuilderInterface $view_builder */
    $view_builder = $this->entityTypeManager->getViewBuilder('node');
    $build = $view_builder->viewField($entity->get($field_name), [
      'label' => 'hidden',
    ]);

    // If the event is not over then apply time-based tags, so that it can be
    // correctly invalidated once the event is over.
    if (!$event->isOver($this->requestDateTime)) {
      $this->applyHourTag($build, $event->getEndDate());
    }

    return $build;
  }

  /**
   * Adds the featured media thumbnail to the build.
   *
   * @param array $build
   *   The description field build.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   */
  protected function addFeaturedMediaThumbnail(array &$build, ContentEntityInterface $entity): void {
    if ($entity->get('oe_event_featured_media')->isEmpty()) {
      return;
    }

    $media = $entity->get('oe_event_featured_media')->entity;
    if (!$media instanceof MediaInterface) {
      return;
    }

    // Retrieve the translation of the media entity.
    $media = $this->entityRepository->getTranslationFromContext($media);

    $cache = new CacheableMetadata();
    $cache->addCacheableDependency($media);
    $thumbnail = !$media->get('thumbnail')->isEmpty() ? $media->get('thumbnail')->first() : NULL;
    if (!$thumbnail instanceof ImageItem || !$thumbnail->entity instanceof FileInterface) {
      $cache->applyTo($build);
      return;
    }

    $cache->addCacheableDependency($thumbnail->entity);
    $build['#fields']['image'] = ImageValueObject::fromImageItem($thumbnail);

    // Only display a caption if we have an image to be captioned by.
    /** @var \Drupal\Core\Entity\EntityViewBuilderInterface $view_builder */
    $view_builder = $this->entityTypeManager->getViewBuilder('node');
    $build['#fields']['caption'] = $view_builder->viewField($entity->get('oe_event_featured_media_legend'), [
      'label' => 'hidden',
    ]);

    $cache->applyTo($build);
  }

}
