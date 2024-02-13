<?php

declare(strict_types=1);

namespace Drupal\oe_theme\ValueObject\Exception;

/**
 * Exception thrown by pattern value objects' factory methods.
 */
class ValueObjectFactoryException extends ValueObjectException {

  /**
   * {@inheritdoc}
   */
  public function __construct(string $class_name) {
    parent::__construct("Could not create instance of {$class_name}. Initial value not supported.");
  }

}
