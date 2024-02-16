<?php

declare(strict_types=1);

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
  protected function getFields(array &$element, $rendering_object): array {
    $fields = [];

    foreach (Element::children($element) as $field_name) {
      $label = $element[$field_name]['#title'] ?? '';
      // By some conditions label of some fields could be passed not yet
      // translated. It can be related to field_group implementation.
      // @todo Investigate why some field labels are translated and some is not.
      if (!empty($label) && is_string($label)) {
        // @codingStandardsIgnoreStart
        $label = $this->t($label);
        // @codingStandardsIgnoreEnd
      }
      // Assign field label and content to the pattern's fields.
      $fields['items'][] = [
        'label' => $label,
        'body' => [
          '#label_display' => 'hidden',
        ] + $element[$field_name],
      ];
    }

    return $fields;
  }

}
