<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\ValueObject;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\oe_theme\ValueObject\DateValueObject;
use Drupal\oe_theme\ValueObject\DateValueObjectInterface;

/**
 * Test date value object with datetime field type.
 */
class DateTimeTest extends DateTimeTestBase {

  /**
   * A field storage to use in this test class.
   *
   * @var \Drupal\field\Entity\FieldStorageConfig
   */
  protected $fieldStorage;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['datetime'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create a field with settings to validate.
    $this->fieldStorage = FieldStorageConfig::create([
      'field_name' => 'field_datetime',
      'type' => 'datetime',
      'entity_type' => 'entity_test',
      'settings' => ['datetime_type' => DateTimeItem::DATETIME_TYPE_DATETIME],
    ]);
    $this->fieldStorage->save();
    FieldConfig::create([
      'field_storage' => $this->fieldStorage,
      'bundle' => 'entity_test',
      'settings' => [
        'default_value' => 'blank',
      ],
    ])->save();
  }

  /**
   * {@inheritdoc}
   */
  protected function getDateValueObject(array $data): DateValueObjectInterface {
    if (!empty($data['end'])) {
      $this->markTestSkipped('Datetime field type do not support end date.');
    }

    $datetime = DateTimePlus::createFromTimestamp($data['start'], new \DateTimeZone($data['timezone']));

    $field_name = $this->fieldStorage->getName();
    // Create an entity.
    $entity = EntityTest::create([
      'name' => $this->randomString(),
      $field_name => [
        'value' => $datetime->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      ],
    ]);
    $entity->save();

    return DateValueObject::fromDateTimeItem($entity->{$field_name}->first());
  }

}
