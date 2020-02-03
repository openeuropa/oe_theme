<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;
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
class SummaryExtraField extends ExtraFieldDisplayFormattedBase {

  use StringTranslationTrait;

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
    return [
      '#lazy_builder' => [SummaryExtraField::class . '::lazyBuilder', [$entity->id()]],
      '#create_placeholder' => TRUE,
    ];
  }

  /**
   * Lazy builder callback to conditionally render the event summary.
   *
   * @param string|int|null $id
   *   Entity ID.
   *
   * @return array
   *   Render array.
   */
  public static function lazyBuilder($id): array {
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($id);
    $event = new EventNodeWrapper($node);
    $current_time = \Drupal::time()->getRequestTime();
    $now = (new \DateTime())->setTimestamp($current_time);
    $view_builder = \Drupal::entityTypeManager()->getViewBuilder('node');

    // Show description summary by default.
    $renderable = $view_builder->viewField($node->get('oe_event_description_summary'), [
      'label' => 'hidden',
    ]);

    // If the event is over and an event report summary is available, use that.
    if ($event->isOver($now) && !$node->get('oe_event_report_summary')->isEmpty()) {
      $renderable = $view_builder->viewField($node->get('oe_event_report_summary'), [
        'label' => 'hidden',
      ]);
    }

    return [
      '#theme' => 'oe_theme_content_event_summary',
      '#text' => $renderable,
    ];
  }

}
