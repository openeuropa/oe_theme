<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_inpage_navigation_test\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for inpage navigation test routes.
 */
class InpageNavigationTestController extends ControllerBase {

  /**
   * Generates a page with test content for the inpage navigation.
   *
   * @return array
   *   The response render array.
   */
  public function contentPage(): array {
    return [
      '#theme' => 'oe_theme_inpage_navigation_test_content',
    ];
  }

  /**
   * Returns content that doesn't generate any entries for inpage navigation.
   *
   * @return array
   *   The response render array.
   */
  public function noEntriesPage(): array {
    return [
      '#theme' => 'oe_theme_inpage_navigation_test_no_elements',
    ];
  }

}
