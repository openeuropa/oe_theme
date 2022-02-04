<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

/**
 * Base class for fields that require display livestream link in exact time.
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
          'livestream_start_timestamp' => $timestamp * 1000,
        ],
      ],
    ];
  }

}
