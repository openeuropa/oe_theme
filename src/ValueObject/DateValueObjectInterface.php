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

}
