<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\oe_content_event\EventNodeWrapper;

/**
 * Extra field displaying either the event summary or a report summary.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_event_summary",
 *   label = @Translation("Summary"),
 *   bundles = {
 *     "node.oe_event",
 *   },
 *   visible = true
 * )
 */
class SummaryExtraField extends RegistrationDateAwareExtraFieldBase {

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Summary');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    $event = EventNodeWrapper::getInstance($entity);

    /** @var \Drupal\Core\Entity\EntityViewBuilderInterface $view_builder */
    $view_builder = $this->entityTypeManager->getViewBuilder('node');

    // If the event is over and an event report summary is available, use that.
    if ($event->isOver($this->requestDateTime) && !$entity->get('oe_event_report_summary')->isEmpty()) {
      $renderable = $view_builder->viewField($entity->get('oe_event_report_summary'), [
        'label' => 'hidden',
      ]);
    }
    else {
      // Otherwise, show the description summary.
      $renderable = $view_builder->viewField($entity->get('oe_event_description_summary'), [
        'label' => 'hidden',
      ]);
    }

    $build = [
      '#theme' => 'oe_theme_content_event_summary',
      '#text' => $renderable,
    ];

    $this->applyRegistrationDatesMaxAge($build, $event);

    return $build;
  }

}
