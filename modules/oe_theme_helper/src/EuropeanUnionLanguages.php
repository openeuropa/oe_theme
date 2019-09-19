<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper;

/**
 * Helper class storing European Union languages information.
 */
class EuropeanUnionLanguages {

  /**
   * List of European Union languages.
   *
   * Each entry includes:
   *
   * - The language name in English
   * - The language name in its native form
   * - The internal language ID, used on URLs, asset names, etc.
   *
   * @var array
   */
  protected static $languages = [
    'bg' => ['Bulgarian', 'български', 'bg'],
    'cs' => ['Czech', 'čeština', 'cs'],
    'da' => ['Danish', 'dansk', 'da'],
    'de' => ['German', 'Deutsch', 'de'],
    'et' => ['Estonian', 'eesti', 'et'],
    'el' => ['Greek', 'ελληνικά', 'el'],
    'en' => ['English', 'English', 'en'],
    'es' => ['Spanish', 'español', 'es'],
    'fr' => ['French', 'français', 'fr'],
    'ga' => ['Irish', 'Gaeilge', 'ga'],
    'hr' => ['Croatian', 'hrvatski', 'hr'],
    'it' => ['Italian', 'italiano', 'it'],
    'lt' => ['Lithuanian', 'lietuvių', 'lt'],
    'lv' => ['Latvian', 'latviešu', 'lv'],
    'hu' => ['Hungarian', 'magyar', 'hu'],
    'mt' => ['Maltese', 'Malti', 'mt'],
    'nl' => ['Dutch', 'Nederlands', 'nl'],
    'pl' => ['Polish', 'polski', 'pl'],
    'pt-pt' => ['Portuguese', 'português', 'pt'],
    'ro' => ['Romanian', 'română', 'ro'],
    'sk' => ['Slovak', 'slovenčina', 'sk'],
    'sl' => ['Slovenian', 'slovenščina', 'sl'],
    'fi' => ['Finnish', 'suomi', 'fi'],
    'sv' => ['Swedish', 'svenska', 'sv'],
  ];

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
  public static function getLanguageList(): array {
    return self::$languages;
  }

  /**
   * Get a language ID given its code.
   *
   * @param string $language_code
   *   The language code as defined by the W3C language tags document.
   *
   * @return bool
   *   Whereas the given language exists.
   */
  public static function hasLanguage(string $language_code): bool {
    return isset(self::$languages[$language_code]);
  }

  /**
   * Get the language name in English given its W3C code.
   *
   * @param string $language_code
   *   The language code as defined by the W3C language tags document.
   *
   * @return string
   *   The language name in English if any, an empty string otherwise.
   */
  public static function getEnglishLanguageName(string $language_code): string {
    return self::$languages[$language_code][0] ?? '';
  }

  /**
   * Get the native language name given its W3C code.
   *
   * @param string $language_code
   *   The language code as defined by the W3C language tags document.
   *
   * @return string
   *   The native language name if any, an empty string otherwise.
   */
  public static function getNativeLanguageName(string $language_code): string {
    return self::$languages[$language_code][1] ?? '';
  }

  /**
   * Get the internal language code given its W3C code.
   *
   * Internal language codes may differ from the standard ones.
   *
   * @param string $language_code
   *   The language code as defined by the W3C language tags document.
   *
   * @return string
   *   The internal language code if any, an empty string otherwise.
   */
  public static function getInternalLanguageCode(string $language_code): string {
    return self::$languages[$language_code][2] ?? '';
  }

}
