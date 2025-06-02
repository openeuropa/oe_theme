<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme;

use PHPUnit\Framework\TestCase;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\PreparedSubscriber;
use PHPUnit\Event\Code\TestMethod;

/**
 * Check if a test has been assigned to a test batch.
 */
final class EnsurePHPUnitBatchingTestExtension implements PreparedSubscriber {

  /**
   * {@inheritdoc}
   */
  public function notify(Prepared $event): void {
    $test = $event->test();
    if (!$test instanceof TestMethod) {
      return;
    }

    $className = $test->className();
    $reflection = new \ReflectionClass($className);
    if (!$reflection->isSubclassOf(TestCase::class)) {
      return;
    }

    $doc_comment = $reflection->getDocComment();
    if ($doc_comment === FALSE) {
      throw new \RuntimeException("The following test has no doc comment: {$className}");
    }

    if (!preg_match('/@group batch\d+/', $doc_comment)) {
      throw new \RuntimeException("The following test has not been assigned to a test batch: {$className}");
    }
  }

}
