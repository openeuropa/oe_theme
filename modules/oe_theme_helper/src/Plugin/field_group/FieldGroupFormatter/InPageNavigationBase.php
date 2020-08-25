<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\field_group\FieldGroupFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Template\Attribute;
use Drupal\field_group\FieldGroupFormatterBase;

/**
 * Base class for In-page navigation field groups.
 */
abstract class InPageNavigationBase extends FieldGroupFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function preRender(&$element, $rendering_object) {
    parent::preRender($element, $rendering_object);

    // Build attributes.
    $element_attributes = new Attribute();
    if ($this->getSetting('id')) {
      $element_attributes->setAttribute('id', Html::getUniqueId($this->getSetting('id')));
    }

    $classes = $this->getClasses();
    if (!empty($classes)) {
      $element_attributes->addClass($classes);
    }

    $element['#attributes'] = $element_attributes;
  }

}
