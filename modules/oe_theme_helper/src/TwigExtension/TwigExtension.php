<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\TwigExtension;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Template\Attribute;
use Drupal\oe_theme_helper\EuropeanUnionLanguages;
use Drupal\smart_trim\Truncate\TruncateHTML;

/**
 * Collection of extra Twig extensions as filters and functions.
 *
 * We don't enforce any strict type checking on filters' arguments as they are
 * coming straight from Twig templates.
 */
class TwigExtension extends \Twig_Extension {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a new TwigExtension object.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
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
      new \Twig_SimpleFilter('smart_trim', [$this, 'smartTrim']),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions(): array {
    return [
      new \Twig_SimpleFunction('to_ecl_icon', [$this, 'toEclIcon'], ['needs_context' => TRUE]),
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
   * Convert icon names to the ECL supported names and apply rotation if needed.
   *
   * @param array $context
   *   The twig context.
   * @param string|null $icon
   *   The icon to be converted.
   *
   * @return array
   *   Icon array for ECL components containing icon name, path and rotation.
   */
  public function toEclIcon(array $context, $icon): array {
    $path = $context['ecl_icon_path'];

    // ECL supported icons naming and rotation.
    $icons = [
      'facebook' => [
        'name' => 'branded--facebook',
      ],
      'instagram' => [
        'name' => 'branded--instagram',
      ],
      'linkedin' => [
        'name' => 'branded--linkedin',
      ],
      'pinterest' => [
        'name' => 'branded--pinterest',
      ],
      'rss' => [
        'name' => 'branded--rss',
      ],
      'skype' => [
        'name' => 'branded--skype',
      ],
      'twitter' => [
        'name' => 'branded--twitter',
      ],
      'youtube' => [
        'name' => 'branded--youtube',
      ],
      'audio' => [
        'name' => 'general--audio',
      ],
      'book' => [
        'name' => 'general--book',
      ],
      'brochure' => [
        'name' => 'general--brochure',
      ],
      'budget' => [
        'name' => 'general--budget',
      ],
      'calendar' => [
        'name' => 'general--calendar',
      ],
      'copy' => [
        'name' => 'general--copy',
      ],
      'data' => [
        'name' => 'general--data',
      ],
      'digital' => [
        'name' => 'general--digital',
      ],
      'edit' => [
        'name' => 'general--edit',
      ],
      'energy' => [
        'name' => 'general--energy',
      ],
      'euro' => [
        'name' => 'general--euro',
      ],
      'faq' => [
        'name' => 'general--faq',
      ],
      'feedback' => [
        'name' => 'general--feedback',
      ],
      'file' => [
        'name' => 'general--file',
      ],
      'gear' => [
        'name' => 'general--gear',
      ],
      'generic-lang' => [
        'name' => 'general--generic-lang',
      ],
      'global' => [
        'name' => 'general--global',
      ],
      'googleplus' => [
        'name' => 'general--digital',
      ],
      'growth' => [
        'name' => 'general--growth',
      ],
      'hamburger' => [
        'name' => 'general--hamburger',
      ],
      'image' => [
        'name' => 'general--image',
      ],
      'infographic' => [
        'name' => 'general--infographic',
      ],
      'language' => [
        'name' => 'general--language',
      ],
      'livestreaming' => [
        'name' => 'general--livestreaming',
      ],
      'location' => [
        'name' => 'general--location',
      ],
      'log-in' => [
        'name' => 'general--log-in',
      ],
      'logged-in' => [
        'name' => 'general--logged-in',
      ],
      'multiple-files' => [
        'name' => 'general--multiple-files',
      ],
      'organigram' => [
        'name' => 'general--organigram',
      ],
      'package' => [
        'name' => 'general--package',
      ],
      'presentation' => [
        'name' => 'general--presentation',
      ],
      'print' => [
        'name' => 'general--print',
      ],
      'regulation' => [
        'name' => 'general--regulation',
      ],
      'search' => [
        'name' => 'general--search',
      ],
      'share' => [
        'name' => 'general--share',
      ],
      'slides' => [
        'name' => 'general--presentation',
      ],
      'spinner' => [
        'name' => 'general--spinner',
      ],
      'spreadsheet' => [
        'name' => 'general--spreadsheet',
      ],
      'video' => [
        'name' => 'general--video',
      ],
      'camera' => [
        'name' => 'general--video',
      ],
      'error' => [
        'name' => 'notifications--error',
      ],
      'information' => [
        'name' => 'notifications--information',
      ],
      'info' => [
        'name' => 'notifications--information',
      ],
      'success' => [
        'name' => 'notifications--success',
      ],
      'warning' => [
        'name' => 'notifications--warning',
      ],
      'check' => [
        'name' => 'ui--check',
      ],
      'check-filled' => [
        'name' => 'ui--check-filled',
      ],
      'close' => [
        'name' => 'ui--close',
      ],
      'close-filled' => [
        'name' => 'ui--close-filled',
      ],
      'corner-arrow' => [
        'name' => 'ui--corner-arrow',
      ],
      'download' => [
        'name' => 'ui--download',
      ],
      'external' => [
        'name' => 'ui--external',
      ],
      'fullscreen' => [
        'name' => 'ui--fullscreen',
      ],
      'minus' => [
        'name' => 'ui--minus',
      ],
      'plus' => [
        'name' => 'ui--plus',
      ],
      'rounded-arrow' => [
        'name' => 'ui--rounded-arrow',
      ],
      'solid-arrow' => [
        'name' => 'ui--solid-arrow',
      ],
      'close-dark' => [
        'name' => 'ui--close-filled',
      ],
      'in' => [
        'name' => 'ui--download',
      ],
      'tag-close' => [
        'name' => 'ui--close',
      ],
      'up' => [
        'name' => 'ui--rounded-arrow',
      ],
      'arrow-down' => [
        'name' => 'ui--solid-arrow',
        'transform' => 'rotate-180',
      ],
      'arrow-up' => [
        'name' => 'ui--solid-arrow',
      ],
      'breadcrumb' => [
        'name' => 'ui--rounded-arrow',
        'transform' => 'rotate-90',
      ],
      'down' => [
        'name' => 'ui--rounded-arrow',
        'transform' => 'rotate-180',
      ],
      'left' => [
        'name' => 'ui--rounded-arrow',
        'transform' => 'rotate-270',
      ],
      'right' => [
        'name' => 'ui--rounded-arrow',
        'transform' => 'rotate-90',
      ],
    ];

    if (array_key_exists($icon, $icons)) {
      $icons[$icon]['path'] = $path;
      return $icons[$icon];
    }

    return [
      'name' => 'general--digital',
      'path' => $path,
    ];
  }

  /**
   * Trims the contents of a text field.
   *
   * The method expects to receive a string (already rendered).
   * Inside a template we can use '|render' for passing to filter
   * only string like in example: {{ long_text|render|smart_trim(10) }}.
   *
   * @param mixed $text
   *   The text to be trimmed.
   * @param int $limit
   *   Amount of text to allow.
   *
   * @return string|array
   *   The trimmed text.
   */
  public function smartTrim($text, $limit = NULL) {
    // Return original text if length is not available.
    if ($limit === NULL) {
      return $text;
    }

    $truncate = new TruncateHTML();

    if (is_string($text) || $text instanceof MarkupInterface) {
      // If $text is a string or a Markup object, trim
      // and return the Markup object.
      return Markup::create($truncate->truncateChars($text, $limit));
    }

    // Return the unchanged $text since we don't support other scenarios.
    return $text;
  }

}
