<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\file\FileInterface;
use Drupal\image\Plugin\Field\FieldType\ImageItem;
use Drupal\media\MediaInterface;
use Drupal\oe_content_event\EventNodeWrapper;
use Drupal\oe_theme\ValueObject\ImageValueObject;

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
class DescriptionExtraField extends RegistrationDateAwareExtraFieldBase {

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
        'caption' => $this->getRenderableFeaturedMediaLegend($entity),
      ],
    ];

    // Get media thumbnail and add media entity as cacheable dependency.
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
    $title = $this->t('Description');

    // If we are past the end date and an event report is available, set title.
    if ($event->isOver($this->requestDateTime) && !$entity->get('oe_event_report_text')->isEmpty()) {
      $title = $this->t('Report');
    }

    $build = ['#markup' => $title];
    $this->applyRegistrationDatesMaxAge($build, $event);
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

    $field_name = ($event->isOver($this->requestDateTime) && !$entity->get('oe_event_report_text')->isEmpty()) ? 'oe_event_report_text' : 'body';
    return $this->entityTypeManager->getViewBuilder('node')->viewField($entity->get($field_name), [
      'label' => 'hidden',
    ]);
  }

  /**
   * Get event featured media legend as a renderable array.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Content entity.
   *
   * @return array
   *   Renderable array.
   */
  protected function getRenderableFeaturedMediaLegend(ContentEntityInterface $entity): array {
    return $this->entityTypeManager->getViewBuilder('node')->viewField($entity->get('oe_event_featured_media_legend'), [
      'label' => 'hidden',
    ]);
  }

  /**
   * Adds the featured media thumbnail to the build.
   *
   * @param array $build
   *   The description field build.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   */
  protected function addFeaturedMediaThumbnail(array $build, ContentEntityInterface $entity): void {
    if ($entity->get('oe_event_featured_media')->isEmpty()) {
      return;
    }

    $media = $entity->get('oe_event_featured_media')->entity;
    if (!$media instanceof MediaInterface) {
      return;
    }

    $cache = new CacheableMetadata();
    $cache->addCacheableDependency($media);
    $thumbnail = !$media->get('thumbnail')->isEmpty() ? $media->get('thumbnail')->first() : NULL;
    if (!$thumbnail instanceof ImageItem || !$thumbnail->entity instanceof FileInterface) {
      $cache->applyTo($build);
      return;
    }

    $cache->addCacheableDependency($thumbnail->entity);
    $build['#fields']['image'] = ImageValueObject::fromImageItem($thumbnail);
    $cache->applyTo($build);
  }

}
