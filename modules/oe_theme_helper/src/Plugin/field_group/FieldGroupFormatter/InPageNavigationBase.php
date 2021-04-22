<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\field_group\FieldGroupFormatter;

use Drupal\field_group\FieldGroupFormatterBase;

/**
 * Base class for In-page navigation field groups.
 */
abstract class InPageNavigationBase extends FieldGroupFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsForm() {
    $form = parent::settingsForm();
    // Hide classes and id attribute settings.
    $form['id']['#access'] = FALSE;
    $form['classes']['#access'] = FALSE;
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function preRender(&$element, $rendering_object) {
    parent::preRender($element, $rendering_object);

    // Pass render object to template.
    $entity_type = $rendering_object['#entity_type'];
    $element['#entity'] = $rendering_object['#' . $entity_type];
  }

}
