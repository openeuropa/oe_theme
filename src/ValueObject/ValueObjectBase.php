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
    // @todo
    // array_key_exists will tell if a key exists in an array, whereas isset
    // will only return true if the key/variable exists *and* is not null.
    // Is this the intended behavior ?
    return array_key_exists($offset, $this->toArray());
  }

  /**
   * {@inheritdoc}
   */
  public function offsetGet($offset) {
    // @todo: We should use a single storage variable.
    return $this->toArray()[$offset];
  }

  /**
   * {@inheritdoc}
   */
  public function offsetSet($offset, $value) {
    // Does nothing as a value object array access is meant to be read-only.
    // @todo
    // If this object is meant to be immutable, then we need to refactor the
    // classes that are extending this.
    // This means that we cannot create methods like: setTitle(), etc etc.
  }

  /**
   * {@inheritdoc}
   */
  public function offsetUnset($offset) {
    // Does nothing as a value object array access is meant to be read-only.
    // @todo
    // If this object is meant to be immutable, then we need to refactor the
    // classes that are extending this.
    // This means that we cannot create methods like: clearTitle().
  }

}
