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

    foreach ($expected as $key => $value) {
      $this->assertEquals($value, $date->{$key}());
    }
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
