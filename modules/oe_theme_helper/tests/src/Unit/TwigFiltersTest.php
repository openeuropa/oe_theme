<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\Unit;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Template\Loader\StringLoader;
use Drupal\oe_theme_helper\TwigExtension\Filters;
use Drupal\Tests\UnitTestCase;

/**
 * Tests for the custom Twig filters extension.
 *
 * @group oe_theme_helper
 * @coversDefaultClass \Drupal\oe_theme_helper\TwigExtension\Filters
 */
class TwigFiltersTest extends UnitTestCase {

  /**
   * The mocked language manager.
   *
   * @var \Prophecy\Prophecy\ProphecyInterface|\Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The Twig extension being tested.
   *
   * @var \Drupal\oe_theme_helper\TwigExtension\Filters
   */
  protected $extension;

  /**
   * The Twig environment containing the extension being tested.
   *
   * @var \Twig_Environment
   */
  protected $twig;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // It is expected that some filters will request the list of languages. In
    // scope of the OpenEuropa platform, it is expected that the names that are
    // returned conform to the official list of EU languages. This functionality
    // is provided in the OpenEuropa Multilingual module but is also returned in
    // mocked form here.
    $this->languageManager = $this->prophesize(LanguageManagerInterface::class);
    $native_languages = [];
    foreach ($this->getEuropeanUnionLanguageList() as $language_code => $language_names) {
      list($language_name, $native_name) = $language_names;
      $this->languageManager->getLanguageName($language_code)->willReturn($language_name);
      $native_language = $this->prophesize(LanguageInterface::class);
      $native_language->getName()->willReturn($native_name);
      $native_languages[$language_code] = $native_language->reveal();
    }
    $this->languageManager->getNativeLanguages()->willReturn($native_languages);

    // Instantiate the system under test.
    $this->extension = new Filters($this->languageManager->reveal());

    // For convenience, make a version of the Twig environment available that
    // has the tested extension preloaded.
    $loader = new StringLoader();
    $this->twig = new \Twig_Environment($loader);
    $this->twig->addExtension($this->extension);
  }

  /**
   * Tests converting a language code to the language name.
   *
   * @param string $language_code
   *   The language code to filter.
   * @param string $expected_language_name
   *   The language name that is expected to be returned.
   *
   * @covers ::toLanguageName
   * @dataProvider toLanguageNameProvider
   */
  public function testToLanguageName(string $language_code, string $expected_language_name): void {
    $result = $this->twig->render("{{ '$language_code'|to_language }}");
    $this->assertEquals($expected_language_name, $result);
  }

  /**
   * Returns test cases for ::testToLanguageName().
   *
   * @return array[]
   *   An array of test cases, each test case an indexed array with the
   *   following two values:
   *   1. The language code to check.
   *   2. The expected language name.
   *
   * @see ::testToLanguageName()
   */
  public function toLanguageNameProvider(): array {
    return [
      ['bg', 'Bulgarian'],
      ['cs', 'Czech'],
      ['da', 'Danish'],
      ['de', 'German'],
      ['et', 'Estonian'],
      ['el', 'Greek'],
      ['en', 'English'],
      ['es', 'Spanish'],
      ['fr', 'French'],
      ['ga', 'Irish'],
      ['hr', 'Croatian'],
      ['it', 'Italian'],
      ['lt', 'Lithuanian'],
      ['lv', 'Latvian'],
      ['hu', 'Hungarian'],
      ['mt', 'Maltese'],
      ['nl', 'Dutch'],
      ['pl', 'Polish'],
      ['pt-pt', 'Portuguese'],
      ['ro', 'Romanian'],
      ['sk', 'Slovak'],
      ['sl', 'Slovenian'],
      ['fi', 'Finnish'],
      ['sv', 'Swedish'],
    ];
  }

  /**
   * Tests converting a language code to the native language name.
   *
   * @param string $language_code
   *   The language code to filter.
   * @param string $expected_native_language_name
   *   The native language name that is expected to be returned.
   *
   * @covers ::toNativeLanguageName
   * @dataProvider toNativeLanguageNameProvider
   */
  public function testToNativeLanguageName(string $language_code, string $expected_native_language_name): void {
    $result = $this->twig->render("{{ '$language_code'|to_native_language }}");
    $this->assertEquals($expected_native_language_name, $result);
  }

  /**
   * Returns test cases for ::testToNativeLanguageName().
   *
   * @return array[]
   *   An array of test cases, each test case an indexed array with the
   *   following two values:
   *   1. The language code to check.
   *   2. The expected native language name.
   *
   * @see ::testToNativeLanguageName()
   */
  public function toNativeLanguageNameProvider(): array {
    return [
      ['bg', 'български'],
      ['cs', 'čeština'],
      ['da', 'dansk'],
      ['de', 'Deutsch'],
      ['et', 'eesti'],
      ['el', 'ελληνικά'],
      ['en', 'English'],
      ['es', 'español'],
      ['fr', 'français'],
      ['ga', 'Gaeilge'],
      ['hr', 'hrvatski'],
      ['it', 'italiano'],
      ['lt', 'lietuvių'],
      ['lv', 'latviešu'],
      ['hu', 'magyar'],
      ['mt', 'Malti'],
      ['nl', 'Nederlands'],
      ['pl', 'polski'],
      ['pt-pt', 'português'],
      ['ro', 'română'],
      ['sk', 'slovenčina'],
      ['sl', 'slovenščina'],
      ['fi', 'suomi'],
      ['sv', 'svenska'],
    ];
  }

  /**
   * Tests invalid language codes when converting to the native language name.
   *
   * @param mixed $invalid_language_code
   *   An invalid language code to pass to the function.
   *
   * @covers ::toNativeLanguageName
   * @dataProvider invalidLanguageCodesProvider
   */
  public function testPassingInvalidLanguageCodesToNativeLanguageName($invalid_language_code): void {
    $this->expectException(\InvalidArgumentException::class);

    try {
      $this->twig->render("{{ '$invalid_language_code'|to_native_language }}");
      $this->fail('The expected exception was not thrown.');
    }
    catch (\Twig_Error_Runtime $e) {
      // Twig wraps any exception that occurs during rendering with its own
      // runtime exception. Rethrow the original exception so we can verify that
      // the correct one is being thrown.
      throw $e->getPrevious();
    }
  }

  /**
   * Returns invalid language codes to use as test cases.
   *
   * @return array[]
   *   An array of test cases, each test case an indexed array with a single
   *   value consisting of an invalid language code.
   *
   * @see ::testPassingInvalidLanguageCodesToNativeLanguageName()
   */
  public function invalidLanguageCodesProvider(): array {
    return [
      [NULL],
      [TRUE],
      [FALSE],
      [''],
      ['qq'],
      [-1e10],
      ['≈ç√∫˜µ≤≥'],
      [0],
      ['😍'],
      ['1;DROP TABLE users'],
    ];
  }

  /**
   * Returns a list of language data.
   *
   * This is the data that is expected to be returned by the overridden language
   * manager as supplied by the OpenEuropa Multilingual module.
   *
   * @return array
   *   An array with language codes as keys, and English and native language
   *   names as values.
   */
  protected static function getEuropeanUnionLanguageList(): array {
    return Filters::getEuropeanUnionLanguageList();
  }

}
