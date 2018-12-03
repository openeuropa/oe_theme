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
   * The storage variable.
   *
   * @var array
   */
  protected $storage;

  /**
   * {@inheritdoc}
   */
  public function offsetExists($offset) {
    return array_key_exists($offset, $this->getArray());
  }

  /**
   * {@inheritdoc}
   */
  public function offsetGet($offset) {
    return $this->getArray()[$offset];
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
    // This means that we cannot create methods like: clearTitle().
  }

  /**
   * {@inheritdoc}
   */
  public function __call($name, $arguments = []) {
    if (isset($this->storage[$name])) {
      return $this->storage[$name];
    }

    throw new \RuntimeException(sprintf('Method (%s) does not exists.', $name));
  }

  /**
   * {@inheritdoc}
   */
  public function getArray(): array {
    return (array) $this->storage;
  }

}
