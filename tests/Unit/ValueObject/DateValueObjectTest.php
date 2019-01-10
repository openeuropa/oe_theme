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
   */
  public function testFromArray() {
    $data = [
      'day' => '24',
      'month' => '09',
      'year' => '1981',
    ];

    /** @var \Drupal\oe_theme\ValueObject\DateValueObject $date */
    $date = DateValueObject::fromArray($data);

    $this->assertEquals('24', $date->getDay());
    $this->assertEquals('Thursday', $date->getWeekDay());
    $this->assertEquals('09', $date->getMonth());
    $this->assertEquals('September', $date->getMonthName());
    $this->assertEquals('1981', $date->getYear());
  }

  /**
   * Test constructing a date value object from a timestamp.
   */
  public function testFromTimestamp() {
    /** @var \Drupal\oe_theme\ValueObject\DateValueObject $date */
    $date = DateValueObject::fromTimestamp(370137600);

    $this->assertEquals('24', $date->getDay());
    $this->assertEquals('Thursday', $date->getWeekDay());
    $this->assertEquals('09', $date->getMonth());
    $this->assertEquals('September', $date->getMonthName());
    $this->assertEquals('1981', $date->getYear());
  }

}
