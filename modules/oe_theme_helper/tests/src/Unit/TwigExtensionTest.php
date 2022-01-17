<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\Unit;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Template\Loader\StringLoader;
use Drupal\oe_theme_helper\TwigExtension\TwigExtension;
use Drupal\oe_theme_helper\EuropeanUnionLanguages;
use Drupal\Tests\UnitTestCase;
use Twig\Environment;
use Twig\Error\RuntimeError;

/**
 * Tests for the custom Twig filters and functions extension.
 *
 * @group oe_theme_helper
 *
 * @coversDefaultClass \Drupal\oe_theme_helper\TwigExtension\TwigExtension
 *
 * @group batch1
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

    // Instantiate the system under test.
    $this->extension = new TwigExtension($this->languageManager->reveal(), $this->renderer->reveal());

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
  public function testToEclIcon(string $icon_name, array $expected_icon_array, string $size = NULL) {
    $context = [
      'ecl_icon_path' => '/path/to/theme/resources/icons/',
      'ecl_icon_social_media_path' => '/path/to/theme/resources/social-media-icons/',
      'ecl_icon_flag_path' => '/path/to/theme/resources/flag-icons/',
    ];
    // We join the resulting array from to_ecl_icon() function so that we have
    // a visual representation of the array being returned by the function.
    if ($size === NULL) {
      $result = $this->twig->render("{{ to_ecl_icon('$icon_name')|join('|') }}", $context);
      $this->assertEquals(implode('|', array_filter($expected_icon_array)), $result);
    }
    else {
      $result = $this->twig->render("{{ to_ecl_icon('$icon_name', '$size')|join('|') }}", $context);
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
  public function toEclIconProvider(): array {
    return [
      [
        'right',
        [
          'name' => 'corner-arrow',
          'transform' => 'rotate-90',
          'path' => '/path/to/theme/resources/icons/',
          'size' => 'xs',
        ],
        'xs',
      ],
      [
        'instagram',
        [
          'name' => 'instagram',
          'path' => '/path/to/theme/resources/social-media-icons/',
          'size' => 'xs',
        ],
        'xs',
      ],
      [
        'instagram-color',
        [
          'name' => 'instagram-color',
          'path' => '/path/to/theme/resources/social-media-icons/',
          'size' => 'xs',
        ],
        'xs',
      ],
      [
        'spain',
        [
          'name' => 'spain',
          'path' => '/path/to/theme/resources/flag-icons/',
          'size' => 'xs',
        ],
        'xs',
      ],
      [
        'spain-square',
        [
          'name' => 'spain-square',
          'path' => '/path/to/theme/resources/flag-icons/',
          'size' => 'xs',
        ],
        'xs',
      ],
      [
        'close-dark',
        [
          'name' => 'close-filled',
          'path' => '/path/to/theme/resources/icons/',
          'size' => 'xl',
        ],
        'xl',
      ],
      [
        'not-supported-icon',
        [
          'name' => 'not-supported-icon',
          'path' => '/path/to/theme/resources/icons/',
          'size' => 'm',
        ],
        'm',
      ],
      [
        'no-size',
        [
          'name' => 'no-size',
          'path' => '/path/to/theme/resources/icons/',
        ],
        NULL,
      ],
      [
        'empty-size',
        [
          'name' => 'empty-size',
          'path' => '/path/to/theme/resources/icons/',
          'size' => '',
        ],
        '',
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

}
