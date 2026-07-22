<?php

declare(strict_types=1);

namespace Drupal\oe_theme_helper\Traits;

use Drupal\image\Entity\ImageStyle;

/**
 * Provides a list of image styles suitable for use as select list options.
 *
 * Replacement for image_style_options(), deprecated in drupal:11.4.0, whose
 * successor is not available in earlier supported core versions.
 */
trait ImageStyleOptionsTrait {

  /**
   * Builds the list of image styles for use as select list options.
   *
   * @param bool $include_empty
   *   If TRUE a '- None -' option is inserted in the options array.
   *
   * @return string[]
   *   Array of image styles keyed by machine name with the label as value.
   */
  protected function getImageStyleOptions(bool $include_empty = TRUE): array {
    $styles = ImageStyle::loadMultiple();
    $options = [];
    if ($include_empty && !empty($styles)) {
      $options[''] = $this->t('- None -');
    }
    foreach ($styles as $name => $style) {
      $options[$name] = $style->label();
    }

    if (empty($options)) {
      $options[''] = $this->t('No defined styles');
    }

    return $options;
  }

}
