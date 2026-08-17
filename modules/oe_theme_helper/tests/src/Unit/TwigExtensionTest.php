<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme_helper\Unit;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Template\Loader\StringLoader;
use Drupal\oe_theme_webtools_mock\WebtoolsIconsMockDecorator;
use Drupal\Tests\UnitTestCase;
use Drupal\oe_theme_helper\EuropeanUnionLanguages;
use Drupal\oe_theme_helper\ExternalLinksInterface;
use Drupal\oe_theme_helper\TwigExtension\TwigExtension;
use Twig\Environment;
use Twig\Error\RuntimeError;

/**
 * Tests for the custom Twig filters and functions extension.
 *
 * @group oe_theme_helper
 *
 * @coversDefaultClass \Drupal\oe_theme_helper\TwigExtension\TwigExtension
 *
 * @group batch9
 */
class TwigExtensionTest extends UnitTestCase {

  /**
   * The mocked language manager.
   *
   * @var \Prophecy\Prophecy\ProphecyInterface|\Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The mocked renderer.
   *
   * @var \Prophecy\Prophecy\ProphecyInterface|\Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The Twig extension being tested.
   *
   * @var \Drupal\oe_theme_helper\TwigExtension\TwigExtension
   */
  protected $extension;

  /**
   * The Twig environment containing the extension being tested.
   *
   * @var \Twig_Environment
   */
  protected $twig;

  /**
   * The external links service.
   *
   * @var \Drupal\oe_theme_helper\ExternalLinksInterface
   */
  protected $externalLinks;

  /**
   * The webtools icons provider.
   *
   * @var \Prophecy\Prophecy\ProphecyInterface|\Drupal\oe_theme_helper\WebtoolsIconsProvider
   */
  protected $webtoolsIconsProvider;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // It is expected that some filters will request the list of languages. In
    // scope of the OpenEuropa platform, it is expected that the names that are
    // returned conform to the official list of EU languages. This functionality
    // is provided in the OpenEuropa Multilingual module but is also returned in
    // mocked form here.
    $this->languageManager = $this->prophesize(LanguageManagerInterface::class);
    $native_languages = [];
    foreach ($this->getEuropeanUnionLanguageList() as $language_code => $language_names) {
      [$language_name, $native_name] = $language_names;
      $this->languageManager->getLanguageName($language_code)->willReturn($language_name);
      $native_language = $this->prophesize(LanguageInterface::class);
      $native_language->getName()->willReturn($native_name);
      $native_languages[$language_code] = $native_language->reveal();
    }
    $this->languageManager->getNativeLanguages()->willReturn($native_languages);

    // Create Renderer service mock.
    $this->renderer = $this->prophesize(Renderer::class);

    // Create the external link service mock.
    $this->externalLinks = $this->prophesize(ExternalLinksInterface::class);

    // Create the webtools icons provider mock.
    $this->webtoolsIconsProvider = $this->prophesize(WebtoolsIconsMockDecorator::class);

    // Instantiate the system under test.
    $this->extension = new TwigExtension($this->languageManager->reveal(), $this->renderer->reveal(), $this->externalLinks->reveal(), $this->webtoolsIconsProvider->reveal());

