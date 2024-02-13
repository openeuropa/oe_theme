<?php

declare(strict_types=1);

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
class SummaryExtraField extends DateAwareExtraFieldBase {

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
    $event = new EventNodeWrapper($entity);
    $build = ['#theme' => 'oe_theme_content_event_summary'];

    // Display 'oe_event_description_summary' by default.
    $field_name = 'oe_event_description_summary';

    // If the event is not over then apply time-based tags, so that it can be
    // correctly invalidated once the event is over.
    if (!$event->isOver($this->requestDateTime)) {
      $this->applyHourTag($build, $event->getEndDate());
    }

    // If the event is over then we use 'oe_event_report_summary', if any.
    if ($event->isOver($this->requestDateTime) && !$entity->get('oe_event_report_summary')->isEmpty()) {
      $field_name = 'oe_event_report_summary';
    }

    /** @var \Drupal\Core\Entity\EntityViewBuilderInterface $view_builder */
    $view_builder = $this->entityTypeManager->getViewBuilder('node');
    if (!$entity->get($field_name)->isEmpty()) {
      $build['#text'] = $view_builder->viewField($entity->get($field_name), [
        'label' => 'hidden',
      ]);
    }
    return $build;
  }

}
