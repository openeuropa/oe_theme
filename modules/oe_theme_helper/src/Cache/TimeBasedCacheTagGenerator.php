<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Cache;

use Carbon\Carbon;

/**
 * Generate Drupal time-based cache tags.
 */
class TimeBasedCacheTagGenerator implements TimeBasedCacheTagGeneratorInterface {

  /**
   * Drupal cache tag prefix.
   */
  const TAG_PREFIX = 'oe_theme_helper_date:';

  /**
   * Date format appended to the tag prefix above, with hour granularity.
   */
  const DATE_FORMAT_GRANULARITY_HOUR = 'Y-m-d-H';

  /**
   * Date format appended to the tag prefix above, with day granularity.
   */
  const DATE_FORMAT_GRANULARITY_DAY = 'Y-m-d';

  /**
   * Date format appended to the tag prefix above, with month granularity.
   */
  const DATE_FORMAT_GRANULARITY_MONTH = 'Y-m';

  /**
   * Date format appended to the tag prefix above, with yeah granularity.
   */
  const DATE_FORMAT_GRANULARITY_YEAR = 'Y';

  /**
   * {@inheritdoc}
   */
  public function generateTags(\DateTime $datetime): array {
    return [
      static::TAG_PREFIX . $datetime->format(self::DATE_FORMAT_GRANULARITY_YEAR),
      static::TAG_PREFIX . $datetime->format(self::DATE_FORMAT_GRANULARITY_MONTH),
      static::TAG_PREFIX . $datetime->format(self::DATE_FORMAT_GRANULARITY_DAY),
      static::TAG_PREFIX . $datetime->format(self::DATE_FORMAT_GRANULARITY_HOUR),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function generateTagsUntilMidnight(\DateTime $datetime): array {
    $midnight = Carbon::instance($datetime)->floorDay();
    return $this->generateTags($midnight);
  }

  /**
   * {@inheritdoc}
   */
  public function generateInvalidatingTags(\DateTime $datetime): array {
    $tags = [];

    // Generate year granularity invalidation tags, up to 5 years in the back.
    $start = Carbon::instance($datetime)->floorYear()->subYears(5);
    $end = Carbon::instance($datetime)->floorYear();
    $period = $start->toPeriod($end, '1 year');
    $period->excludeEndDate();
    foreach ($period as $item) {
      $tags[] = static::TAG_PREFIX . $item->format(self::DATE_FORMAT_GRANULARITY_YEAR);
    };

    // Generate month granularity invalidation tags, for the current year.
    $start = Carbon::instance($datetime)->floorYear();
    $end = Carbon::instance($datetime)->floorMonth();
    $period = $start->toPeriod($end, '1 month');
    $period->excludeEndDate();
    foreach ($period as $item) {
      $tags[] = static::TAG_PREFIX . $item->format(self::DATE_FORMAT_GRANULARITY_MONTH);
    };

    // Generate day granularity invalidation tags, for the current year.
    $start = Carbon::instance($datetime)->floorMonth();
    $end = Carbon::instance($datetime)->floorDay();
    $period = $start->toPeriod($end, '1 day');
    $period->excludeEndDate();
    foreach ($period as $item) {
      $tags[] = static::TAG_PREFIX . $item->format(self::DATE_FORMAT_GRANULARITY_DAY);
    };

    // Generate hour granularity invalidation tags, for the current day.
    $start = Carbon::instance($datetime)->floorDay();
    $period = $start->toPeriod($datetime, '1 hour');
    $period->excludeEndDate();
    foreach ($period as $item) {
      $tags[] = static::TAG_PREFIX . $item->format(self::DATE_FORMAT_GRANULARITY_HOUR);
    };

    return $tags;
  }

}
