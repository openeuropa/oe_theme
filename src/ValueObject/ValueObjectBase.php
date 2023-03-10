<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

use Drupal\Core\Cache\RefinableCacheableDependencyTrait;

/**
 * Value object base class.
 *
 * This class provides read-only array access for value objects and it should be
 * extended by all value object implementations.
 */
abstract class ValueObjectBase implements ValueObjectInterface {

  use RefinableCacheableDependencyTrait;

  /**
   * {@inheritdoc}
   */
  #[\ReturnTypeWillChange]
  public function offsetExists($offset) {
    return array_key_exists($offset, $this->getArray());
  }

  /**
   * {@inheritdoc}
   */
  #[\ReturnTypeWillChange]
  public function getIterator() {
    return new \ArrayIterator($this->getArray());
  }

  /**
   * {@inheritdoc}
   */
  #[\ReturnTypeWillChange]
  public function offsetGet($offset) {
    return $this->getArray()[$offset];
  }

  /**
   * {@inheritdoc}
   */
  #[\ReturnTypeWillChange]
  public function offsetSet($offset, $value) {
    // Does nothing as a value object array access is meant to be read-only.
  }

  /**
   * {@inheritdoc}
   */
  #[\ReturnTypeWillChange]
  public function offsetUnset($offset) {
    // Does nothing as a value object array access is meant to be read-only.
  }

}
