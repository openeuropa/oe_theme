<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Element;

use Drupal\Component\Utility\Html;
use Drupal\Core\Render\Element\RenderElement;
use Drupal\Core\Template\Attribute;

/**
 * Provides a render element for an in-page navigation element.
 *
 * Properties:
 * - #items: list of items to be rendered, each having 'label' and 'content'.
 *
 * See \Drupal\Core\Render\Element\Link for additional properties.
 *
 * Usage Example:
 * @code
 *   $build['navigation'] = [
 *     '#type' => 'oe_theme_helper_in_page_navigation',
 *     '#items' => [
 *       [
 *         'label' => 'First section',
 *         'content' => 'First section content',
 *       ],
 *       [
 *         'label' => 'Second section',
 *         'content' => 'Second section content',
 *       ],
 *     ],
 *   ];
 * @endcode
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
    if (empty($element['#items'])) {
      return [];
    }

    // Process in-page navigation items, assigning them a unique ID.
    foreach ($element['#items'] as $key => $item) {
      $element['#items'][$key]['id'] = strtolower(Html::cleanCssIdentifier($element['#items'][$key]['label']));
    }

    return $element;
  }

}
