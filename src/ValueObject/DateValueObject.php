<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

use Drupal\Component\Datetime\DateTimePlus;

/**
 * Handle information about a date/date interval, as expected by the ECL.
 */
class DateValueObject extends ValueObjectBase implements DateValueObjectInterface {

  /**
   * Start date.
   *
   * @var \Drupal\Component\Datetime\DateTimePlus
   */
  protected $start;

  /**
   * End date.
   *
   * @var \Drupal\Component\Datetime\DateTimePlus
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
    $this->start = DateTimePlus::createFromTimestamp($start, $timezone);

    if ($end !== NULL) {
      $this->end = DateTimePlus::createFromTimestamp($end, $timezone);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function fromArray(array $parameters = []): ValueObjectInterface {
    return new static(
      $parameters['start'],
      $parameters['end'] ?? NULL,
      $parameters['timezone'] ?? NULL
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function fromTimestamp(int $start, int $end = NULL, string $timezone = NULL): DateValueObjectInterface {
    return new static($start, $end, $timezone);
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
   *   Formatted interval.
   */
  protected function getDateInterval(string $format, string $extra): string {
    $start = $this->start->format($format);

    if (!empty($this->end) &&
      (
        $this->start->format($format) !== $this->end->format($format)
        || $this->start->format($extra) !== $this->end->format($extra)
      )
      ) {
      return $start . '-' . $this->end->format($format);
    }

    return $start;
  }

}
