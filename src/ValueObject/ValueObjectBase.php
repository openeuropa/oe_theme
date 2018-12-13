<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

/**
 * Value object base class.
 *
 * This class provides read-only array access for value objects and it should be
 * extended by all value object implementations.
 */
abstract class ValueObjectBase implements ValueObjectInterface {

  /**
   * {@inheritdoc}
   */
  public function offsetExists($offset) {
    return array_key_exists($offset, $this->toArray());
  }

  /**
   * {@inheritdoc}
   */
  public function offsetGet($offset) {
    return $this->toArray()[$offset];
  }

  /**
   * {@inheritdoc}
   */
  public function offsetSet($offset, $value) {
    // Does nothing as a value object array access is meant to be read-only.
  }

  /**
   * {@inheritdoc}
   */
  public function offsetUnset($offset) {
    // Does nothing as a value object array access is meant to be read-only.
  }

}
