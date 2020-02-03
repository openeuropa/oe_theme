<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;
use Drupal\oe_content_event\EventNodeWrapper;

/**
 * Extra field displaying the event registration button.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_event_registration_button",
 *   label = @Translation("Registration button"),
 *   bundles = {
 *     "node.oe_event",
 *   },
 *   visible = true
 * )
 */
class RegistrationButtonExtraField extends ExtraFieldDisplayFormattedBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    return [
      '#lazy_builder' => [RegistrationButtonExtraField::class . '::lazyBuilder', [$entity->id()]],
      '#create_placeholder' => TRUE,
    ];
  }

  /**
   * Lazy builder callback to render registration button.
   *
   * @param string|int|null $id
   *   Entity ID.
   *
   * @return array
   *   Render array.
   */
  public static function lazyBuilder($id): array {
    /** @var \Drupal\Core\Datetime\DateFormatter $date_formatter */
    $date_formatter = \Drupal::service('date.formatter');
    $current_time = \Drupal::time()->getRequestTime();
    $now = (new \DateTime())->setTimestamp($current_time);
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($id);
    $event = new EventNodeWrapper($node);

    // If event has no registration information then don't display anything.
    if (!$event->hasRegistration()) {
      return [];
    }

    /** @var \Drupal\link\Plugin\Field\FieldType\LinkItem $link */
    $link = $node->get('oe_event_registration_url')->first();

    // Set default registration button values.
    $build = [
      '#theme' => 'oe_theme_content_event_registration_button',
      '#label' => t('Register'),
      '#url' => $link->getUrl()->toString(),
      '#description' => t('Register here'),
      '#enabled' => FALSE,
    ];

    // Registration is active.
    if ($event->isRegistrationPeriodActive($now) && $event->isRegistrationOpen()) {
      $date_diff = $date_formatter->formatDiff($now->getTimestamp(), $event->getRegistrationEndDate()->getTimestamp(), ['granularity' => 1]);
      $build['#description'] = t('Book your seat, @time_left left to register.', [
        '@time_left' => $date_diff,
      ]);
      $build['#enabled'] = TRUE;

      return $build;
    }

    // Registration yet has to come.
    if ($event->isRegistrationPeriodYetToCome($now)) {
      $build['#label'] = t('Registration will open on @start_date, until @end_date.', [
        '@start_date' => $date_formatter->format($event->getRegistrationStartDate()->getTimestamp(), 'oe_event_date_hour'),
        '@end_date' => $date_formatter->format($event->getRegistrationEndDate()->getTimestamp(), 'oe_event_date_hour'),
      ]);
      $build['#description'] = t('Registration will open on @date', [
        '@date' => $date_formatter->format($event->getRegistrationStartDate()->getTimestamp(), 'oe_event_long_date_hour'),
      ]);

      return $build;
    }

    // Registration period is over.
    if ($event->isRegistrationPeriodOver($now)) {
      $build['#label'] = t('Registration period ended on @date', [
        '@date' => $date_formatter->format($event->getRegistrationEndDate()->getTimestamp(), 'oe_event_long_date_hour'),
      ]);
      $build['#description'] = t('Registration for this event has ended.');

      return $build;
    }

    // Registration period is closed.
    if ($event->isRegistrationClosed()) {
      $build['#label'] = t('Registration is now closed.');
      $build['#description'] = t('Registration is now closed for this event.');

      return $build;
    }

    return $build;
  }

}
