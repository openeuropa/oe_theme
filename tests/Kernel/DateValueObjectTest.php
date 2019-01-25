<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\datetime_range\Plugin\Field\FieldType\DateRangeItem;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\oe_theme\ValueObject\DateValueObject;
use Drupal\Tests\datetime_range\Kernel\DateRangeItemTest;
use Symfony\Component\Yaml\Yaml;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

/**
 * Test date value object with datetime_range field type.
 */
class DateValueObjectTest extends DateRangeItemTest {

  /**
   * Test constructing a date value object from an DateRangeItem.
   *
   * @param array $data
   *   The array with start and end date.
   * @param array $expected
   *   The array with expected values.
   *
   * @dataProvider dataProvider
   */
  public function testFromDateRangeItem(array $data, array $expected): void {
    $this->fieldStorage->setSetting('datetime_type', DateRangeItem::DATETIME_TYPE_DATE);
    $field_name = $this->fieldStorage->getName();

    $start_date_formatted = gmdate(DateTimeItemInterface::DATE_STORAGE_FORMAT, $data['start']);
    $end_date_formatted = gmdate(DateTimeItemInterface::DATE_STORAGE_FORMAT, $data['end'] ?? $data['start']);

    // Create an entity.
    $entity = EntityTest::create([
      'name' => $this->randomString(),
      $field_name => [
        'value' => $start_date_formatted,
        'end_value' => $end_date_formatted,
      ],
    ]);
    $entity->save();

    $date = DateValueObject::fromDateRangeItem($entity->{$field_name}->first());

    $this->assertEquals($expected['day'], $date->getDay());
    $this->assertEquals($expected['week_day'], $date->getWeekDay());
    $this->assertEquals($expected['month'], $date->getMonth());
    $this->assertEquals($expected['month_name'], $date->getMonthName());
    $this->assertEquals($expected['year'], $date->getYear());
  }

  /**
   * Get fixture content.
   *
   * @return array
   *   A set of test data.
   */
  public function dataProvider(): ?array {
    return Yaml::parse(file_get_contents(__DIR__ . "/../Unit/fixtures/value_object/date_value_object.yml"));
  }

}
