<?php

declare(strict_types=1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

/**
 * Base class for fields rendering livestream information.
 */
abstract class OnlineDisplayExtraFieldBase extends InfoDisclosureExtraFieldBase {

  /**
   * Add livestream information disclosure script.
   *
   * {@inheritdoc}
   */
  protected function attachDisclosureScript(array &$build, int $timestamp): void {
    $build['#attached'] = [
      'library' => 'oe_theme_content_event/livestream_link_disclosure',
      'drupalSettings' => [
        'oe_theme_content_event' => [
          // Disclose the livestream information 10 minutes before it starts.
          'livestream_start_timestamp' => ($timestamp - 600) * 1000,
        ],
      ],
    ];
  }

}
