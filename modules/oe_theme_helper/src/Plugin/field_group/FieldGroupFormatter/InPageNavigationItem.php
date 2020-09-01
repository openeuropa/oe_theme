<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\field_group\FieldGroupFormatter;

/**
 * Display a in-page navigation field group item.
 *
 * @FieldGroupFormatter(
 *   id = "oe_theme_helper_in_page_navigation_item",
 *   label = @Translation("In-page navigation item"),
 *   description = @Translation("Display a in-page navigation field group item."),
 *   supported_contexts = {
 *     "view"
 *   }
 * )
 */
class InPageNavigationItem extends InPageNavigationBase {

  /**
   * {@inheritdoc}
   */
  public function preRender(&$element, $rendering_object) {
    parent::preRender($element, $rendering_object);

    $element += [
      '#type' => 'oe_theme_helper_in_page_navigation_item',
      '#title' => $this->getLabel(),
      '#content' => $element,
    ];
  }

}
