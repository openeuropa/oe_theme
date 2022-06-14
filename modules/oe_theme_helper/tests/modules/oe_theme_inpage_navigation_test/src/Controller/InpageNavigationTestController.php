<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_inpage_navigation_test\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for inpage navigation test routes.
 */
class InpageNavigationTestController extends ControllerBase {

  /**
   * Renders a test page using a specific variation of the test template.
   *
   * @param string $variation
   *   The template variation to render.
   *
   * @return array
   *   The response render array.
   */
  public function renderTemplate(string $variation): array {
    return [
      '#theme' => 'oe_theme_inpage_navigation_test__' . $variation,
    ];
  }

}
