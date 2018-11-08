<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

/**
 * Interface implemented by all field type value objects.
 */
interface ValueObjectInterface {

  /**
   * Gets value object as an array.
   *
   * @return array
   *   An array of property values, keyed by property name.
   */
  public function toArray(): array;

  /**
   * Build and return a value object from a given array.
   *
   * @param array $values
   *   List of values.
   *
   * @return $this
   */
  public static function fromArray(array $values = []): ValueObjectInterface;

  /**
   * Get an object instance from any value.
   *
   * This factory will be calling other factories, such as ValueObjectInterface::fromArray().
   *
   * @param mixed $value
   *   Mixed value from which to construct the object.
   *
   * @return $this
   */
  public static function fromAny($value): ValueObjectInterface;

}
