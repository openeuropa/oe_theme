<?php

declare(strict_types=1);

namespace Drupal\oe_theme_helper\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines media_data_extractor annotation object.
 *
 * @Annotation
 */
class MediaDataExtractor extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

}
