<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\oe_content_event\EventNodeWrapper;
use Drupal\oe_theme_helper\Cache\TimeBasedCacheTagGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
class RegistrationButtonExtraField extends RegistrationDateAwareExtraFieldBase {

  /**
   * Date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * RegistrationButtonExtraField constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\oe_theme_helper\Cache\TimeBasedCacheTagGeneratorInterface $cache_tag_generator
   *   Time based cache tag generator service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, TimeInterface $time, TimeBasedCacheTagGeneratorInterface $cache_tag_generator, DateFormatterInterface $date_formatter) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $time, $cache_tag_generator);
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('datetime.time'),
      $container->get('oe_theme_helper.time_based_cache_tag_generator'),
      $container->get('date.formatter')
    );
  }

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
    ];

    // Current request happens before the registration starts.
    if ($event->isRegistrationPeriodYetToCome($this->requestDateTime)) {
      $datetime_start = $event->getRegistrationStartDate();
      $datetime_end = $event->getRegistrationEndDate();
      $request_datetime = DrupalDateTime::createFromTimestamp($this->requestTime);

      // If the request time is on the same day as the start day we need to
      // show different message.
      if ($datetime_start->format('Ymd') === $request_datetime->format('Ymd')) {
        $build['#description'] = $this->t('Registration will open today, @start_date.', [
          '@start_date' => $this->dateFormatter->format($datetime_start->getTimestamp(), 'oe_event_date_hour'),
        ]);
      }
      else {
        $date_diff_formatted = $this->dateFormatter->formatDiff($this->requestTime, $datetime_start->getTimestamp());
        $build['#description'] = $this->t('Registration will open in @time_left. You can register from @start_date, until @end_date.', [
          '@time_left' => $date_diff_formatted,
          '@start_date' => $this->dateFormatter->format($datetime_start->getTimestamp(), 'oe_event_date_hour'),
          '@end_date' => $this->dateFormatter->format($datetime_end->getTimestamp(), 'oe_event_date_hour'),
        ]);
      }

      $build['#enabled'] = FALSE;

      // We invalidate this message every day at midnight.
      $this->applyMidnightTag($build, $datetime_start);

      // We invalidate this message when the registration period starts.
      $this->applyHourTag($build, $datetime_start);
      return $build;
    }

    // Current request happens within the registration period.
    if ($event->isRegistrationPeriodActive($this->requestDateTime)) {
      $datetime_end = $event->getRegistrationEndDate();
      $request_datetime = DrupalDateTime::createFromTimestamp($this->requestTime);

      // If the request time is on the same day as the end day we need to
      // show different message.
      if ($datetime_end->format('Ymd') === $request_datetime->format('Ymd')) {
        $build['#description'] = $this->t('Book your seat, the registration will end today, @end_date', [
          '@end_date' => $this->dateFormatter->format($datetime_end->getTimestamp(), 'oe_event_date_hour'),
        ]);
      }
      else {
        $date_diff_formatted = $this->dateFormatter->formatDiff($this->requestTime, $datetime_end->getTimestamp(), ['granularity' => 1]);
        $build['#description'] = $this->t('Book your seat, @time_left left to register, registration will end on @end_date', [
          '@time_left' => $date_diff_formatted,
          '@end_date' => $this->dateFormatter->format($datetime_end->getTimestamp(), 'oe_event_date_hour'),
        ]);
      }

      // We invalidate this message every day at midnight.
      $this->applyMidnightTag($build, $datetime_end);

      // We invalidate this message when the registration period ends.
      $this->applyHourTag($build, $datetime_end);
      return $build;
    }

    // Current request happens after the registration has ended.
    if ($event->isRegistrationPeriodOver($this->requestDateTime)) {
      $datetime_end = $event->getRegistrationEndDate();
      $build['#description'] = $this->t('Registration period ended on @date', [
        '@date' => $this->dateFormatter->format($datetime_end->getTimestamp(), 'oe_event_long_date_hour'),
      ]);
      $build['#show_button'] = FALSE;

      return $build;
    }

    return $build;
  }

}
