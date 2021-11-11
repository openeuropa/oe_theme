<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\PatternAssertions;

/**
 * Interface implemented by all assertion states.
 */
interface PatternAssertStateInterface {

  /**
   * Asserts the given rendered pattern.
   *
   * @param string $html
   *   The rendered pattern.
   */
  public function assert(string $html): void;

}
