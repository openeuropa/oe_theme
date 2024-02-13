<?php

declare(strict_types=1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\oe_time_caching\Cache\TimeBasedCacheTagGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for fields that require display in exact time.
 */
abstract class InfoDisclosureExtraFieldBase extends DateAwareExtraFieldBase {

  /**
   * Date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * InfoDisclosureExtraFieldBase constructor.
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
   * Asserts if a given timestamp matches to the same day of the current time.
   *
   * @param int $timestamp
   *   The timestamp of start date.
   * @param string|null $timezone
   *   The timezone id.
   *
   * @return bool
   *   True if timestamp is within current day.
   */
  protected function isCurrentDay(int $timestamp, string $timezone = NULL): bool {
    $current_date = $this->dateFormatter->format($this->requestDateTime->getTimestamp(), 'custom', 'Ymd', $timezone);
    $start_day = $this->dateFormatter->format($timestamp, 'custom', 'Ymd', $timezone);
    return $current_date === $start_day;
  }

  /**
   * Add disclosure script.
   *
   * @param array $build
   *   The render array.
   * @param int $timestamp
   *   The timestamp of start date.
   */
  abstract protected function attachDisclosureScript(array &$build, int $timestamp): void;

}
