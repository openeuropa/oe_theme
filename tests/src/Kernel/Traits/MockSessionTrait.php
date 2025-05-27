<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\Kernel\Traits;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

/**
 * Provides a trait to initialize a request with a mock session.
 */
trait MockSessionTrait {

  /**
   * Sets up a request with a mock session and pushes it into the request stack.
   */
  protected function setUpMockSessionRequest(): void {
    // Manually create a mock session.
    $session = new Session(new MockArraySessionStorage());
    $session->start();

    // Create a request and attach the session.
    $request = Request::create('/');
    $request->setSession($session);

    // Push the request with session into the request stack.
    \Drupal::service('request_stack')->push($request);
  }

}
