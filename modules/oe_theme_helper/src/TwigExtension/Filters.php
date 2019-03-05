<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\TwigExtension;

use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Collection of extra Twig filters.
 *
 * We don't enforce any strict type checking on filters' arguments as they are
 * coming straight from Twig templates.
 */
class Filters extends \Twig_Extension {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a new Filters object.
   */
  public function __construct(LanguageManagerInterface $languageManager) {
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getFilters(): array {
    return [
      new \Twig_SimpleFilter('format_size', 'format_size'),
      new \Twig_SimpleFilter('to_language', [$this, 'toLanguageName']),
      new \Twig_SimpleFilter('to_native_language', [$this, 'toNativeLanguageName']),
      new \Twig_SimpleFilter('to_file_icon', [$this, 'toFileIcon']),
    ];
  }

  /**
   * Get a translated language name given its code.
   *
   * @param mixed $language_code
   *   Two letters language code.
   *
   * @return string
   *   Language name.
   */
  public function toLanguageName($language_code): string {
    return (string) $this->languageManager->getLanguageName($language_code);
  }

  /**
   * Get a native language name given its code.
   *
   * @param string $language_code
   *   The language code as defined by the W3C language tags document.
   *
   * @return string
   *   The native language name.
   *
   * @throws \InvalidArgumentException
   *   Thrown when the passed in language code does not exist.
   */
  public function toNativeLanguageName($language_code): string {
    $languages = $this->languageManager->getNativeLanguages();
    if (!empty($languages[$language_code])) {
      return $languages[$language_code]->getName();
    }
    // The fallback implemented in case we don't have enabled language.
    $predefined = self::getEuropeanUnionLanguageList() + LanguageManager::getStandardLanguageList();
    if (!empty($predefined[$language_code][1])) {
      return $predefined[$language_code][1];
    }

    throw new \InvalidArgumentException('The language code ' . $language_code . ' does not exist.');
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
  public static function getEuropeanUnionLanguageList(): array {
    return [
      'bg' => ['Bulgarian', 'български'],
      'cs' => ['Czech', 'čeština'],
      'da' => ['Danish', 'dansk'],
      'de' => ['German', 'Deutsch'],
      'et' => ['Estonian', 'eesti'],
      'el' => ['Greek', 'ελληνικά'],
      'en' => ['English', 'English'],
      'es' => ['Spanish', 'español'],
      'fr' => ['French', 'français'],
      'ga' => ['Irish', 'Gaeilge'],
      'hr' => ['Croatian', 'hrvatski'],
      'it' => ['Italian', 'italiano'],
      'lt' => ['Lithuanian', 'lietuvių'],
      'lv' => ['Latvian', 'latviešu'],
      'hu' => ['Hungarian', 'magyar'],
      'mt' => ['Maltese', 'Malti'],
      'nl' => ['Dutch', 'Nederlands'],
      'pl' => ['Polish', 'polski'],
      'pt-pt' => ['Portuguese', 'português'],
      'ro' => ['Romanian', 'română'],
      'sk' => ['Slovak', 'slovenčina'],
      'sl' => ['Slovenian', 'slovenščina'],
      'fi' => ['Finnish', 'suomi'],
      'sv' => ['Swedish', 'svenska'],
    ];
  }

  /**
   * Get file icon class given its extension.
   *
   * @param string $extension
   *   File extension.
   *
   * @return string
   *   File icon class name.
   */
  public function toFileIcon(string $extension): string {
    $extension = strtolower($extension);
    $extension_mapping = [
      'image' => [
        'jpg',
        'jpeg',
        'gif',
        'png',
        'webp',
      ],
      'presentation' => [
        'ppt',
        'pptx',
        'pps',
        'ppsx',
        'odp',
      ],
      'spreadsheet' => [
        'xls',
        'xlsx',
        'ods',
      ],
      'video' => [
        'mp4',
        'mov',
        'mpeg',
        'avi',
        'm4v',
        'webm',
      ],
    ];

    foreach ($extension_mapping as $file_type => $extensions) {
      if (in_array($extension, $extensions)) {
        return $file_type;
      }
    }

    return 'file';
  }

}
