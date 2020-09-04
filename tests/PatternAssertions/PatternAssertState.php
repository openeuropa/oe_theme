<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\PatternAssertions;

/**
 * Basic pattern assertion state.
 *
 * An assertion state object is used to contain the expected value of a
 * rendered pattern and corresponding pattern assertion class.
 * This is used when asserting patterns embedded in other patterns.
 */
class PatternAssertState implements PatternAssertStateInterface {

  /**
   * The assertion object to be used.
   *
   * @var \Drupal\Tests\oe_theme\PatternAssertions\BasePatternAssert
   */
  private $assert;

  /**
   * The expected values.
   *
   * @var array
   */
  private $expected;

  /**
   * PatternAssertionState constructor.
   *
   * @param \Drupal\Tests\oe_theme\PatternAssertions\PatternAssertInterface $assert
   *   The assertion object.
   * @param array $expected
   *   The expected values.
   */
  public function __construct(PatternAssertInterface $assert, array $expected) {
    $this->assert = $assert;
    $this->expected = $expected;
  }

  /**
   * {@inheritdoc}
   */
  public function assert(string $html): void {
    $this->assert->assertPattern($this->expected, $html);
  }

}
