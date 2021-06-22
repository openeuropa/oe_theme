<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\locale\SourceString;

/**
 * Base class for multilingual tests.
 */
abstract class MultilingualAbstractKernelTestBase extends AbstractKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'content_translation',
    'locale',
    'language',
    'oe_multilingual',
    'oe_multilingual_demo',
    'system',
    'user',
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
  protected function setUp() {
    parent::setUp();

    $this->markTestSkipped('Skip this test temporarily, as part of ECL v3 upgrade.');

    $this->installSchema('locale', [
      'locales_location',
      'locales_source',
      'locales_target',
    ]);

    $this->installConfig([
      'locale',
      'language',
      'content_translation',
      'oe_multilingual',
      'image',
      'responsive_image',
    ]);

    $this->container->get('module_handler')->loadInclude('oe_multilingual', 'install');
    oe_multilingual_install(FALSE);

    // Rebuild the container in order to make sure tests pass.
    // @todo: fix test setup so that we can get rid of this line.
    $this->container->get('kernel')->rebuildContainer();
  }

  /**
   * Translate a locale string.
   *
   * @param string $string
   *   The string to be translated.
   * @param string $translation
   *   The translation string.
   * @param string $langcode
   *   The target language code.
   */
  protected function translateLocaleString(string $string, string $translation, string $langcode): void {
    /** @var \Drupal\locale\StringDatabaseStorage $locale_storage */
    $locale_storage = $this->container->get('locale.storage');
    // Find the target string.
    $locale_string = $locale_storage->findString(['source' => $string]);

    // If the target string is not found, create it.
    if (!$locale_string) {
      $locale_string = new SourceString();
      $locale_string->setString($string);
      $locale_string->setStorage($locale_storage);
      $locale_string->save();
    }

    // Add the translation for the string.
    $locale_storage->createTranslation([
      'lid' => $locale_string->lid,
      'language' => $langcode,
      'translation' => $translation,
    ])->save();
  }

}
