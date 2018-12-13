<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

/**
 * Interface DateValueObjectInterface.
 */
interface DateValueObjectInterface extends ValueObjectInterface {

  /**
   * DateValueObject factory that uses a timestamp.
   *
   * @param int $timestamp
   *   A timestamp.
   *
   * @return \Drupal\oe_theme\ValueObject\DateValueObjectInterface
   *   A new DateValueObject.
   */
  public static function fromTimestamp(int $timestamp): DateValueObjectInterface;

}
