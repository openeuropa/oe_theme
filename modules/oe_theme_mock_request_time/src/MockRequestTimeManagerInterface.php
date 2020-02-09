<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_mock_request_time;

/**
 * Interface for mock request time manager.
 */
interface MockRequestTimeManagerInterface {

  /**
   * Set mock request time.
   *
   * @param int $timestamp
   *   Mock request time as UNIX timestamp.
   */
  public function set(int $timestamp): void;

  /**
   * Get mock request time, fallback to REQUEST_TIME if none available.
   *
   * @return int|null
   *   Mock request time as UNIX timestamp, if any. NULL otherwise.
   */
  public function get(): int;

  /**
   * Clear mock request time.
   */
  public function clear(): void;

  /**
   * Set mock request time form a given date format.
   *
   * @param string $format
   *   Date format.
   * @param string $request_time
   *   Date and time of the mock request time.
   *
   * @return int
   *   Mock request time as UNIX timestamp.
   */
  public function setFromFormat(string $format, string $request_time): int;

  /**
   * Check if mock request time is set.
   *
   * @return bool
   *   Return whether a mock request time is set.
   */
  public function isRequestTimeSet(): bool;

}
