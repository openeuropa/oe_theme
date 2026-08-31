<?php

declare(strict_types=1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\oe_content_event\EventNodeWrapper;

/**
 * Display Event status.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_event_status",
 *   label = @Translation("Status"),
 *   bundles = {
 *     "node.oe_event",
 *   },
 *   visible = true
 * )
 */
class EventStatusExtraField extends DateAwareExtraFieldBase {

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Status');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    if ($this->getViewMode() !== 'teaser') {
      $this->isEmpty = TRUE;
      return [];
    }

    $cacheable = CacheableMetadata::createFromRenderArray(['#cache' => ['contexts' => ['timezone']]]);
    $event = EventNodeWrapper::getInstance($entity);

    $labels = [
      'cancelled' => $this->t('Cancelled'),
      'rescheduled' => $this->t('Rescheduled'),
      'postponed' => $this->t('Postponed'),
    ];
    $name = $entity->get('oe_event_status')->value;
    if (isset($labels[$name])) {
      $label = $labels[$name];
      $variant = 'low';
    }
    // If the event is not started, cache it by its start date.
    elseif ($this->requestDateTime < $event->getStartDate()->getPhpDateTime()) {
      $name = 'future';
      $label = $this->t('Upcoming');
      $variant = 'medium';
      $cacheable->addCacheTags($this->cacheTagGenerator->generateTags($event->getStartDate()->getPhpDateTime()));
    }
    // If the event is ongoing, cache it by its end date.
    elseif (!$event->isOver($this->requestDateTime)) {
      $name = 'ongoing';
      $label = $this->t('Ongoing');
      $variant = 'high';
      $cacheable->addCacheTags($this->cacheTagGenerator->generateTags($event->getEndDate()->getPhpDateTime()));
    }
    // If we reach this point, then the event has happened already.
    else {
      $name = 'past';
      $label = $this->t('Past');
      $variant = 'low';
    }

    $build = [
      '#theme' => 'oe_theme_content_event_status',
      '#label' => $label,
      '#name' => $name,
      '#variant' => $variant,
    ];
    $cacheable->applyTo($build);

    return $build;
  }

}
