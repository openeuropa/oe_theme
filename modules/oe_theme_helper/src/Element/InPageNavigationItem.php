<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Element;

use Drupal\Core\Render\Element\RenderElement;

/**
 * Provides a render element for an in-page navigation item element.
 *
 * @RenderElement("oe_theme_helper_in_page_navigation_item")
 */
class InPageNavigationItem extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);

    return [
      '#process' => [
        [$class, 'processGroup'],
      ],
      '#pre_render' => [
        [$class, 'preRenderGroup'],
      ],
      '#theme_wrappers' => ['oe_theme_helper_in_page_navigation_item'],
    ];
  }

}
