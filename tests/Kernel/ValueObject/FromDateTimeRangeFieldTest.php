<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\ValueObject;

use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\datetime_range\Plugin\Field\FieldType\DateRangeItem;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\oe_theme\ValueObject\DateValueObject;

/**
 * Test date value object with datetime_range field type.
 */
class FromDateTimeRangeFieldTest extends FromDateTimeFieldTestBase {

  /**
   * A field storage to use in this test class.
   *
   * @var \Drupal\field\Entity\FieldStorageConfig
   */
  protected $fieldStorage;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'datetime',
    'datetime_range',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Add a datetime range field.
    $this->fieldStorage = FieldStorageConfig::create([
      'field_name' => mb_strtolower($this->randomMachineName()),
      'entity_type' => 'entity_test',
      'type' => 'daterange',
      'settings' => ['datetime_type' => DateRangeItem::DATETIME_TYPE_DATE],
    ]);
    $this->fieldStorage->save();

    FieldConfig::create([
      'field_storage' => $this->fieldStorage,
      'bundle' => 'entity_test',
      'required' => TRUE,
    ])->save();

  }

  /**
   * Test constructing a date value object from an DateRangeItem.
   *
   * @param array $data
   *   The array with start and end date.
   * @param array $expected
   *   The array with expected values.
   *
   * @dataProvider dataProviderForFactory
   */
  public function testFromDateRangeItem(array $data, array $expected): void {
    $date = DateValueObject::fromDateRangeItem($this->getDateRangeItemInstance($data));

    $this->assertEquals($expected['day'], $date->getDay());
    $this->assertEquals($expected['week_day'], $date->getWeekDay());
    $this->assertEquals($expected['month'], $date->getMonth());
    $this->assertEquals($expected['month_name'], $date->getMonthName());
    $this->assertEquals($expected['year'], $date->getYear());
  }

  /**
   * Test date block pattern rendering, built from an DateRangeItem object.
   *
   * @param string $variant
   *   Date block variant.
   * @param array $date
   *   Date object passed as an array.
   * @param array $assertions
   *   Test assertions.
   *
   * @throws \Exception
   *
   * @dataProvider renderingDataProvider
   */
  public function testDateRangeField(string $variant, array $date, array $assertions): void {
    $pattern = [
      '#type' => 'pattern',
      '#id' => 'date_block',
      '#variant' => $variant,
      '#fields' => [
        'date' => DateValueObject::fromDateRangeItem($this->getDateRangeItemInstance($date)),
      ],
    ];

    $html = $this->renderRoot($pattern);
    $this->assertRendering($html, $assertions);
  }

  /**
   * Get instance (value) of datetime_range field type.
   *
   * @param array $data
   *   The array with start and end date.
   *
   * @return \Drupal\datetime_range\Plugin\Field\FieldType\DateRangeItem
   *   DateRangeItem object.
   */
  protected function getDateRangeItemInstance(array $data): DateRangeItem {
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

    return $entity->{$field_name}->first();
  }

}
