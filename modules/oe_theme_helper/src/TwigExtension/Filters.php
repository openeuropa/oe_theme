<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\TwigExtension;

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
    throw new \InvalidArgumentException("The language code $language_code does not exist or is not enabled.");
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
