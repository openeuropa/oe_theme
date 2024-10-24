<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme;

use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\BeforeTestHook;
use PHPUnit\Runner\Exception;

/**
 * Check if a test has been assigned to a test batch.
 *
 * @todo This class should be removed and approach changed to new event system during upgrade to phpunit 10.
 * See https://localheinz.com/articles/2023/02/14/extending-phpunit-with-its-new-event-system/#content-components-in-the-new-event-system
 */
class EnsurePHPUnitBatchingTestExtension implements BeforeTestHook {

  /**
   * {@inheritdoc}
   */
  public function executeBeforeTest(string $test): void {
    [$class] = \explode('::', $test);
    $reflection = new \ReflectionClass($class);
    if (!$reflection->isSubclassOf(TestCase::class)) {
      return;
    }
    $doc_comment = $reflection->getDocComment();
    if ($doc_comment === FALSE) {
      throw new Exception("The following test has no doc comment: " . $test);
    }
    if (!(bool) preg_match('/@group batch(\d+)/', $doc_comment)) {
      throw new Exception("The following test has not been assigned to a test batch: " . $test);
    }
  }

}
