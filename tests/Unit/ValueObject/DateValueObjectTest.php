<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Unit\Patterns;

use Drupal\oe_theme\ValueObject\DateValueObject;
use Drupal\Tests\UnitTestCase;

/**
 * Test date value object.
 */
class DateValueObjectTest extends UnitTestCase {

  /**
   * Test constructing a date value object from an array.
   *
   * @dataProvider fromArrayDataProvider
   */
  public function testFromArray(array $data, array $expected) {
    /** @var \Drupal\oe_theme\ValueObject\DateValueObject $date */
    $date = DateValueObject::fromArray($data);

    $this->assertEquals($expected['day'], $date->getDay());
    $this->assertEquals($expected['week_day'], $date->getWeekDay());
    $this->assertEquals($expected['month'], $date->getMonth());
    $this->assertEquals($expected['month_name'], $date->getMonthName());
    $this->assertEquals($expected['year'], $date->getYear());
  }

  /**
   * Data provider for DateValueObjectTest::testFromArray().
   *
   * @return array
   *   Test data.
   */
  public function fromArrayDataProvider() {
    return [

      // Only start date.
      [
        'data' => [
          'start' => 1543136400,
          'end' => NULL,
          'timezone' => 'Europe/Brussels',
        ],
        'expected' => [
          'day' => '25',
          'week_day' => 'Sun',
          'month' => '11',
          'month_name' => 'Nov',
          'year' => '2018',
        ],
      ],

      // Same day different hour.
      [
        'data' => [
          'start' => 1543136400,
          'end' => 1543140000,
          'timezone' => 'Europe/Brussels',
        ],
        'expected' => [
          'day' => '25',
          'week_day' => 'Sun',
          'month' => '11',
          'month_name' => 'Nov',
          'year' => '2018',
        ],
      ],

      // Different start and end dates.
      [
        'data' => [
          'start' => 1545732000,
          'end' => 1546250400,
          'timezone' => 'Europe/Brussels',
        ],
        'expected' => [
          'day' => '25-31',
          'week_day' => 'Tue-Mon',
          'month' => '12',
          'month_name' => 'Dec',
          'year' => '2018',
        ],
      ],

      // Different dates, same day number, different months, same year.
      [
        'data' => [
          'start' => 1543140000,
          'end' => 1545732000,
          'timezone' => 'Europe/Brussels',
        ],
        'expected' => [
          'day' => '25-25',
          'week_day' => 'Sun-Tue',
          'month' => '11-12',
          'month_name' => 'Nov-Dec',
          'year' => '2018',
        ],
      ],

      // Different dates, same day name and different years.
      [
        'data' => [
          'start' => 1545732000,
          'end' => 1546336800,
          'timezone' => 'Europe/Brussels',
        ],
        'expected' => [
          'day' => '25-01',
          'week_day' => 'Tue-Tue',
          'month' => '12-01',
          'month_name' => 'Dec-Jan',
          'year' => '2018-2019',
        ],
      ],
    ];
  }

}
