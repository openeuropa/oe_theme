<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\Kernel\ValueObject;

use Drupal\Tests\field\Kernel\FieldKernelTestBase;
use Drupal\Tests\oe_theme\Traits\RenderTrait;
use Drupal\oe_theme\ValueObject\DateValueObjectInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Base class for "date type" field kernel tests.
 */
abstract class DateTimeTestBase extends FieldKernelTestBase {

  use RenderTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'ui_patterns',
    'ui_patterns_library',
    'oe_theme_helper',
    'image',
    'breakpoint',
    'responsive_image',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->container->get('theme_installer')->install(['oe_theme']);
    $this->config('system.theme')->set('default', 'oe_theme')->save();
    $this->container->set('theme.registry', NULL);
    $this->installConfig(['image', 'responsive_image']);
  }

  /**
   * Test constructing a date value object from an DateRangeItem/DateTimeItem.
   *
   * @param array $data
   *   The array with start and end date.
   * @param array $expected
   *   The array with expected values.
   *
   * @dataProvider dataProviderForFactory
   */
  public function testFromDateTimeObject(array $data, array $expected): void {
    $date = $this->getDateValueObject($data);

    $this->assertEquals($expected['day'], $date->getDay());
    $this->assertEquals($expected['week_day'], $date->getWeekDay());
    $this->assertEquals($expected['month'], $date->getMonth());
    $this->assertEquals($expected['month_name'], $date->getMonthName());
    $this->assertEquals($expected['month_fullname'], $date->getMonthFullName());
    $this->assertEquals($expected['year'], $date->getYear());
  }

  /**
   * Get fixture content.
   *
   * @return array
   *   Data provider for factory methods test.
   */
  public function dataProviderForFactory(): array {
    return Yaml::parse(file_get_contents(__DIR__ . '/../../Unit/fixtures/value_object/date_value_object.yml'));
  }

  /**
   * Get the DateValueObject from context.
   *
   * @param array $date
   *   The array with start and end date.
   *
   * @return \Drupal\oe_theme\ValueObject\DateValueObjectInterface
   *   Data provider for factory methods test.
   */
  abstract protected function getDateValueObject(array $date): DateValueObjectInterface;

}
