<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
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
class RegistrationButtonExtraField extends InfoDisclosureExtraFieldBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    $event = EventNodeWrapper::getInstance($entity);

    // If event has no registration information or the event is over
    // then we don't display the whole registration block.
    if (!$event->hasRegistration() || $event->isOver($this->requestDateTime)) {
      return [];
    }

    /** @var \Drupal\link\Plugin\Field\FieldType\LinkItem $link */
    $link = $entity->get('oe_event_registration_url')->first();

    // Set default registration button values.
    $build = [
      '#theme' => 'oe_theme_content_event_registration_button',
      '#label' => $this->t('Register here'),
      '#url' => $link->getUrl(),
      '#enabled' => TRUE,
      '#show_button' => TRUE,
      '#registration_day' => FALSE,
    ];

    // Current request happens after the registration has ended.
    if ($event->isRegistrationPeriodOver($this->requestDateTime)) {
      $datetime_end = $event->getRegistrationEndDate();
      $build['#description'] = $this->t('Registration period ended on @date', [
        '@date' => $this->dateFormatter->format($datetime_end->getTimestamp(), 'oe_event_long_date_hour_timezone', '', $event->getRegistrationTimezone()),
      ]);
      $build['#show_button'] = FALSE;

      return $build;
    }

    $datetime_end = $event->getRegistrationEndDate();

    // Current request happens before the registration starts.
    if ($event->isRegistrationPeriodYetToCome($this->requestDateTime)) {
      $datetime_start = $event->getRegistrationStartDate();

      // We invalidate this message every day at midnight.
      $this->applyMidnightTag($build, $datetime_start);

      // We invalidate this message when the registration period starts.
      $this->applyHourTag($build, $datetime_start);

      $build['#enabled'] = FALSE;

      // If the request time is on the same day as the start day we need to
      // show different message.
      if ($this->isCurrentDay($datetime_start->getTimestamp(), $event->getRegistrationTimezone())) {
        $build['#registration_day_description'] = $this->t('Registration will open today, @start_date.', [
          '@start_date' => $this->dateFormatter->format($datetime_start->getTimestamp(), 'oe_event_date_hour_timezone', '', $event->getRegistrationTimezone()),
        ]);
        $build['#registration_day'] = TRUE;
        $this->attachDisclosureScript($build, $datetime_start->getTimestamp());
      }
      else {
        $date_diff_formatted = $this->dateFormatter->formatDiff($this->requestTime, $datetime_start->getTimestamp(), ['granularity' => 1]);
        $build['#description'] = $this->t('Registration will open in @time_left. You can register from @start_date, until @end_date.', [
          '@time_left' => $date_diff_formatted,
          '@start_date' => $this->dateFormatter->format($datetime_start->getTimestamp(), 'oe_event_date_hour_timezone', '', $event->getRegistrationTimezone()),
          '@end_date' => $this->dateFormatter->format($datetime_end->getTimestamp(), 'oe_event_date_hour_timezone', '', $event->getRegistrationTimezone()),
        ]);
        return $build;
      }
    }

    // Current request happens within the registration period.
    if ($event->isRegistrationPeriodActive($this->requestDateTime)) {
      // We invalidate this message every day at midnight.
      $this->applyMidnightTag($build, $datetime_end);

      // We invalidate this message when the registration period ends.
      $this->applyHourTag($build, $datetime_end);
    }

    if ($event->hasRegistrationDates()) {
      // If the request time is on the same day as the end day we need to
      // show different message.
      if ($this->isCurrentDay($datetime_end->getTimestamp(), $event->getRegistrationTimezone())) {
        $build['#description'] = $this->t('Book your seat, the registration will end today, @end_date', [
          '@end_date' => $this->dateFormatter->format($datetime_end->getTimestamp(), 'oe_event_date_hour_timezone', '', $event->getRegistrationTimezone()),
        ]);
      }
      else {
        $date_diff_formatted = $this->dateFormatter->formatDiff($this->requestTime, $datetime_end->getTimestamp(), ['granularity' => 1]);
        $build['#description'] = $this->t('Book your seat, @time_left left to register, registration will end on @end_date', [
          '@time_left' => $date_diff_formatted,
          '@end_date' => $this->dateFormatter->format($datetime_end->getTimestamp(), 'oe_event_date_hour_timezone', '', $event->getRegistrationTimezone()),
        ]);
      }
    }
    return $build;
  }

  /**
   * Add registration information disclosure script.
   *
   * {@inheritdoc}
   */
  protected function attachDisclosureScript(array &$build, int $timestamp): void {
    $build['#attached'] = [
      'library' => 'oe_theme_content_event/registration_link_disclosure',
      'drupalSettings' => [
        'oe_theme_content_event' => [
          'registration_start_timestamp' => $timestamp * 1000,
        ],
      ],
    ];
  }

}
