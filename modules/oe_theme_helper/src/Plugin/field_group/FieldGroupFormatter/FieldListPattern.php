<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\field_group\FieldGroupFormatter;

use Drupal\Core\Render\Element;

/**
 * Format a field group using the field list pattern.
 *
 * @FieldGroupFormatter(
 *   id = "oe_theme_helper_field_list_pattern",
 *   label = @Translation("Field list pattern"),
 *   description = @Translation("Format a field group using the field list pattern."),
 *   supported_contexts = {
 *     "view"
 *   }
 * )
 */
class FieldListPattern extends PatternFormatterBase {

  /**
   * {@inheritdoc}
   */
  protected function getPatternId(): string {
    return 'field_list';
  }

  /**
   * {@inheritdoc}
   */
  public function preRender(&$element, $rendering_object) {
    // Instantiate pattern render array.
    $pattern = [
      '#type' => 'pattern',
      '#id' => $this->getPatternId(),
      '#variant' => $this->getSetting('variant'),
      '#fields' => [],
    ];

    foreach (Element::children($element) as $field_name) {
      // Assign field label and content to the pattern's fields.
      $pattern['#fields']['items'][] = [
        'label' => isset($element[$field_name]['#title']) ? $element[$field_name]['#title'] : '',
        'body' => [
          '#label_display' => 'hidden',
        ] + $element[$field_name],
      ];

      // Remove field render array from the field group element.
      unset($element[$field_name]);
    }

    // Pass along the pattern render array.
    $element['pattern'] = $pattern;
  }

}
