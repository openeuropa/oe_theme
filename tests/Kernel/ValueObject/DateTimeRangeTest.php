<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\ValueObject;

use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\datetime_range\Plugin\Field\FieldType\DateRangeItem;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\oe_theme\ValueObject\DateValueObject;
use Drupal\oe_theme\ValueObject\DateValueObjectInterface;

/**
 * Test date value object with datetime_range field type.
 */
class DateTimeRangeTest extends DateTimeTestBase {

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
   * {@inheritdoc}
   */
  protected function getDateValueObject(array $data): DateValueObjectInterface {
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

    return DateValueObject::fromDateRangeItem($entity->{$field_name}->first());
  }

}
