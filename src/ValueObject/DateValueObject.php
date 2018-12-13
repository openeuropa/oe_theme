<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Handle information about a date.
 */
class DateValueObject extends ValueObjectBase implements DateValueObjectInterface {

  /**
   * The day property.
   *
   * @var string|int
   */
  private $day;

  /**
   * The month property.
   *
   * @var string|int
   */
  private $month;

  /**
   * The year property.
   *
   * @var string|int
   */
  private $year;

  /**
   * The weekDay property.
   *
   * @var string|int
   */
  private $weekDay;

  /**
   * The monthname property.
   *
   * @var string|int
   */
  private $monthname;

  /**
   * DateValueObject constructor.
   *
   * @param string $day
   *   The date day.
   * @param string $month
   *   The date month.
   * @param string $year
   *   The date year.
   * @param string|int|null $weekDay
   *   The day of the week.
   */
  private function __construct(string $day, string $month, string $year, string $weekDay = NULL) {
    $this->day = $day;
    $this->month = $month;
    $this->year = $year;
    $this->weekDay = $weekDay;

    $date = new DateTimePlus(implode('-', [$year, $month, $day]));

    $this->weekDay = empty($weekDay) ?
      $date->format('l') :
      $weekDay;
    $this->monthname = $date->format('F');
  }

  /**
   * {@inheritdoc}
   */
  public static function fromTimestamp(int $timestamp): DateValueObjectInterface {
    $parameters = explode(
      '-',
      DrupalDateTime::createFromTimestamp($timestamp)->format('d-m-Y')
    );

    return new static(...$parameters);
  }

  /**
   * {@inheritdoc}
   */
  public static function fromArray(array $parameters = []): ValueObjectInterface {
    return new static(...array_values($parameters));
  }

  /**
   * {@inheritdoc}
   */
  public function toArray(): array {
    return [
      'day' => $this->day,
      'month' => $this->month,
      'year' => $this->year,
      'week_day' => $this->weekDay,
      'monthname' => $this->monthname,
    ];
  }

}
