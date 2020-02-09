<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_mock_request_time\Commands;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\oe_theme_mock_request_time\MockRequestTimeManagerInterface;
use Drush\Commands\DrushCommands;

/**
 * Provide Drush commands to set, get and clear mock request time.
 */
class MockRequestTimeCommands extends DrushCommands {

  /**
   * Mock request time manager service.
   *
   * @var \Drupal\oe_theme_mock_request_time\MockRequestTimeManagerInterface
   */
  protected $requestTimeManager;

  /**
   * MockRequestTimeCommands constructor.
   *
   * @param \Drupal\oe_theme_mock_request_time\MockRequestTimeManagerInterface $request_time_manager
   *   Mock request time manager service.
   */
  public function __construct(MockRequestTimeManagerInterface $request_time_manager) {
    parent::__construct();
    $this->requestTimeManager = $request_time_manager;
  }

  /**
   * Set mock request time.
   *
   * @param string $request_time
   *   Date and time of the mock request time, as 'Y-m-d H:i:s'.
   *
   * @usage mrt:set '2020-01-15 12:00:00'
   *
   * @command mock-request-time:set
   * @aliases mrt:set
   */
  public function set(string $request_time): void {
    $timestamp = $this->requestTimeManager->setFromFormat(DrupalDateTime::FORMAT, $request_time);
    $this->logger()->success("Mock request time set to '{$request_time}', timestamp: {$timestamp}");
  }

  /**
   * Get test request time.
   *
   * @usage mrt:get '2020-01-15 12:00:00'
   *
   * @command mock-request-time:get
   * @aliases mrt:get
   */
  public function get(): void {
    if (!$this->requestTimeManager->isRequestTimeSet()) {
      $this->logger()->success("No mock request time set.");
      return;
    }
    $request_time = $this->requestTimeManager->get();
    $date = DrupalDateTime::createFromTimestamp($request_time)->format(DrupalDateTime::FORMAT);
    $this->logger()->success("Mock request time is set to {$date}, timestamp: {$request_time}");
  }

  /**
   * Clear mock request time.
   *
   * @usage mrt:clear '2020-01-15 12:00:00'
   *
   * @command mock-request-time:clear
   * @aliases mrt:clear
   */
  public function clear(): void {
    // Run the get command so we print the current mock request time, if any.
    $this->get();
    $this->requestTimeManager->clear();
    $this->logger()->success("Mock request time has been deleted, REQUEST_TIME will now be used.");
  }

}
