<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Cache;

/**
 * Interface for time based cache tag generator service.
 */
interface TimeBasedCacheTagGeneratorInterface {

  /**
   * Generate tags for the given datetime object, with increasing granularity.
   *
   * Use this to apply generated tags to a render array.
   *
   * @param \DateTime $datetime
   *   Input datetime object.
   *
   * @return array
   *   List of tags to be applied to a render array.
   */
  public function generateTags(\DateTime $datetime): array;

  /**
   * Generate tags with increasing granularity, up until the current midnight.
   *
   * Use this to apply generated tags to a render array.
   *
   * @param \DateTime $datetime
   *   Input datetime object.
   *
   * @return array
   *   List of tags to be applied to a render array.
   */
  public function generateTagsUntilMidnight(\DateTime $datetime): array;

  /**
   * Generate a list of invalidating tags.
   *
   * Pass the list generated by this method to Cache::invalidateTags().
   *
   * @param \DateTime $datetime
   *   Input datetime object.
   *
   * @return array
   *   List of invalidating tags.
   */
  public function generateInvalidatingTags(\DateTime $datetime): array;

}
