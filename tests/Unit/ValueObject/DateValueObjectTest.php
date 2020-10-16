<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Unit\Patterns;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\StringTranslation\TranslatableMarkup;
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
    // Mock the string translation service.
    $language = $this->createMock('\Drupal\Core\Language\LanguageInterface');
    $language->expects($this->any())
      ->method('getId')
      ->willReturn('en');
    $language_manager = $this->createMock('\Drupal\Core\Language\LanguageManagerInterface');
    $language_manager->expects($this->any())
      ->method('getCurrentLanguage')
      ->willReturn($language);

    // If there is an end date configured, we expect to have double the calls.
    $expected_calls = $data['end'] ? 6 : 3;
    $translation = $this->createMock('Drupal\Core\StringTranslation\TranslationInterface');
    $translation->expects($this->exactly($expected_calls))
      ->method('translateString')
      ->willReturnCallback(function (TranslatableMarkup $wrapper) {
        return $wrapper->getUntranslatedString();
      });
    $container = new ContainerBuilder();
    \Drupal::setContainer($container);
    $container->set('string_translation', $translation);
    $container->set('language_manager', $language_manager);

    /** @var \Drupal\oe_theme\ValueObject\DateValueObject $date */
    $date = DateValueObject::fromArray($data);
    $this->assertEquals($expected['day'], $date->getDay());
    $this->assertEquals($expected['week_day'], $date->getWeekDay());
    $this->assertEquals($expected['month'], $date->getMonth());
    $this->assertEquals($expected['month_name'], $date->getMonthName());
    $this->assertEquals($expected['month_fullname'], $date->getMonthfullName());
    $this->assertEquals($expected['year'], $date->getYear());

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
