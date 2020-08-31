<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\field_group\FieldGroupFormatter;

use Drupal\Core\Render\Element;

/**
 * Display a field group using the ECL in-page navigation component.
 *
 * @FieldGroupFormatter(
 *   id = "oe_theme_helper_in_page_navigation",
 *   label = @Translation("In-page navigation"),
 *   description = @Translation("Display a field group using the ECL in-page navigation component."),
 *   supported_contexts = {
 *     "view"
 *   }
 * )
 */
class InPageNavigation extends InPageNavigationBase {

  /**
   * {@inheritdoc}
   */
  public function preRender(&$element, $rendering_object) {
    parent::preRender($element, $rendering_object);

    $element += [
      '#type' => 'oe_theme_helper_in_page_navigation',
      '#title' => $this->getLabel(),
    ];

    $children = Element::children($element);
    $length = count($children);
    $i = 0;
    foreach ($children as $value) {
      // Mark first and last elements. It is helpful in theming.
      if ($i === 0) {
        $element[$value]['#first'] = TRUE;
      }
      if ($i === $length - 1) {
        $element[$value]['#last'] = TRUE;
      }
      $i++;
      $field_group_item = $this->getInPageNavigationItemGroup($value, $rendering_object['#fieldgroups']);
      if (empty($field_group_item)) {
        // It isn't In-page navigation item group.
        continue;
      }

      if (empty($field_group_item->children)) {
        // Field group is empty, so it won't be rendered - no need to create
        // link for it.
        continue;
      }

      // Choose children In-page navigation item field groups to process
      // in the render element.
      $element['#in_page_navigation_items'][] = $field_group_item;
    }
  }

  /**
   * Returns if the field is a group or a field.
   *
   * @param string $field_name
   *   The name of the field.
   * @param array $field_groups
   *   List of groups.
   *
   * @return object|null
   *   Object if field group is found, NULL otherwise.
   */
  protected function getInPageNavigationItemGroup(string $field_name, array $field_groups): ?object {
    if (in_array($field_name, array_keys($field_groups))
      && ($field_groups[$field_name]->format_type == 'oe_theme_helper_in_page_navigation_item')) {
      return $field_groups[$field_name];
    }
    return NULL;
  }

}