    // For convenience, make a version of the Twig environment available that
    // has the tested extension preloaded.
    $loader = new StringLoader();
    $this->twig = new Environment($loader);
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
  public static function toLanguageNameProvider(): array {
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
  public static function toNativeLanguageNameProvider(): array {
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
    catch (RuntimeError $e) {
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
  public static function invalidLanguageCodesProvider(): array {
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
    return EuropeanUnionLanguages::getLanguageList();
  }

  /**
   * Tests converting an icon name to the ECL supported icons.
   *
   * @param string $icon_name
   *   The icon name.
   * @param array $expected_icon_array
   *   The icon array to be rendered.
   * @param string|null $size
   *   The icon size.
   *
   * @covers ::toEclIcon
   * @dataProvider toEclIconProvider
   */
  public function testToEclIcon(string $icon_name, array $expected_icon_array, ?string $size = NULL) {
    // We join the resulting array from to_ecl_icon() function so that we have
    // a visual representation of the array being returned by the function.
    if ($size === NULL) {
      $result = $this->twig->render("{{ to_ecl_icon('$icon_name')|join('|') }}");
      $this->assertEquals(implode('|', array_filter($expected_icon_array)), $result);
    }
    else {
      $result = $this->twig->render("{{ to_ecl_icon('$icon_name', '$size')|join('|') }}");
      $this->assertEquals(implode('|', array_filter($expected_icon_array)), $result);
    }
  }

  /**
   * Returns test cases for ::testToEclIcon().
   *
   * @return array[]
   *   An icon array.
   *
   * @see ::testToEclIcon()
   */
  public static function toEclIconProvider(): array {
    return [
      [
        'right',
        [
          'name' => 'corner-arrow',
          'transform' => 'rotate-90',
          'size' => 'xs',
        ],
        'xs',
      ],
      [
        'instagram',
        [
          'name' => 'instagram',
          'size' => 'xs',
        ],
        'xs',
      ],
      [
        'instagram-color',
        [
          'name' => 'instagram-color',
          'size' => 'xs',
        ],
        'xs',
      ],
      [
        'es',
        [
          'name' => 'es',
          'size' => 'xs',
        ],
        'xs',
      ],
      [
        'close-filled',
        [
          'name' => 'close-filled',
          'size' => 'xl',
        ],
        'xl',
      ],
      [
        'not-supported-icon',
        [
          'name' => 'not-supported-icon',
          'size' => 'm',
        ],
        'm',
      ],
      [
        'no-size',
        [
          'name' => 'no-size',
        ],
        NULL,
      ],
      [
        'empty-size',
        [
          'name' => 'empty-size',
          'size' => '',
        ],
        '',
      ],
    ];
  }

  /**
   * Tests the ECL border color class is properly.
   *
   * @param string $component_library
   *   The current component library.
   * @param string $expected_class
   *   The expected border color class.
   *
   * @covers ::eclBorderColor
   * @dataProvider eclBorderColorProvider
   */
  public function testEclBorderColor(string $component_library, string $expected_class) {
    $context = [
      'ecl_component_library' => $component_library,
    ];
    $result = $this->twig->render("{{ ecl_class_border_color() }}", $context);
    $this->assertEquals($expected_class, $result);
  }

  /**
   * Returns test cases for ::testEclBorderColor().
   *
   * @return array[]
   *   Test cases array.
   *
   * @see ::testEclBorderColor()
   */
  public static function eclBorderColorProvider(): array {
    return [
      [
        'ec',
        'ecl-u-border-color-neutral-50',
      ],
      [
        'eu',
        'ecl-u-border-color-primary-10',
      ],
    ];
  }

  /**
   * Tests the ECL background color class is properly.
   *
   * @param string $component_library
   *   The current component library.
   * @param string $expected_class
   *   The expected background color class.
   *
   * @covers ::eclBackgroundColor
   * @dataProvider eclBackgroundColorProvider
   */
  public function testEclBackgroundColor(string $component_library, string $expected_class) {
    $context = [
      'ecl_component_library' => $component_library,
    ];
    $result = $this->twig->render("{{ ecl_class_background_color() }}", $context);
    $this->assertEquals($expected_class, $result);
  }

  /**
   * Returns test cases for ::testEclBackgroundColor().
   *
   * @return array[]
   *   Test cases array.
   *
   * @see ::testEclBackgroundColor()
   */
  public static function eclBackgroundColorProvider(): array {
    return [
      [
        'ec',
        'ecl-u-bg-neutral-light-50',
      ],
      [
        'eu',
        'ecl-u-bg-primary-5',
      ],
    ];
  }

  /**
   * Test that create_markup filter returns MarkupInterface object.
   */
  public function testCreateMarkup() {
    $markup_object = $this->extension->createMarkup('Some string');
    $this->assertInstanceOf(MarkupInterface::class, $markup_object);
  }

  /**
   * Tests converting a language code to the internal language code.
   *
   * @param string $language_code
   *   The language code to filter.
   * @param string $expected_internal_language_code
   *   The internal language code that is expected to be returned.
   *
   * @covers ::toInternalLanguageId
   * @dataProvider toInternalLanguageIdProvider
   */
  public function testToInternalLanguageId(string $language_code, string $expected_internal_language_code): void {
    $result = $this->twig->render("{{ '$language_code'|to_internal_language_id }}");
    $this->assertEquals($expected_internal_language_code, $result);
  }

  /**
   * Returns test cases for ::testToInternalLanguageId().
   *
   * @return array[]
   *   An array of test cases, each test case an indexed array with the
   *   following two values:
   *   1. The language code to check.
   *   2. The expected internal language code.
   *
   * @see ::testToInternalLanguageId()
   */
  public static function toInternalLanguageIdProvider(): array {
    return [
      ['bg', 'bg'],
      ['cs', 'cs'],
      ['da', 'da'],
      ['de', 'de'],
      ['et', 'et'],
      ['el', 'el'],
      ['en', 'en'],
      ['es', 'es'],
      ['fr', 'fr'],
      ['ga', 'ga'],
      ['hr', 'hr'],
      ['it', 'it'],
      ['lt', 'lt'],
      ['lv', 'lv'],
      ['hu', 'hu'],
      ['mt', 'mt'],
      ['nl', 'nl'],
      ['pl', 'pl'],
      ['pt-pt', 'pt'],
      ['ro', 'ro'],
      ['sk', 'sk'],
      ['sl', 'sl'],
      ['fi', 'fi'],
      ['sv', 'sv'],
      ['nb', 'no'],
      ['zh-hans', 'zh'],
    ];
  }

}
