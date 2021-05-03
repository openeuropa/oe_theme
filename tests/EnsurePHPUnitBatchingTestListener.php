<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme;

use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;
use PHPUnit\Runner\Exception;

/**
 * Check if a test has been assigned to a test batch.
 */
class EnsurePHPUnitBatchingTestListener implements TestListener {

  use TestListenerDefaultImplementation;

  /**
   * {@inheritdoc}
   */
  public function startTest(Test $test) {
    if ($test instanceof TestCase) {
      $groups = $test->getGroups();
      if (empty(preg_grep('/^batch(\d+)$/', $groups))) {
        $reflection = new \ReflectionClass($test);
        $name = $reflection->name . '::' . $test->getName();
        throw new Exception("The following test has not been assigned to a test batch: " . $name);
      }
    }
  }

}
