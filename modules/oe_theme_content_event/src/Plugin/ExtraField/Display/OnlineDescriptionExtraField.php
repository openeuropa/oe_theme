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
class OnlineDescriptionExtraField extends DateAwareExtraFieldBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    $event = EventNodeWrapper::getInstance($entity);

    if (!$event->hasOnlineType() || !$event->hasOnlineLink() || !$event->hasOnlineDates()) {
      // All online fields have to be filled to show online information.
      return [];
    }

    $build = [];

    if ($event->isOnlinePeriodYetToCome($this->requestDateTime)) {
      // Invalidate cache tags when livestream will be started.
      $this->applyHourTag($build, $event->getOnlineStartDate());
    }

    if ($event->isOnlinePeriodActive($this->requestDateTime)) {
      // Invalidate cache tags when livestream will be ended.
      $this->applyHourTag($build, $event->getOnlineEndDate());

      $build['#theme'] = 'oe_theme_content_event_online_description';
      $view_builder = $this->entityTypeManager->getViewBuilder('node');
      $build['#description'] = $view_builder->viewField($entity->get('oe_event_online_description'), [
        'label' => 'hidden',
      ]);

      /** @var \Drupal\link\Plugin\Field\FieldType\LinkItem $link */
      $link = $entity->get('oe_event_online_link')->first();
      $value = $link->getValue();
      $build['#url'] = $link->getUrl();
      $build['#label'] = $value['title'];
    }

    return $build;
  }

}
