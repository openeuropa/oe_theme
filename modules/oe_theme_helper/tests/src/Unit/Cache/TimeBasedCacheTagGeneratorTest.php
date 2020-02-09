<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\Unit\Cache;

use Drupal\oe_theme_helper\Cache\TimeBasedCacheTagGenerator;
use Drupal\Tests\UnitTestCase;

/**
 * Test the generation of Drupal time-based cache tags.
 */
class TimeBasedCacheTagGeneratorTest extends UnitTestCase {

  /**
   * Test the generation a list of tags.
   */
  public function testGenerateTags() {
    $date = \DateTime::createFromFormat('Y-m-d H:i:s', '2020-02-15 12:35:13');
    $this->assertEquals([
      'oe_theme_helper_date:2020',
      'oe_theme_helper_date:2020-02',
      'oe_theme_helper_date:2020-02-15',
      'oe_theme_helper_date:2020-02-15-12',
    ], (new TimeBasedCacheTagGenerator())->generateTags($date));
  }

  /**
   * Test the generation a list of tags, up until midnight.
   */
  public function testGenerateTagsUntilMidnight() {
    $date = \DateTime::createFromFormat('Y-m-d H:i:s', '2020-02-15 12:35:13');
    $this->assertEquals([
      'oe_theme_helper_date:2020',
      'oe_theme_helper_date:2020-02',
      'oe_theme_helper_date:2020-02-15',
      'oe_theme_helper_date:2020-02-15-00',
    ], (new TimeBasedCacheTagGenerator())->generateTagsUntilMidnight($date));
  }

  /**
   * Test the generation of a list invalidating tags.
   */
  public function testGenerateInvalidatingTags() {
    $date = \DateTime::createFromFormat('Y-m-d H:i:s', '2020-02-15 12:35:13');
    $this->assertEquals([
      'oe_theme_helper_date:2015',
      'oe_theme_helper_date:2016',
      'oe_theme_helper_date:2017',
      'oe_theme_helper_date:2018',
      'oe_theme_helper_date:2019',
      'oe_theme_helper_date:2020-01',
      'oe_theme_helper_date:2020-02-01',
      'oe_theme_helper_date:2020-02-02',
      'oe_theme_helper_date:2020-02-03',
      'oe_theme_helper_date:2020-02-04',
      'oe_theme_helper_date:2020-02-05',
      'oe_theme_helper_date:2020-02-06',
      'oe_theme_helper_date:2020-02-07',
      'oe_theme_helper_date:2020-02-08',
      'oe_theme_helper_date:2020-02-09',
      'oe_theme_helper_date:2020-02-10',
      'oe_theme_helper_date:2020-02-11',
      'oe_theme_helper_date:2020-02-12',
      'oe_theme_helper_date:2020-02-13',
      'oe_theme_helper_date:2020-02-14',
      'oe_theme_helper_date:2020-02-15-00',
      'oe_theme_helper_date:2020-02-15-01',
      'oe_theme_helper_date:2020-02-15-02',
      'oe_theme_helper_date:2020-02-15-03',
      'oe_theme_helper_date:2020-02-15-04',
      'oe_theme_helper_date:2020-02-15-05',
      'oe_theme_helper_date:2020-02-15-06',
      'oe_theme_helper_date:2020-02-15-07',
      'oe_theme_helper_date:2020-02-15-08',
      'oe_theme_helper_date:2020-02-15-09',
      'oe_theme_helper_date:2020-02-15-10',
      'oe_theme_helper_date:2020-02-15-11',
      'oe_theme_helper_date:2020-02-15-12',
    ], (new TimeBasedCacheTagGenerator())->generateInvalidatingTags($date));
  }

}
