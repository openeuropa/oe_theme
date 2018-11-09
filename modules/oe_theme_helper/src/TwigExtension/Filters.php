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
   * Get file icon given its extension.
   *
   * @param mixed $extension
   *   File extension.
   *
   * @return string
   *   File icon.
   */
  public function toFileIcon($extension): string {
    // @todo: Complete and/or centralise extension to icon conversion.
    // Get icons from given file extension.
    switch ($extension) {
      case 'ppt':
        return 'presentation';

      default:
        return 'file';
    }
  }

}
