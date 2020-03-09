<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\TwigExtension;

use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Template\Attribute;
use Drupal\oe_theme_helper\EuropeanUnionLanguages;

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
      new \Twig_SimpleFilter('to_internal_language_id', [$this, 'toInternalLanguageId']),
      new \Twig_SimpleFilter('to_file_icon', [$this, 'toFileIcon']),
      new \Twig_SimpleFilter('to_date_status', [$this, 'toDateStatus']),
      new \Twig_SimpleFilter('to_ecl_attributes', [$this, 'toEclAttributes']),
      new \Twig_SimpleFilter('to_icon', [$this, 'toIcon']),
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
    $predefined = EuropeanUnionLanguages::getLanguageList() + LanguageManager::getStandardLanguageList();
    if (!empty($predefined[$language_code][1])) {
      return $predefined[$language_code][1];
    }

    throw new \InvalidArgumentException('The language code ' . $language_code . ' does not exist.');
  }

  /**
   * Get an internal language ID given its code.
   *
   * @param string $language_code
   *   The language code as defined by the W3C language tags document.
   *
   * @return string
   *   The internal language ID, or the given language code if none found.
   */
  public function toInternalLanguageId($language_code): string {
    if (EuropeanUnionLanguages::hasLanguage($language_code)) {
      return EuropeanUnionLanguages::getInternalLanguageCode($language_code);
    }

    return $language_code;
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
   *
   * @deprecated use EuropeanUnionLanguages::getLanguageList() instead.
   */
  public static function getEuropeanUnionLanguageList(): array {
    return EuropeanUnionLanguages::getLanguageList();
  }

  /**
   * Get date variant class given its status.
   *
   * @param string $status
   *   File extension.
   *
   * @return string
   *   File icon class name.
   */
  public static function toDateStatus(string $status): string {
    $variant_mapping = [
      'default' => 'default',
      'ongoing' => 'ongoing',
      'cancelled' => 'canceled',
      'past' => 'past',
    ];

    return array_key_exists($status, $variant_mapping) ? $variant_mapping[$status] : $status;
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

  /**
   * Convert Drupal attribute arrays to ECL Twig compatible ones.
   *
   * ECL Twig expects an array of string attributes, keyed by a name/value pair.
   * We use Drupal's Attribute class to make sure that we always get a printable
   * string, regardless of what we get as input from accessor preprocesses.
   *
   * @param mixed $attributes
   *   Drupal attributes, what we initialize the Drupal's Attribute class with.
   *
   * @return array
   *   An ECL Twig compatible attributes list.
   */
  public function toEclAttributes($attributes): array {
    // Only deal with iterable data.
    if (!is_iterable($attributes)) {
      return [];
    }

    $ecl_attributes = [];
    $attributes = new Attribute($attributes);
    foreach ($attributes as $key => $value) {
      $ecl_attributes[] = [
        'name' => $key,
        'value' => (string) $value,
      ];
    }

    return $ecl_attributes;
  }

  /**
   * Convert icon names to the ECL supported names.
   *
   * @param string $icon
   *   The icon to be converted.
   *
   * @return string
   *   The converted icon name or digital if the icon name is not supported.
   */
  public function toIcon(string $icon): string {
    $icons = [
      'facebook' => 'branded--facebook',
      'instagram' => 'branded--instagram',
      'linkedin' => 'branded--linkedin',
      'pinterest' => 'branded--pinterest',
      'rss' => 'branded--rss',
      'skype' => 'branded--skype',
      'twitter' => 'branded--twitter',
      'youtube' => 'branded--youtube',
      'audio' => 'general--audio',
      'book' => 'general--book',
      'brochure' => 'general--brochure',
      'budget' => 'general--budget',
      'calendar' => 'general--calendar',
      'copy' => 'general--copy',
      'data' => 'general--data',
      'digital' => 'general--digital',
      'edit' => 'general--edit',
      'energy' => 'general--energy',
      'euro' => 'general--euro',
      'faq' => 'general--faq',
      'feedback' => 'general--feedback',
      'file' => 'general--file',
      'gear' => 'general--gear',
      'generic-lang' => 'general--generic-lang',
      'global' => 'general--global',
      'growth' => 'general--growth',
      'hamburger' => 'general--hamburger',
      'image' => 'general--image',
      'infographic' => 'general--infographic',
      'language' => 'general--language',
      'livestreaming' => 'general--livestreaming',
      'location' => 'general--location',
      'log-in' => 'general--log-in',
      'logged-in' => 'general--logged-in',
      'multiple-files' => 'general--multiple-files',
      'organigram' => 'general--organigram',
      'package' => 'general--package',
      'presentation' => 'general--presentation',
      'print' => 'general--print',
      'regulation' => 'general--regulation',
      'search' => 'general--search',
      'share' => 'general--share',
      'spinner' => 'general--spinner',
      'spreadsheet' => 'general--spreadsheet',
      'video' => 'general--video',
      'error' => 'notifications--error',
      'information' => 'notifications--information',
      'success' => 'notifications--success',
      'warning' => 'notifications--warning',
      'check' => 'ui--check',
      'check-filled' => 'ui--check-filled',
      'close' => 'ui--close',
      'close-filled' => 'ui--close-filled',
      'corner-arrow' => 'ui--corner-arrow',
      'download' => 'ui--download',
      'external' => 'ui--external',
      'fullscreen' => 'ui--fullscreen',
      'minus' => 'ui--minus',
      'plus' => 'ui--plus',
      'rounded-arrow' => 'ui--rounded-arrow',
      'solid-arrow' => 'ui--solid-arrow',
    ];

    return $icons[$icon] ?? 'general--digital';
  }

}
