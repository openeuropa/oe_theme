<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\TwigExtension;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Render\AttachmentsInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\Markup;
use Drupal\Core\Render\RenderableInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Template\Attribute;
use Drupal\oe_theme_helper\EuropeanUnionLanguages;
use Drupal\oe_theme_helper\ExternalLinksInterface;
use Drupal\Core\Template\TwigExtension as CoreTwigExtension;
use Drupal\smart_trim\TruncateHTML;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Collection of extra Twig extensions as filters and functions.
 *
 * We don't enforce any strict type checking on filters' arguments as they are
 * coming straight from Twig templates.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class TwigExtension extends AbstractExtension {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The external links service.
   *
   * @var \Drupal\oe_theme_helper\ExternalLinksInterface
   */
  protected $externalLinks;

  /**
   * Constructs a new TwigExtension object.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\oe_theme_helper\ExternalLinksInterface $external_links
   *   The external links service.
   */
  public function __construct(LanguageManagerInterface $languageManager, RendererInterface $renderer, ExternalLinksInterface $external_links) {
    $this->languageManager = $languageManager;
    $this->renderer = $renderer;
    $this->externalLinks = $external_links;
  }

  /**
   * {@inheritdoc}
   */
  public function getFilters(): array {
    return [
      new TwigFilter('format_size', 'format_size'),
      new TwigFilter('to_language', [$this, 'toLanguageName']),
      new TwigFilter('to_native_language', [$this, 'toNativeLanguageName']),
      new TwigFilter('to_internal_language_id', [$this, 'toInternalLanguageId']),
      new TwigFilter('to_file_icon', [$this, 'toFileIcon']),
      new TwigFilter('to_date_status', [$this, 'toDateStatus']),
      new TwigFilter('to_ecl_attributes', [$this, 'toEclAttributes']),
      new TwigFilter('smart_trim', [$this, 'smartTrim'], ['needs_environment' => TRUE]),
      new TwigFilter('is_external_url', [$this, 'isExternal']),
      new TwigFilter('filter_empty', [$this, 'filterEmpty']),
      new TwigFilter('create_markup', [$this, 'createMarkup']),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions(): array {
    return [
      new TwigFunction('to_ecl_icon', [$this, 'toEclIcon'], ['needs_context' => TRUE]),
      new TwigFunction('get_link_icon', [$this, 'getLinkIcon'], ['needs_context' => TRUE]),
      new TwigFunction('ecl_footer_links', [$this, 'eclFooterLinks'], ['needs_context' => TRUE]),
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
   * @param string $size
   *   The icon size.
   *
   * @return array
   *   Icon array for ECL components containing icon name, path and rotation.
   */
  public function toEclIcon(array $context, $icon, string $size = ''): array {
    $path = $this->getIconPath($context, $icon);

    // Icons that require transforming.
    $transformed_icons = [
      'googleplus' => [
        'name' => 'digital',
      ],
      'slides' => [
        'name' => 'presentation',
      ],
      'info' => [
        'name' => 'information',
      ],
      'close-dark' => [
        'name' => 'close-filled',
      ],
      'in' => [
        'name' => 'download',
      ],
      'tag-close' => [
        'name' => 'close',
      ],
      'up' => [
        'name' => 'corner-arrow',
      ],
      'arrow-down' => [
        'name' => 'solid-arrow',
        'transform' => 'rotate-180',
      ],
      'arrow-up' => [
        'name' => 'solid-arrow',
      ],
      'breadcrumb' => [
        'name' => 'corner-arrow',
        'transform' => 'rotate-90',
      ],
      'down' => [
        'name' => 'corner-arrow',
        'transform' => 'rotate-180',
      ],
      'left' => [
        'name' => 'corner-arrow',
        'transform' => 'rotate-270',
      ],
      'right' => [
        'name' => 'corner-arrow',
        'transform' => 'rotate-90',
      ],
    ];

    // Check whether the icon needs any transformation.
    if (array_key_exists($icon, $transformed_icons)) {
      $transformed_icons[$icon]['path'] = $path;
      if ($size) {
        $transformed_icons[$icon]['size'] = $size;
      }
      return $transformed_icons[$icon];
    }

    // We define a default icon if one is not provided.
    if (!$icon) {
      $icon = 'digital';
    }
    $icon = [
      'name' => $icon,
      'path' => $path,
    ];
    if ($size) {
      $icon['size'] = $size;

    }
    return $icon;
  }

  /**
   * Returns the file path for an ECL icon.
   *
   * @param array $context
   *   The twig context.
   * @param string $icon
   *   The icon to be converted.
   *
   * @return string
   *   ECL icon file path.
   */
  protected function getIconPath(array $context, string $icon): string {
    // Flag icon names.
    $flag_icons = [
      'austria',
      'belgium',
      'bulgaria',
      'croatia',
      'cyprus',
      'czech-republic',
      'denmark',
      'estonia',
      'EU',
      'finland',
      'france',
      'germany',
      'greece',
      'hungary',
      'ireland',
      'italy',
      'latvia',
      'lithuania',
      'luxembourg',
      'malta',
      'netherlands',
      'poland',
      'portugal',
      'romania',
      'slovakia',
      'slovenia',
      'spain',
      'sweden',
    ];
    // Flag icons can have a -square string appended, so check if the icon name
    // starts with a country name.
    $found_icon = array_filter($flag_icons, function ($var) use ($icon) {
      if (strpos($icon, $var) === 0) {
        return TRUE;
      };
      return FALSE;
    });
    if ($found_icon) {
      return $context['ecl_icon_flag_path'];
    }

    // Social media icon names.
    $social_icons = [
      'blog',
      'facebook',
      'flickr',
      'foursquare',
      'instagram',
      'linkedin',
      'pinterest',
      'reddit',
      'skype',
      'spotify',
      'twitter',
      'youtube',
    ];
    // Social icons can have a -color or a -negative string appended,
    // so check if the icon name starts with a social name.
    $found_icon = array_filter($social_icons, function ($var) use ($icon) {
      if (strpos($icon, $var) === 0) {
        return TRUE;
      };
      return FALSE;
    });
    if ($found_icon) {
      return $context['ecl_icon_social_media_path'];
    }
    return $context['ecl_icon_path'];
  }

  /**
   * Trim given input using smart_trim module heuristics.
   *
   * @param \Twig\Environment $env
   *   Current Twig environment.
   * @param mixed $input
   *   Input to be trimmed, it can be a string, an object or a render array.
   * @param int $limit
   *   Amount of text to allow.
   *
   * @return mixed
   *   The trimmed output.
   */
  public function smartTrim(Environment $env, $input, $limit = 0) {
    // Bubbles Twig template argument's cacheability & attachment metadata.
    $this->bubbleArgMetadata($input);
    $truncate = new TruncateHTML();

    // If input is a Markup object, trim it and return it as such.
    if ($input instanceof MarkupInterface) {
      return Markup::create($truncate->truncateChars((string) $input, $limit));
    }

    $output = $env->getExtension(CoreTwigExtension::class)->renderVar($input);

    // If rendered output is a Markup object, trim it and return it as such.
    if ($output instanceof MarkupInterface) {
      return Markup::create($truncate->truncateChars((string) $output, $limit));
    }

    // If rendered output is a scalar, trim it and return it as a string.
    if (is_scalar($output)) {
      return $truncate->truncateChars((string) $output, $limit);
    }

    // Just return input if we didn't fall in any of the cases above.
    return $input;
  }

  /**
   * Filter out empty, false and null values.
   *
   * @param array $entry
   *   Array to be filtered.
   *
   * @return array
   *   The filtered output.
   */
  public function filterEmpty(array $entry): array {
    return array_filter($entry, function ($var) {
      return $var !== '' && $var !== FALSE && $var !== NULL;
    });
  }

  /**
   * Bubbles Twig template argument's cacheability & attachment metadata.
   *
   * For example: a generated link or generated URL object is passed as a Twig
   * template argument, and its bubbleable metadata must be bubbled.
   *
   * @param mixed $arg
   *   A Twig template argument that is about to be printed.
   *
   * @see \Drupal\Core\Template\TwigExtension::bubbleArgMetadata()
   */
  protected function bubbleArgMetadata($arg) {
    // If it's a renderable, then it'll be up to the generated render array it
    // returns to contain the necessary cacheability & attachment metadata. If
    // it doesn't implement CacheableDependencyInterface or AttachmentsInterface
    // then there is nothing to do here.
    if ($arg instanceof RenderableInterface || !($arg instanceof CacheableDependencyInterface || $arg instanceof AttachmentsInterface)) {
      return;
    }

    $arg_bubbleable = [];
    BubbleableMetadata::createFromObject($arg)
      ->applyTo($arg_bubbleable);

    $this->renderer->render($arg_bubbleable);
  }

  /**
   * Gets icon based on the url that is used in ecl-twig/link component.
   *
   * @param array $context
   *   The twig context.
   * @param string $path
   *   The internal path or external URL.
   * @param string $size
   *   Size of the icon. Default is "s".
   *
   * @return array
   *   Icon settings to be used in the ecl-twig/link component.
   */
  public function getLinkIcon(array $context, string $path, string $size = 's'): array {
    $icon_path = $context['ecl_icon_path'];

    $icon = [
      'path' => $icon_path,
      'size' => $size,
      'color' => 'primary',
    ];
    if ($this->externalLinks->isExternalLink($path)) {
      $icon['name'] = 'external';
    }
    else {
      $icon['name'] = 'corner-arrow';
      $icon['transform'] = 'rotate-90';
    }

    return $icon;
  }

  /**
   * Processes footer links to make them compatible with ECL formatting.
   *
   * @param array $context
   *   The twig context.
   * @param array $links
   *   Set of links to be processed.
   *
   * @return array
   *   Set of processed links.
   */
  public function eclFooterLinks(array $context, array $links): array {
    $ecl_links = [];

    foreach ($links as $link) {
      // Skip if the link is limited to some ECL branding and the current
      // ECL branding does not match.
      if (!empty($link['branding']) && $context['ecl_branding'] !== $link['branding']) {
        continue;
      }
      $ecl_link = [
        'link' => [
          'label' => $link['label'],
          'path' => $link['href'],
          'icon_position' => 'after',
        ],
      ];

      if (!empty($link['external']) && $link['external'] === TRUE) {
        $ecl_link += [
          'icon' => [
            'path' => $context['ecl_icon_path'],
            'name' => 'external',
            'size' => 'xs',
          ],
        ];
      }

      if (!empty($link['social_network'])) {
        $ecl_link['link']['icon_position'] = 'before';
        $ecl_link += [
          'icon' => [
            'path' => $context['ecl_icon_social_media_path'],
            'name' => $context['ecl_component_library'] == 'eu' ? $link['social_network'] : $link['social_network'] . '-negative',
          ],
        ];
      }

      $ecl_links[] = $ecl_link;
    }

    return $ecl_links;
  }

  /**
   * Checks if a given path is external or not.
   *
   * @param string $path
   *   The path to be checked.
   *
   * @return bool
   *   Whether the path is external.
   */
  public function isExternal(string $path): bool {
    return $this->externalLinks->isExternalLink($path);
  }

  /**
   * Creates a Markup object.
   *
   * @param mixed $string
   *   The string to mark as safe. This value will be cast to a string.
   *
   * @return \Drupal\Component\Render\MarkupInterface
   *   A safe string.
   */
  public function createMarkup($string): MarkupInterface {
    return Markup::create($string);
  }

}
