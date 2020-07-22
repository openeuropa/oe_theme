<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Element;

use Drupal\Core\Render\Element\RenderElement;

/**
 * Provides a render element for a html element.
 *
 * @FormElement("field_group_in_page_navigation")
 */
class InLinePaginationElement extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);

    return [
      '#process' => [
        [$class, 'processGroup'],
        [$class, 'processHtmlElement'],
      ],
      '#pre_render' => [
        [$class, 'preRenderGroup'],
      ],
      '#theme_wrappers' => ['field_group_in_page_navigation'],
    ];
  }

}
