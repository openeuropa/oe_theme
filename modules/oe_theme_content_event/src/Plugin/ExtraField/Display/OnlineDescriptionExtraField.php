<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\oe_content_event\EventNodeWrapper;

/**
 * Extra field displaying online description on events.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_event_online_description",
 *   label = @Translation("Online description"),
 *   bundles = {
 *     "node.oe_event",
 *   },
 *   visible = true
 * )
 */
class OnlineDescriptionExtraField extends InfoDisclosureExtraFieldBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    $build['#theme'] = 'oe_theme_content_event_online_description';
    $event = EventNodeWrapper::getInstance($entity);

    if (!$event->hasOnlineType() || !$event->hasOnlineLink() || !$event->hasOnlineDates()) {
      // All online fields have to be filled to show online information.
      $this->isEmpty = TRUE;
      return $build;
    }

    // If the livestream is over, we don't display the livestream block.
    if ($event->isOnlinePeriodOver($this->requestDateTime)) {
      $this->isEmpty = TRUE;
      return $build;
    }

    // If the livestream didn't start yet, we cache it by its start date.
    if ($event->isOnlinePeriodYetToCome($this->requestDateTime)) {
      $this->applyHourTag($build, $event->getOnlineStartDate());
      // Do not send field value to browser if it is not yet day online
      // livestreaming should be started.
      if (!$this->isCurrentDay($event->getOnlineStartDate()->getTimestamp())) {
        $this->isEmpty = TRUE;
        return $build;
      }
      // But anyway keep information hidden from users till the time
      // of online streaming has started.
      $build['#hidden'] = TRUE;
      $this->attachLivestreamDisclosure($build, $event->getOnlineStartDate()->getTimestamp());
    }

    if ($event->isOnlinePeriodActive($this->requestDateTime)) {
      // Cache it by the livestream end date.
      $this->applyHourTag($build, $event->getOnlineEndDate());
    }
    $view_builder = $this->entityTypeManager->getViewBuilder('node');
    $build['#description'] = $view_builder->viewField($entity->get('oe_event_online_description'), [
      'label' => 'hidden',
    ]);
    /** @var \Drupal\link\Plugin\Field\FieldType\LinkItem $link */
    $link = $entity->get('oe_event_online_link')->first();
    $value = $link->getValue();
    $build['#url'] = $link->getUrl();
    $build['#label'] = $value['title'];

    return $build;
  }

}
