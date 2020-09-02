<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\oe_content_event\EventNodeWrapper;

/**
 * Display whereas an event is "past", "ongoing", "future" or "cancelled".
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_event_date_aware_status",
 *   label = @Translation("Date aware status"),
 *   bundles = {
 *     "node.oe_event",
 *   },
 *   visible = false
 * )
 */
class TimeStatusExtraField extends DateAwareExtraFieldBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Date aware status');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    $elements['#markup'] = 'cancelled';

    $event = EventNodeWrapper::getInstance($entity);
    // If it's cancelled, then we don't mind when the event occurs.
    if ($event->isCancelled()) {
      $elements['#markup'] = 'cancelled';
      return $elements;
    }

    // If event is not started, cache it by its start date.
    if ($this->requestDateTime < $event->getStartDate()->getPhpDateTime()) {
      $elements['#markup'] = 'future';
      $this->applyHourTag($elements, $event->getStartDate());
      return $elements;
    }

    // If event is ongoing, cache it by its end date.
    if (($this->requestDateTime >= $event->getStartDate()->getPhpDateTime()) && !$event->isOver($this->requestDateTime)) {
      $elements['#markup'] = 'ongoing';
      $this->applyHourTag($elements, $event->getEndDate());
      return $elements;
    }

    // If we reach this point, then the event has happened already.
    $elements['#markup'] = 'past';
    $cacheable = CacheableMetadata::createFromRenderArray($elements);
    $cacheable->addCacheContexts(['timezone']);
    $cacheable->applyTo($elements);

    return $elements;
  }

}
