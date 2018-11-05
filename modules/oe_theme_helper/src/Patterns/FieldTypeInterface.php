<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Patterns;

/**
 * Interface implemented by all field type value objects.
 */
interface FieldTypeInterface {

  /**
   * Gets value object as an array.
   *
   * @return array
   *   An array of property values, keyed by property name.
   */
  public function toArray(): array;

  /**
   * Construct object from an array.
   *
   * @param array $values
   *   List of values.
   *
   * @return $this
   */
  public static function fromArray(array $values = []): FieldTypeInterface;

  /**
   * Get object instance.
   *
   * @param mixed $value
   *   Mixed value from which to construct the object.
   *
   * @return $this
   */
  public static function getInstance($value): FieldTypeInterface;

}
