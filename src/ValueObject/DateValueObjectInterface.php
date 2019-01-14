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
   * @param int $start
   *   Start date as UNIX timestamp.
   * @param int $end
   *   End date as UNIX timestamp.
   * @param string|null $timezone
   *   Timezone string, e.g. "Europe/Brussels".
   *
   * @return \Drupal\oe_theme\ValueObject\DateValueObjectInterface
   *   A new DateValueObject.
   */
  public static function fromTimestamp(int $start, int $end = NULL, string $timezone = NULL): DateValueObjectInterface;

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
