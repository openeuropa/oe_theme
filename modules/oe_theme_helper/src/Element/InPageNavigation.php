<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Element;

use Drupal\Component\Utility\Html;
use Drupal\Core\Render\Element\RenderElement;
use Drupal\Core\Template\Attribute;

/**
 * Provides a render element for an in-page navigation element.
 *
 * @RenderElement("oe_theme_helper_in_page_navigation")
 */
class InPageNavigation extends RenderElement {

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
        [$class, 'preRenderInPageNavigation'],
      ],
      '#theme_wrappers' => ['oe_theme_helper_in_page_navigation'],
    ];
  }

  /**
   * Builds page contents based on titles from the In-page navigation items.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   element.
   *
   * @return array
   *   The modified element with all group members.
   */
  public static function preRenderInPageNavigation(array $element): array {
    $links = [];

    if (!empty($element['#in_page_navigation_items'])) {
      foreach ($element['#in_page_navigation_items'] as $in_page_navigation_item) {
        // Generate list of links.
        $label = $in_page_navigation_item->label;
        $id = "inline-nav-" . Html::cleanCssIdentifier($label);
        $links[] = [
          'href' => "#" . $id,
          'label' => $label,
        ];
        $element[$in_page_navigation_item->group_name]['#title_attributes'] = new Attribute(['id' => $id]);
      }
    }
    $element['#links'] = $links;

    return $element;
  }

}
