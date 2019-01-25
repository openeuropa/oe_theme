<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\oe_theme\ValueObject\DateValueObject;
use Drupal\Tests\datetime\Kernel\DateTimeItemTest;
use Drupal\Tests\oe_theme\Traits\RenderTrait;
use Symfony\Component\Yaml\Yaml;

/**
 * Test date value object with datetime field type.
 */
class DateTimeFieldTest extends DateTimeItemTest {

  use RenderTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'ui_patterns',
    'ui_patterns_library',
    'oe_theme_helper',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->container->get('theme_installer')->install(['oe_theme']);
    $this->container->get('theme_handler')->setDefault('oe_theme');
    $this->container->set('theme.registry', NULL);
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
  public function testFromDateTimeItem(array $data, array $expected): void {
    $date_range_item = $this->getDateTimeItemInstance($data);
    $date = DateValueObject::fromDateTimeItem($date_range_item);

    $this->assertEquals($expected['day'], $date->getDay());
    $this->assertEquals($expected['week_day'], $date->getWeekDay());
    $this->assertEquals($expected['month'], $date->getMonth());
    $this->assertEquals($expected['month_name'], $date->getMonthName());
    $this->assertEquals($expected['year'], $date->getYear());
  }

  /**
   * Test date block pattern rendering, built from an DateTimeItem object.
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
  public function testFromDateTimeField(string $variant, array $date, array $assertions) {
    $pattern = [
      '#type' => 'pattern',
      '#id' => 'date_block',
      '#variant' => $variant,
      '#fields' => [
        'date' => DateValueObject::fromDateTimeItem($this->getDateTimeItemInstance($date)),
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
  private function getDateTimeItemInstance(array $data): DateTimeItem {
    if (!empty($data['end'])) {
      $this->markTestSkipped('Datetime field type do not support end date.');
    }
    $this->fieldStorage->setSetting('datetime_type', DateTimeItem::DATETIME_TYPE_DATETIME);
    $this->fieldStorage->save();

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

    return $entity->{$field_name}->first();
  }

  /**
   * Data provider for rendering test.
   *
   * @return array
   *   An array of test data arrays with assertions.
   */
  public function renderingDataProvider(): array {
    return Yaml::parse(file_get_contents(__DIR__ . '/fixtures/patterns/date_block_pattern_rendering.yml'));
  }

  /**
   * Get fixture content.
   *
   * @return array
   *   A set of test data.
   */
  public function dataProviderForFactory(): ?array {
    return Yaml::parse(file_get_contents(__DIR__ . '/../Unit/fixtures/value_object/date_value_object.yml'));
  }

}
