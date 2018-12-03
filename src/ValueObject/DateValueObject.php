<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\datetime_range\Plugin\Field\FieldType\DateRangeItem;

/**
 * Handle information about a date/date interval, as expected by the ECL.
 *
 * @method day()
 * @method month()
 * @method month_name()
 * @method week_day()
 * @method year()
 */
class DateValueObject extends ValueObjectBase implements DateValueObjectInterface {

  /**
   * DateValueObject constructor.
   *
   * @param int $start
   *   Start date as UNIX timestamp.
   * @param int $end
   *   End date as UNIX timestamp.
   * @param string|null $timezone
   *   Timezone string, e.g. "Europe/Brussels".
   */
  private function __construct(int $start, int $end = NULL, string $timezone = NULL) {
    $startDate = DateTimePlus::createFromTimestamp($start, $timezone);
    $endDate = $end !== NULL ?
      DateTimePlus::createFromTimestamp($end, $timezone) :
      NULL;

    $this->storage = [
      'day' => $this->getDateInterval('d', 'm', $startDate, $endDate),
      'month' => $this->getDateInterval('m', 'Y', $startDate, $endDate),
      'year' => $this->getDateInterval('Y', 'Y', $startDate, $endDate),
      'week_day' => $this->getDateInterval('D', 'm', $startDate, $endDate),
      'month_name' => $this->getDateInterval('M', 'Y', $startDate, $endDate),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function fromArray(array $parameters = []): ValueObjectInterface {
    $parameters += ['start' => NULL, 'end' => NULL, 'timezone' => NULL];

    return new static(
      $parameters['start'],
      $parameters['end'],
      $parameters['timezone']
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function fromDateRangeItem(DateRangeItem $dateRangeItem): DateValueObjectInterface {
    return new static(
      $dateRangeItem->get('start_date')->getValue()->getTimeStamp(),
      $dateRangeItem->get('end_date')->getValue()->getTimeStamp(),
      NULL
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function fromDateTimeItem(DateTimeItem $dateTimeItem): DateValueObjectInterface {
    return new static(
      $dateTimeItem->get('date')->getValue()->getTimeStamp(),
      NULL,
      NULL
    );
  }

  /**
   * Get date interval as expected by the ECL.
   *
   * @param string $format
   *   Format to be used to print the interval.
   * @param string $extra
   *   Run extra check to make sure we should not print an interval.
   *   This is useful, for example, in case of same days on different months
   *   which should still be printed as an interval.
   * @param \Drupal\Component\Datetime\DateTimePlus $start
   *   The start date.
   * @param \Drupal\Component\Datetime\DateTimePlus|null $end
   *   The end date if any.
   *
   * @return string
   *   The formatted interval.
   */
  private function getDateInterval(string $format, string $extra, DateTimePlus $start, ?DateTimePlus $end = NULL): string {
    $date_interval = $start->format($format);

    if ($end !== NULL &&
      (
        $start->format($format) !== $end->format($format)
        || $start->format($extra) !== $end->format($extra)
      )
      ) {
      $date_interval .= '-' . $end->format($format);
    }

    return $date_interval;
  }

}
