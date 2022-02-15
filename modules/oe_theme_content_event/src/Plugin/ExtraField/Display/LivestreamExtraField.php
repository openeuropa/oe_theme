<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\oe_content_event\EventNodeWrapper;

/**
 * Extra field displaying livestream information on events.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_event_livestream",
 *   label = @Translation("Livestream"),
 *   bundles = {
 *     "node.oe_event",
 *   },
 *   visible = true
 * )
 */
class LivestreamExtraField extends InfoDisclosureExtraFieldBase {

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Livestream');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    $build = ['#theme' => 'oe_theme_content_event_livestream'];
    $event = EventNodeWrapper::getInstance($entity);

    // All the online fields have to be filled to show online information.
    if (!$event->hasOnlineType() || !$event->hasOnlineLink() || !$event->hasOnlineDates()) {
      $this->isEmpty = TRUE;
      return $build;
    }
    // If the livestream is over, we don't display any info.
    if ($event->isOnlinePeriodOver($this->requestDateTime)) {
      $this->isEmpty = TRUE;
      return $build;
    }
    $link = $entity->get('oe_event_online_link')->first();
    $value = $link->getValue();
    $link = [
      '#url' => $link->getUrl(),
      '#label' => $value['title'],
    ];
    // If the livestream didn't start yet, we cache it by its start date and
    // render the date only.
    if ($event->isOnlinePeriodYetToCome($this->requestDateTime)) {
      $this->applyHourTag($build, $event->getOnlineStartDate());
      if ($this->isCurrentDay($event->getOnlineStartDate()->getTimestamp())) {
        $build += $link;
        $build['#hide_link'] = TRUE;
        $this->attachLivestreamDisclosure($build, $event->getOnlineStartDate()->getTimestamp());
      }
    }
    $build['#date'] = $this->t('Starts on @date', [
      '@date' => $this->dateFormatter->format($event->getOnlineStartDate()->getTimestamp(), 'oe_event_long_date_hour_timezone', '', $event->getOnlineTimezone()),
    ]);
    // If the livestream is ongoing, we add the livestream link.
    if ($event->isOnlinePeriodActive($this->requestDateTime)) {
      // Cache it by its end date.
      $this->applyHourTag($build, $event->getOnlineEndDate());
      $build += $link;
    }

    return $build;
  }

}
