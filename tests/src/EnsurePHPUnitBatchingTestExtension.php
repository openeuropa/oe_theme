<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme;

use PHPUnit\Runner\BeforeTestHook;
use PHPUnit\Runner\Exception;

/**
 * Check if a test has been assigned to a test batch.
 */
class EnsurePHPUnitBatchingTestExtension implements BeforeTestHook {

  /**
   * {@inheritdoc}
   */
  public function executeBeforeTest(string $test): void {
    [$class] = \explode('::', $test);
    $reflection = new \ReflectionClass($class);
    if (!(bool) preg_match('/@group batch(\d+)/', $reflection->getDocComment())) {
      throw new Exception("The following test has not been assigned to a test batch: " . $test);
    }
  }

}
