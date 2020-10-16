<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\datetime_range\Plugin\Field\FieldType\DateRangeItem;

/**
 * Handle information about a date/date interval, as expected by the ECL.
 */
class DateValueObject extends ValueObjectBase implements DateValueObjectInterface {

  /**
   * Start date.
   *
   * @var \Drupal\Core\Datetime\DrupalDateTime
   */
  protected $start;

  /**
   * End date.
   *
   * @var \Drupal\Core\Datetime\DrupalDateTime
   */
  protected $end;

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
    $this->start = DrupalDateTime::createFromTimestamp($start, $timezone);

    if ($end !== NULL) {
      $this->end = DrupalDateTime::createFromTimestamp($end, $timezone);
    }
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
   * {@inheritdoc}
   */
  public function getArray(): array {
    return [
      'day' => $this->getDay(),
      'month' => $this->getMonth(),
      'year' => $this->getYear(),
      'week_day' => $this->getWeekDay(),
      'month_name' => $this->getMonthName(),
      'month_fullname' => $this->getMonthFullName(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getDay(): string {
    return $this->getDateInterval('d', 'm');
  }

  /**
   * {@inheritdoc}
   */
  public function getMonth(): string {
    return $this->getDateInterval('m', 'Y');
  }

  /**
   * {@inheritdoc}
   */
  public function getYear(): string {
    return $this->getDateInterval('Y', 'Y');
  }

  /**
   * {@inheritdoc}
   */
  public function getWeekDay(): string {
    return $this->getDateInterval('D', 'm');
  }

  /**
   * {@inheritdoc}
   */
  public function getMonthName(): string {
    return $this->getDateInterval('M', 'Y');
  }

  /**
   * {@inheritdoc}
   */
  public function getMonthFullName(): string {
    return $this->getDateInterval('F', 'Y');
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
   *
   * @return string
   *   The formatted interval.
   */
  protected function getDateInterval(string $format, string $extra): string {
    $date_interval = $this->start->format($format);

    if (!empty($this->end) &&
      (
        $this->start->format($format) !== $this->end->format($format)
        || $this->start->format($extra) !== $this->end->format($extra)
      )
    ) {
      $date_interval .= '-' . $this->end->format($format);
    }

    return $date_interval;
  }

}
