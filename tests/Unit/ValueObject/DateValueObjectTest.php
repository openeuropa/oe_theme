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

    foreach ($data as $key => $value) {
      $this::assertEquals($value, $date->{$key}());
    }

    $data = [
      'day' => '24',
      'month' => '09',
      'year' => '1981',
      'variant' => 'past',
    ];

    /** @var \Drupal\oe_theme\ValueObject\DateValueObject $date */
    $date = DateValueObject::fromArray($data);

    foreach ($data as $key => $value) {
      $this::assertEquals($value, $date->{$key}());
    }
  }

}
