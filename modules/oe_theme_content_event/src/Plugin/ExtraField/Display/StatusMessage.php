<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\oe_content_event\EventNodeWrapper;
use Drupal\oe_time_caching\Cache\TimeBasedCacheTagGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Extra field displaying the event status messages.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_event_status_message",
 *   label = @Translation("Status message"),
 *   bundles = {
 *     "node.oe_event",
 *   },
 *   visible = true
 * )
 */
class StatusMessage extends DateAwareExtraFieldBase {

  /**
   * Date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * StatusMessage extra field constructor.
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
   *   Time service.
   * @param \Drupal\oe_time_caching\Cache\TimeBasedCacheTagGeneratorInterface $cache_tag_generator
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
      $container->get('oe_time_caching.time_based_cache_tag_generator'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Status message');
  }

  /**
   * {@inheritdoc}
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   * @SuppressWarnings(PHPMD.NPathComplexity)
   */
  public function viewElements(ContentEntityInterface $entity) {
    // Render event status messages using the ECL Message component.
    $event = new EventNodeWrapper($entity);
    $build = ['#theme' => 'oe_theme_content_event_status_message'];

    $cacheable = CacheableMetadata::createFromRenderArray($build);

    // If the event is not over then apply time-based tags, so that it can be
    // correctly invalidated once the event is over.
    if (!$event->isOver($this->requestDateTime)) {
      $this->applyHourTag($build, $event->getEndDate());
    }

    // Display message based on current event status.
    if (!$event->isAsPlanned()) {
      $build['#variant'] = 'warning';
      $build['#icon'] = 'warning';
      $status = $entity->get('oe_event_status')->value;
      $build['#title'] = $this->t('This event has been @status.', ['@status' => $status]);
      // Add the 'Status description' field value if available.
      if (!$entity->get('oe_event_status_description')->isEmpty()) {
        $build['#description'] = $entity->get('oe_event_status_description')->value;
      }
      // Add timezone cache contexts.
      $cacheable->addCacheContexts(['timezone']);
      $cacheable->applyTo($build);
      return $build;
    }

    // If we have an online type set, the livestream messages have priority
    // over the "As planned" event messages.
    if ($event->hasOnlineType()) {
      $build['#variant'] = 'warning';
      $build['#icon'] = 'livestreaming';

      // The livestream is over, but the event is still ongoing.
      if ($event->isOnlinePeriodOver($this->requestDateTime) && $event->isOngoing($this->requestDateTime)) {
        // Cache it by the event end date and add the timezone cache contexts.
        $build['#title'] = $this->t('The livestream has ended, but the event is ongoing.');
        $this->applyHourTag($build, $event->getEndDate());
        return $build;
      }

      // The event is ongoing but the livestream is yet to start.
      if ($event->isOngoing($this->requestDateTime) && $event->isOnlinePeriodYetToCome($this->requestDateTime)) {
        // Cache it by the event end date and its livestream start date.
        $this->applyHourTag($build, $event->getEndDate());
        $this->applyHourTag($build, $event->getOnlineStartDate());
        $livestream_date = $this->dateFormatter->format($entity->get('oe_event_online_dates')->start_date->getTimestamp(), 'oe_event_date_hour_timezone');
        $build['#title'] = $this->t('This event has started. The livestream will start at @date.', ['@date' => $livestream_date]);
        return $build;
      }

      // If the livestream is ongoing, cache it by its end date.
      if ($event->isOnlinePeriodActive($this->requestDateTime)) {
        $this->applyHourTag($build, $event->getOnlineEndDate());

        // The event is over, apply timezone cache contexts.
        if ($event->isOver($this->requestDateTime)) {
          $build['#title'] = $this->t('This event has ended, but the livestream is ongoing.');
          $cacheable->addCacheContexts(['timezone']);
          $cacheable->applyTo($build);
          return $build;
        }

        // If event is not started, cache it by its start date.
        if ($this->requestDateTime < $event->getStartDate()->getPhpDateTime()) {
          $build['#title'] = $this->t('The livestream has started.');
          $this->applyHourTag($build, $event->getStartDate());
          return $build;
        }

        // If event is ongoing, cache it by its end date.
        if ($event->isOngoing($this->requestDateTime)) {
          $build['#title'] = $this->t('This event has started. You can also watch it via livestream.');
          $this->applyHourTag($build, $event->getEndDate());
          return $build;
        }
      }
    }

    // If the event status is 'As planned', we'll add the title based on the
    // event state (ongoing or past).
    if ($event->isAsPlanned()) {
      $build['#variant'] = 'info';
      $build['#icon'] = 'information';
      if ($event->isOver($this->requestDateTime)) {
        // Add timezone cache contexts.
        $cacheable->addCacheContexts(['timezone']);
        $cacheable->applyTo($build);
        $build['#title'] = $this->t('This event has ended.');
        return $build;
      }
      // If event is ongoing, cache it by its end date.
      if ($event->isOngoing($this->requestDateTime)) {
        $build['#title'] = $this->t('This event has started.');
        $this->applyHourTag($build, $event->getEndDate());
        return $build;
      }
    }

    // If we don't have a title set, we do not display any status message.
    if (!isset($build['#title'])) {
      $this->isEmpty = TRUE;
    }
    return $build;
  }

}
