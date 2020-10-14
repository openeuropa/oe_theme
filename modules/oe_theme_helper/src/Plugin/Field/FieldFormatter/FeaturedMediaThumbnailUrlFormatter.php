<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Display a featured media thumbnail URL.
 *
 * @FieldFormatter(
 *   id = "oe_theme_helper_featured_media_thumbnail_url_formatter",
 *   label = @Translation("Thumbnail URL"),
 *   description = @Translation("Display a featured media thumbnail URL."),
 *   field_types = {
 *     "oe_featured_media"
 *   }
 * )
 */
class FeaturedMediaThumbnailUrlFormatter extends MediaThumbnailUrlFormatter {

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    return TRUE;
  }

}
