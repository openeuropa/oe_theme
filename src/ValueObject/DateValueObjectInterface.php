<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

use Drupal\datetime_range\Plugin\Field\FieldType\DateRangeItem;

/**
 * Interface DateValueObjectInterface.
 */
interface DateValueObjectInterface extends ValueObjectInterface {

  /**
   * Factory method for constructing DateValueObject from DateRangeItem.
   *
   * @param \Drupal\datetime_range\Plugin\Field\FieldType\DateRangeItem $dateRangeItem
   *   The instance of DateRangeItem from datetime_range field type value.
   *
   * @return \Drupal\oe_theme\ValueObject\ValueObjectInterface
   *   A new ValueObject object.
   */
  public static function fromDateRangeItem(DateRangeItem $dateRangeItem): ValueObjectInterface;

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
