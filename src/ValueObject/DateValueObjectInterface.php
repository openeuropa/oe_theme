<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\datetime_range\Plugin\Field\FieldType\DateRangeItem;

/**
 * Interface DateValueObjectInterface.
 */
interface DateValueObjectInterface extends ValueObjectInterface {

  /**
   * Instantiates a new DateValueObject from a DateRangeItem object.
   *
   * @param \Drupal\datetime_range\Plugin\Field\FieldType\DateRangeItem $dateRangeItem
   *   The DateRangeItem instance.
   *
   * @return \Drupal\oe_theme\ValueObject\DateValueObjectInterface
   *   A new ValueObject object.
   */
  public static function fromDateRangeItem(DateRangeItem $dateRangeItem): DateValueObjectInterface;

  /**
   * Instantiates a new DateValueObject from a DateTimeItem object.
   *
   * @param \Drupal\datetime\Plugin\Field\FieldType\DateTimeItem $dateTimeItem
   *   The instance of DateTimeItem from datetime field type value.
   *
   * @return \Drupal\oe_theme\ValueObject\DateValueObjectInterface
   *   A new ValueObject object.
   */
  public static function fromDateTimeItem(DateTimeItem $dateTimeItem): DateValueObjectInterface;

  /**
   * Get the start timestamp.
   *
   * @return int
   *   Returns timestamp.
   */
  public function getStartTime(): int;

  /**
   * Get the end timestamp.
   *
   * @return int
   *   Returns timestamp.
   */
  public function getEndTime(): int;

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

  /**
   * Get month full name.
   *
   * @return string
   *   Month full name.
   */
  public function getMonthFullName(): string;

  /**
   * Checks if the start and end date are the same.
   *
   * @param bool $strict
   *   To check in a strict way by comparing the timestamp (TRUE) or
   *   date strings (FALSE).
   * @param string $format
   *   The format to compare for.
   *
   * @return bool
   *   TRUE/FALSE.
   */
  public function isSameDate(bool $strict = FALSE, string $format = 'dmY'): bool;

}
