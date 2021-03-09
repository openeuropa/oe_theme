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
  public function settingsForm() {
    $form = parent::settingsForm();
    $form['label']['#required'] = TRUE;
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function preRender(&$element, $rendering_object) {
    parent::preRender($element, $rendering_object);

    $element += [
      '#theme' => 'oe_theme_helper_in_page_navigation',
      '#title' => $this->getLabel(),
    ];

    $children = Element::children($element);
    foreach ($children as $key => $group_name) {
      // Bail out if group does not exist or it's not of the right type.
      if (!$this->isInPageNavigationItem($group_name, $rendering_object)) {
        continue;
      }

      // Bail out if field group has no children.
      $group_object = $rendering_object['#fieldgroups'][$group_name];
      if (empty($group_object->children)) {
        continue;
      }

      // Choose children In-page navigation item field groups to process
      // in the render element.
      $element['#items'][] = [
        'label' => $group_object->label,
        'content' => $element[$group_name],
      ];
    }
  }

  /**
   * Check if given group is an in-page navigation group item.
   *
   * @param string $group
   *   Field group name.
   * @param array $rendering_object
   *   The object / entity being rendered.
   *
   * @return bool
   *   TRUE if provided field group is In-page navigation item, FALSE otherwise.
   */
  protected function isInPageNavigationItem(string $group, array $rendering_object): bool {
    return isset($rendering_object['#fieldgroups'][$group]) && $rendering_object['#fieldgroups'][$group]->format_type === 'oe_theme_helper_in_page_navigation_item';
  }

}
