<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Traits;

use Symfony\Component\HttpFoundation\Request;

/**
 * Helper methods to deal with requests.
 */
trait RequestTrait {

  /**
   * Sets a request to a certain URI as the current in the request stack.
   *
   * @param string $uri
   *   The URI of the request. It needs to match a valid Drupal route.
   */
  protected function setCurrentRequest(string $uri): void {
    // Simulate a request to a node canonical route with a language prefix.
    $request = Request::create($uri);
    // Let the Drupal router populate all the request parameters.
    $parameters = \Drupal::service('router.no_access_checks')->matchRequest($request);
    $request->attributes->add($parameters);
    // Set the prepared request as current.
    \Drupal::requestStack()->push($request);
    // Reset any discovered language. KernelTestBase creates a request to the
    // root of the website for legacy purposes, so the language is set by
    // default to the default one.
    // @see \Drupal\KernelTests\KernelTestBase::bootKernel()
    \Drupal::languageManager()->reset();
  }

}
