<?php

declare(strict_types=1);

namespace Drupal\oe_theme_helper\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides preview pages for SDC components used in Behat tests.
 */
class ComponentPreviewController extends ControllerBase {

  /**
   * Renders the dropdown component preview page.
   *
   * @return array
   *   A render array.
   */
  public function dropdown(): array {
    return [
      '#type' => 'component',
      '#component' => 'oe_theme:dropdown',
      '#props' => [
        'button_label' => 'Dropdown',
        'links' => [
          [
            'label' => 'European Commission',
            'url' => 'http://example.com',
          ],
          [
            'label' => 'Priorities',
            'url' => 'http://example.com',
          ],
          [
            'label' => 'Jobs, Growth and Investment',
            'url' => 'http://example.com',
          ],
        ],
      ],
    ];
  }

}
