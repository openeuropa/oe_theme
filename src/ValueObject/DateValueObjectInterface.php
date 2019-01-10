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
   * @param string|null $timezone
   *   The timezone.
   *
   * @return \Drupal\oe_theme\ValueObject\DateValueObjectInterface
   *   A new DateValueObject.
   */
  public static function fromTimestamp(int $timestamp, string $timezone = NULL): DateValueObjectInterface;

  /**
   * Get day.
   *
   * @return string
   *   Day as a number.
   */
  public function getDay(): string;

  /**
   * Get month.
   *
   * @return string
   *   Month as a number.
   */
  public function getMonth(): string;

  /**
   * Get year.
   *
   * @return string
   *   Year.
   */
  public function getYear(): string;

  /**
   * Get week day.
   *
   * @return string
   *   Week day name.
   */
  public function getWeekDay(): string;

  /**
   * Get month name.
   *
   * @return string
   *   Month name.
   */
  public function getMonthName(): string;

}
