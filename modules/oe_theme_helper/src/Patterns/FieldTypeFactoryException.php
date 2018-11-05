<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Patterns;

/**
 * Exception thrown by pattern value objects' factory methods.
 */
class FieldTypeFactoryException extends FieldTypeException {

  /**
   * {@inheritdoc}
   */
  public function __construct(string $class_name) {
    parent::__construct("Could not create instance of {$class_name}. Initial value not supported.");
  }

}
