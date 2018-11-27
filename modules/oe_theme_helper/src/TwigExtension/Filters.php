<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\TwigExtension;

/**
 * Collection of extra Twig filters.
 *
 * We don't enforce any strict type checking on filters' arguments as they are
 * coming straight from Twig templates.
 */
class Filters extends \Twig_Extension {

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
    return (string) \Drupal::languageManager()->getLanguageName($language_code);
  }

  /**
   * Get a native language name given its code.
   *
   * @param mixed $language_code
   *   Two letters language code.
   *
   * @return string
   *   Language name.
   */
  public function toNativeLanguageName($language_code): string {
    // @todo: Fix this.
    // We should store language information contained in
    // MultilingualHelper::getEuropeanUnionLanguageList() in configuration.
    if (\Drupal::moduleHandler()->moduleExists('oe_multilingual')) {
      $languages = \Drupal::service('oe_multilingual.helper')->getLanguageNameList();
      return $languages[$language_code];
    }

    return $this->toLanguageName($language_code);
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
