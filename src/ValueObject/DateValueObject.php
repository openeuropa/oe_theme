<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\DateTimeComputed;

/**
 * Handle information about a date.
 *
 * @property mixed $day
 * @property mixed $month
 * @property mixed $year
 * @property mixed $week_day
 * @property mixed $monthname
 *
 * @method day()
 * @method month()
 * @method year()
 * @method week_day()
 * @method monthname()
 */
class DateValueObject extends ValueObjectBase implements DateValueObjectInterface {

  /**
   * The storage variable.
   *
   * @var array
   */
  protected $storage;

  /**
   * DateValueObject constructor.
   *
   * @param string $day
   *   The date day.
   * @param string $month
   *   The date month.
   * @param string $year
   *   The date year.
   * @param string|int|null $week_day
   *   The day of the week.
   */
  private function __construct(string $day, string $month, string $year, string $week_day = NULL) {
    $this->storage = compact([
      'day',
      'month',
      'year',
    ]);

    $date = new DateTimePlus(implode('-', [$year, $month, $day]));

    $this->storage['week_day'] = empty($week_day) ?
      $date->format('l') :
      $week_day;
    $this->storage['monthname'] = $date->format('F');
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
  public static function fromDateTimeComputed(DateTimeComputed $dateTimeComputed): DateValueObjectInterface {
    return self::fromDrupalDateTime($dateTimeComputed->getValue());
  }

  /**
   * {@inheritdoc}
   */
  public static function fromDrupalDateTime(DrupalDateTime $drupalDateTime): DateValueObjectInterface {
    $parameters = explode(
      '-',
      $drupalDateTime->format('d-m-Y')
    );

    return new static(...$parameters);
  }

  /**
   * {@inheritdoc}
   */
  public static function fromArray(array $parameters = []): DateValueObjectInterface {
    return new static(...array_values($parameters));
  }

  /**
   * {@inheritdoc}
   */
  public function toArray(): array {
    return $this->storage;
  }

  /**
   * {@inheritdoc}
   */
  public function __call($name, $arguments = []) {
    if (isset($this->storage[$name])) {
      return $this->storage[$name];
    }

    // @todo: Should we return an exception or NULL ?
    throw new \RuntimeException(sprintf('Method (%s) does not exists.', $name));
  }

  /**
   * {@inheritdoc}
   */
  public function __get($name) {
    if (isset($this->storage[$name])) {
      return $this->storage[$name];
    }

    // @todo: Should we return an exception or NULL ?
    throw new \RuntimeException(sprintf('Property (%s) does not exists.', $name));
  }

  /**
   * {@inheritdoc}
   */
  public function __set($name, $value) {
    throw new \BadMethodCallException('Dynamic properties has been disabled. Use the class constructor for that.');
  }

  /**
   * {@inheritdoc}
   */
  public function __isset($name) {
    return isset($this->storage[$name]);
  }

}
