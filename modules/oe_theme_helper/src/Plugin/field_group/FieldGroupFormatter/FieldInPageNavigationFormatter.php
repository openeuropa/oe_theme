<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\field_group\FieldGroupFormatter;

use Drupal\field_group\FieldGroupFormatterBase;
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
class FieldInPageNavigationFormatter extends FieldGroupFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function process(&$element, $processed_object) {
    $element += [
      '#type' => 'field_group_in_page_navigation',
    ];

    // Get children elements from the in-page navigation group.
    $links = [];
    foreach (Element::children($element) as $value) {
      $type = $this->groupOrField($value, $processed_object['#fieldgroups']);
      if (!$this->hasVisibleLabel($type, $value, $processed_object)) {
        continue;
      }
      if ($type == 'field') {
        $label = $processed_object[$value]['#title'];
      }
      elseif ($type == 'group') {
        $label = $processed_object['#fieldgroups'][$value]->label;
      }
      // If a group has a default id, use it as anchor link.
      if (!empty($processed_object['#fieldgroups'][$value]->format_settings['id'])) {
        $id = $processed_object['#fieldgroups'][$value]->format_settings['id'];
      }
      else {
        $id = "inline-nav-" . Html::cleanCssIdentifier($label);
      }

      $links[] = [
        'href' => "#" . $id,
        'label' => $label,
        'field' => $value,
      ];
      $element[$value]['#id'] = $id;
      $element[$value]['#attributes']['id'] = $id;
      $element[$value]['#in_page_navigation'] = TRUE;
    }

    $element['#links'] = $links;

    if (count($links) > 0) {
      $element['#inline_title'] = t('Page contents');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preRender(&$element, $rendering_object) {
    parent::preRender($element, $rendering_object);
    $this->process($element, $rendering_object);
  }

  /**
   * Returns if the field is a group or a field.
   *
   * @param string $field_name
   *   The name of the field.
   * @param array $field_groups
   *   List of groups.
   *
   * @return string
   *   The type of field, group or field.
   */
  protected function groupOrField(string $field_name, array $field_groups): string {
    if (in_array($field_name, array_keys($field_groups))) {
      return 'group';
    }
    return 'field';
  }

  /**
   * Returns if the field or group label is visible.
   *
   * @param string $type
   *   The type of field (field or group).
   * @param string $field_name
   *   The name of the field.
   * @param array $processed_object
   *   The object / entity beïng processed.
   *
   * @return bool
   *   Returns if the label is visible or not.
   */
  protected function hasVisibleLabel(string $type, string $field_name, array $processed_object): bool {
    if (
      $type === 'field' &&
      in_array($processed_object[$field_name]['#label_display'], ['above', 'inline'])
    ) {
      return TRUE;
    }

    if (
      $type === 'group' &&
      !empty($processed_object['#fieldgroups'][$field_name]) &&
      (
        !isset($processed_object['#fieldgroups'][$field_name]->format_settings['show_label']) ||
        $processed_object['#fieldgroups'][$field_name]->format_settings['show_label'] !== FALSE
      )
    ) {
      return TRUE;
    }

    return FALSE;
  }

}
