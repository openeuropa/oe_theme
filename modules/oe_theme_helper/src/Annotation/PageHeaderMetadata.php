<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines the page header metadata plugin annotation.
 *
 * Example definition:
 * @code
 * @PageHeaderMetadata(
 *   id = "page_header_example",
 *   label = @Translation("Page header example"),
 *   weight = 0
 * )
 * @endcode
 *
 * @Annotation
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class PageHeaderMetadata extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The plugin weight.
   *
   * @var int
   */
  public $weight = 0;

}
