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
  public function offsetExists($offset): bool {
    return array_key_exists($offset, $this->getArray());
  }

  /**
   * {@inheritdoc}
   */
  public function getIterator(): \Traversable {
    return new \ArrayIterator($this->getArray());
  }

  /**
   * {@inheritdoc}
   */
  public function offsetGet($offset): mixed {
    return $this->getArray()[$offset];
  }

  /**
   * {@inheritdoc}
   */
  public function offsetSet($offset, $value): void {
    // Does nothing as a value object array access is meant to be read-only.
  }

  /**
   * {@inheritdoc}
   */
  public function offsetUnset($offset): void {
    // Does nothing as a value object array access is meant to be read-only.
  }

}
