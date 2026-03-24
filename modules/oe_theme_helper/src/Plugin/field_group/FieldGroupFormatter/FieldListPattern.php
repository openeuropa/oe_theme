<?php

declare(strict_types=1);

namespace Drupal\oe_theme_helper\Plugin\field_group\FieldGroupFormatter;

use Drupal\Core\Render\Element;
use Drupal\Core\StringTranslation\TranslatableMarkup;

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
  public static function defaultSettings() {
    return [
      'wrapper_classes' => '',
      'with_border_color' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm() {
    $form = parent::settingsForm();

    $form['wrapper_classes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Wrapper classes'),
      '#default_value' => $this->getSetting('wrapper_classes'),
    ];

    $form['with_border_color'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Apply border color'),
      '#description' => $this->t('Apply the ECL border color utility class based on the active component library. Only visible if an actual border class is passed in EXtra classes.'),
      '#default_value' => (bool) $this->getSetting('with_border_color'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    if ($this->getSetting('wrapper_classes')) {
      $summary[] = $this->t('Wrapper classes: @wrapper_classes', ['@wrapper_classes' => $this->getSetting('wrapper_classes')]);
    }

    if ($this->getSetting('with_border_color')) {
      $summary[] = $this->t('Apply border color');
    }

    return $summary;
  }

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
    parent::preRender($element, $rendering_object);

    if ($this->getSetting('wrapper_classes') !== '') {
      $element['pattern']['#props']['wrapper_classes'] = $this->getSetting('wrapper_classes');
    }

    if (!$this->getSetting('with_border_color')) {
      return;
    }

    $border_color_class = theme_get_setting('component_library') === 'ec'
      ? 'ecl-u-border-color-neutral-50'
      : 'ecl-u-border-color-primary-10';

    $element['pattern']['#props']['wrapper_classes'] = trim(
      ($element['pattern']['#props']['wrapper_classes'] ?? '') . ' ' . $border_color_class
    );
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
        'label' => $label instanceof TranslatableMarkup ? $label->render() : $label,
        'body' => [
          '#label_display' => 'hidden',
        ] + $element[$field_name],
      ];
    }

    return $fields;
  }

}
