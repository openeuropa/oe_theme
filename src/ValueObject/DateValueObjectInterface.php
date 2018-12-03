<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\DateTimeComputed;

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

  /**
   * DateValueObject factory that uses a DateTimeComputed.
   *
   * @param \Drupal\datetime\DateTimeComputed $dateTimeComputed
   *   The DateTimeComputed object.
   *
   * @return \Drupal\oe_theme\ValueObject\DateValueObjectInterface
   *   A new DateValueObject.
   */
  public static function fromDateTimeComputed(DateTimeComputed $dateTimeComputed): DateValueObjectInterface;

  /**
   * DateValueObject factory that uses a DrupalDateTime.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $drupalDateTime
   *   The DrupalDateTime object.
   *
   * @return \Drupal\oe_theme\ValueObject\DateValueObjectInterface
   *   A new DateValueObject.
   */
  public static function fromDrupalDateTime(DrupalDateTime $drupalDateTime): DateValueObjectInterface;

  /**
   * DateValueObject factory that uses an array.
   *
   * @param array $parameters
   *   The parameters.
   *
   * @return \Drupal\oe_theme\ValueObject\DateValueObjectInterface
   *   A new DateValueObject.
   */
  public static function fromArray(array $parameters = []): self;

  /**
   * {@inheritdoc}
   */
  public function __call($name, $arguments = []);

  /**
   * {@inheritdoc}
   */
  public function __get($name);

  /**
   * {@inheritdoc}
   */
  public function __set($name, $value);

  /**
   * {@inheritdoc}
   */
  public function __isset($name);

}
