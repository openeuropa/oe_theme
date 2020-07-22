<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\field_group\FieldGroupFormatter;

use Drupal\field_group\FieldGroupFormatterBase;

/**
 * Format a field group using the field list pattern.
 *
 * @FieldGroupFormatter(
 *   id = "oe_theme_helper_in_page_navigation",
 *   label = @Translation("Field In-page navigation"),
 *   description = @Translation("Format field group for ecl-inpage-navigation."),
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
    // Get children elements in in page navigation group.
    $field_elements = $processed_object['#fieldgroups'][$element['#group_name']]->children;
    // Add anchors links and anchors to children with labels.
    $links = [];
    $i = 1;
    foreach ($field_elements as $key => $value) {
      // Check if we should add the field to page contents.
      if (!is_array($element[$value]) || $this->excludeField($value, $element[$value], $processed_object['#fieldgroups']) === TRUE) {
        continue;
      }
      // We will add anchors when the element, field or group, has a label.
      $label = $this->fieldGetLabel($value, $processed_object);
      // Add menu element and anchor.
      if ($label !== '') {
        // If the group has a default id, use it as anchor link.
        if (isset($processed_object['#fieldgroups'][$value]) && isset($processed_object['#fieldgroups'][$value]->format_settings['id']) && $processed_object['#fieldgroups'][$value]->format_settings['id'] !== '') {
          $id = $processed_object['#fieldgroups'][$value]->format_settings['id'];
        }
        else {
          $id = "inline-nav-" . $i++;
        }
        $links[] = [
          'href' => "#" . $id,
          'label' => $label,
        ];
        $element[$value]['#id'] = $id;
        $element[$value]['#attributes']['id'] = $id;
      }
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
   * Check if the field should create an in page navigation entry.
   *
   * @param string $field_name
   *   Name of the field.
   * @param array $field
   *   The field to check.
   * @param array $fieldgroups
   *   List of groups.
   *
   * @return bool
   *   Include or not the field.
   */
  private function excludeField(string $field_name, array $field, array $fieldgroups) :bool {
    // Exclude empty fields.
    if (!in_array($field_name, array_keys($fieldgroups)) && !isset($field['#items'])) {
      return TRUE;
    }
    // Exclude empty groups.
    if (in_array($field_name, array_keys($fieldgroups)) && count($field) < 1) {
      return TRUE;
    }
    // Exclude groups with only empty fields.
    if (in_array($field_name, array_keys($fieldgroups))) {
      foreach ($field as $value) {
        // If there is a field with values, include the group.
        if (isset($value['#items'])) {
          return FALSE;
        }
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Get the label used on the field.
   *
   * @param string $field_name
   *   Name of the field.
   * @param array $processed_object
   *   Entity processed.
   *
   * @return string
   *   Label of the field.
   */
  private function fieldGetLabel(string $field_name, array $processed_object): string {
    $label = '';
    if (isset($processed_object['#fieldgroups'][$field_name]->label)) {
      if ($processed_object['#fieldgroups'][$field_name]->label !== '') {
        $label = $processed_object['#fieldgroups'][$field_name]->label;
        // We will assume groups labels are shown unless it is empty or
        // explicitly hidden.
        if (isset($processed_object['#fieldgroups'][$field_name]->format_settings['show_label']) && $processed_object['#fieldgroups'][$field_name]->format_settings['show_label'] === FALSE) {
          return '';
        }
      }
    }
    else {
      // For fields, check if the label is not hidden.
      if (!in_array($processed_object[$field_name]['#label_display'], ['hidden', 'visually_hidden'])) {
        return $processed_object[$field_name]['#title'];
      }
    }
    return $label;
  }

}
