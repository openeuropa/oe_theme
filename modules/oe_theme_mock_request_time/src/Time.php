<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_mock_request_time;

use Drupal\Component\Datetime\Time as CoreTime;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Overrides Drupal core 'datetime.time' service.
 */
class Time extends CoreTime {

  /**
   * Mock request time manager service.
   *
   * @var \Drupal\oe_theme_mock_request_time\MockRequestTimeManagerInterface
   */
  protected $requestTimeManager;

  /**
   * Constructs the Time object.
   *
   * @param \Drupal\oe_theme_mock_request_time\MockRequestTimeManagerInterface $request_time_manager
   *   Mock request time manager service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(MockRequestTimeManagerInterface $request_time_manager, RequestStack $request_stack) {
    parent::__construct($request_stack);
    $this->requestTimeManager = $request_time_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getRequestTime() {
    return $this->requestTimeManager->get();
  }

  /**
   * {@inheritdoc}
   */
  public function getRequestMicroTime() {
    return $this->requestTimeManager->get();
  }

}
