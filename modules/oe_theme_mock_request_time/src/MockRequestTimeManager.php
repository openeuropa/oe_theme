<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_mock_request_time;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\State\StateInterface;

/**
 * Set, get and clear mock request time.
 */
class MockRequestTimeManager implements MockRequestTimeManagerInterface {

  /**
   * Name of the key used to store current request time in the State storage.
   */
  const REQUEST_TIME_STATE_KEY = 'oe_theme_mock_request_time.request_time';

  /**
   * State API service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * MockRequestTimeCommands constructor.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   State API service.
   */
  public function __construct(StateInterface $state) {
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public function set(int $timestamp): void {
    $this->state->set(MockRequestTimeManager::REQUEST_TIME_STATE_KEY, $timestamp);
  }

  /**
   * {@inheritdoc}
   */
  public function get(): int {
    return $this->state->get(MockRequestTimeManager::REQUEST_TIME_STATE_KEY, REQUEST_TIME);
  }

  /**
   * {@inheritdoc}
   */
  public function clear(): void {
    $this->state->delete(MockRequestTimeManager::REQUEST_TIME_STATE_KEY);
  }

  /**
   * {@inheritdoc}
   */
  public function setFromFormat(string $format, string $request_time): int {
    $timestamp = DrupalDateTime::createFromFormat($format, $request_time)->getTimestamp();
    $this->set($timestamp);
    return $timestamp;
  }

  /**
   * {@inheritdoc}
   */
  public function isRequestTimeSet(): bool {
    return $this->state->get(MockRequestTimeManager::REQUEST_TIME_STATE_KEY) !== NULL;
  }

}
