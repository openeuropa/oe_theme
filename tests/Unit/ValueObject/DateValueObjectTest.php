<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Unit\Patterns;

use Drupal\oe_theme\ValueObject\DateValueObject;
use Drupal\Tests\oe_theme\Unit\AbstractUnitTestBase;

/**
 * Test date value object.
 */
class DateValueObjectTest extends AbstractUnitTestBase {

  /**
   * Test constructing a date value object from an array.
   *
   * @dataProvider dataProvider
   */
  public function testFromArray(array $data, array $expected) {
    /** @var \Drupal\oe_theme\ValueObject\DateValueObject $date */
    $date = DateValueObject::fromArray($data);

    $this->assertEquals($expected['day'], $date->day());
    $this->assertEquals($expected['week_day'], $date->week_day());
    $this->assertEquals($expected['month'], $date->month());
    $this->assertEquals($expected['month_name'], $date->month_name());
    $this->assertEquals($expected['year'], $date->year());
  }

  /**
   * Data provider for testFromArray() and testFromTimestamp().
   *
   * @return array
   *   Test data.
   */
  public function dataProvider() {
    return $this->getFixtureContent('value_object/date_value_object.yml');
  }

}
