<?php

declare(strict_types=1);

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
  public function offsetExists(mixed $offset): bool {
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
  public function offsetGet(mixed $offset): mixed {
    return $this->getArray()[$offset];
  }

  /**
   * {@inheritdoc}
   */
  public function offsetSet(mixed $offset, mixed $value): void {
    // Does nothing as a value object array access is meant to be read-only.
  }

  /**
   * {@inheritdoc}
   */
  public function offsetUnset(mixed $offset): void {
    // Does nothing as a value object array access is meant to be read-only.
  }

}
