<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\field_group\FieldGroupFormatter;

use Drupal\Core\Template\Attribute;
use Drupal\Core\Render\Element;
use Drupal\Component\Utility\Html;

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

    // Build page contents based on titles from the In-page navigation item
    // groups.
    $links = [];
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

      // Generate list of links.
      $label = $field_group_item->label;
      $id = "inline-nav-" . Html::cleanCssIdentifier($label);
      $links[] = [
        'href' => "#" . $id,
        'label' => $label,
      ];

      $element[$value]['#title_attributes'] = new Attribute();
      $element[$value]['#title_attributes']->setAttribute('id', $id);
    }

    $element['#links'] = $links;
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
   *   Object if field group is found, FALSE otherwise.
   */
  protected function getInPageNavigationItemGroup(string $field_name, array $field_groups): ?object {
    if (in_array($field_name, array_keys($field_groups))
      && ($field_groups[$field_name]->format_type == 'oe_theme_helper_in_page_navigation_item')) {
      return $field_groups[$field_name];
    }
    return NULL;
  }

}
