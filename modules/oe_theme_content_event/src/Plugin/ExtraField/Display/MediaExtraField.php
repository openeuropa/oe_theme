<?php

declare(strict_types=1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Url;
use Drupal\oe_content_event\EventNodeWrapper;

/**
 * Extra field displaying either the event media related fields.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_event_media",
 *   label = @Translation("Media gallery"),
 *   bundles = {
 *     "node.oe_event",
 *   },
 *   visible = true
 * )
 */
class MediaExtraField extends DateAwareExtraFieldBase {

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Media gallery');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    $event = new EventNodeWrapper($entity);
    $build = [
      '#theme' => 'oe_theme_content_event_media',
      '#items' => [],
    ];

    // If the event is not over then apply time-based tags, so that it can be
    // correctly invalidated once the event is over.
    if (!$event->isOver($this->requestDateTime)) {
      $this->applyHourTag($build, $event->getEndDate());
    }

    /** @var \Drupal\Core\Entity\EntityViewBuilderInterface $view_builder */
    $view_builder = $this->entityTypeManager->getViewBuilder('node');
    if (!$event->isOver($this->requestDateTime)) {
      $this->isEmpty = TRUE;
      return $build;
    }
    if (!$entity->get('oe_event_media')->isEmpty()) {
      $build['#items']['media_gallery'] = $view_builder->viewField($entity->get('oe_event_media'), [
        'label' => 'hidden',
        'type' => 'oe_theme_helper_media_gallery',
      ]);
    }
    if (!$entity->get('oe_event_media_more_link')->isEmpty()) {
      $build['#items']['media_more_link'] = [
        'url' => Url::fromUri($entity->get('oe_event_media_more_link')->getValue()[0]['uri']),
        'title' => $entity->get('oe_event_media_more_link')->getValue()[0]['title'],
      ];
    }
    if (!$entity->get('oe_event_media_more_description')->isEmpty()) {
      $build['#items']['media_more_description'] = $view_builder->viewField($entity->get('oe_event_media_more_description'), [
        'label' => 'hidden',
      ]);
    }
    if (count($build['#items']) < 1) {
      $this->isEmpty = TRUE;
      return $build;
    }

    return $build;
  }

}
