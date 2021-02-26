<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;

/**
 * Interface implemented by all field type value objects.
 */
interface ValueObjectInterface extends \ArrayAccess, \IteratorAggregate, RefinableCacheableDependencyInterface {

  /**
   * Get value object as an array.
   *
   * @return array
   *   An array of property values, keyed by property name.
   */
  public function getArray(): array;

  /**
   * Build and return a value object from a given array.
   *
   * @param array $values
   *   List of values.
   *
   * @return \Drupal\oe_theme\ValueObject\ValueObjectInterface
   *   A new ValueObject object.
   */
  public static function fromArray(array $values = []): ValueObjectInterface;

}
