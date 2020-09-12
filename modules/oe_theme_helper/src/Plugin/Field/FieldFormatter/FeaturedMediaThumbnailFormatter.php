<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\Field\FieldFormatter;

use Drupal\media\Plugin\Field\FieldFormatter\MediaThumbnailFormatter;

/**
 * Display a featured media thumbnail.
 *
 * @FieldFormatter(
 *   id = "oe_theme_helper_featured_media_thumbnail_formatter",
 *   label = @Translation("Thumbnail"),
 *   description = @Translation("Display a featured media thumbnail."),
 *   field_types = {
 *     "oe_featured_media"
 *   }
 * )
 */
class FeaturedMediaThumbnailFormatter extends MediaThumbnailFormatter {

}
