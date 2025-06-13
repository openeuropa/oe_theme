<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme;

use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\PreparationStartedSubscriber;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Exception;
use PHPUnit\Runner\BeforeTestHook;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

// PHPUnit introduced new event system for extending PHPUnit.
// See: https://github.com/sebastianbergmann/phpunit/issues/4676
// See: https://github.com/sebastianbergmann/phpunit/issues/4596
// In PHPUnit 10, only the new event based system is available. However,
// the pipelines are also running tests via PHPUnit 9 (for Drupal core versions
// below 11).
// Extension implementation for PHPUnit version below 10.
// @todo Remove this implementation when PHPUnit 9 is no longer supported
//   (i.e. Drupal core version below 11 is not supported).
// See also:
// - phpunit9.xml.dist
// - command 'setup:phpunit9' in runner.yml.dist
// - step 'phpunit9' in .drone.yml
// - exclusion of this class in phpstan.neon.dist
if (interface_exists(BeforeTestHook::class)) {
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
      if (!$reflection->isSubclassOf(TestCase::class)) {
        return;
      }
      $doc_comment = $reflection->getDocComment();
      if ($doc_comment === FALSE) {
        throw new Exception("The following test has no doc comment: " . $test);
      }
      if (!preg_match('/@group batch(\d+)/', $doc_comment)) {
        throw new Exception("The following test has not been assigned to a test batch: " . $test);
      }
    }

  }
}

// Extension implementation for PHPUnit version 10 and above.
// @todo Keep this implementation only when PHPUnit 9 is no longer supported
//   (i.e. Drupal core version below 11 is not supported).
if (interface_exists(Extension::class)) {
  /**
   * Check if a test has been assigned to a test batch.
   */
  class EnsurePHPUnitBatchingTestExtension implements Extension {

    /**
     * Ensures that a test has been assigned to a test batch.
     *
     * @param string $test
     *   The test name.
     */
    public function ensureBatch(string $test): void {
      [$class] = \explode('::', $test);
      $reflection = new \ReflectionClass($class);
      if (!$reflection->isSubclassOf(TestCase::class)) {
        return;
      }
      $doc_comment = $reflection->getDocComment();
      if ($doc_comment === FALSE) {
        throw new Exception("The following test has no doc comment: " . $test);
      }
      if (!preg_match('/@group batch(\d+)/', $doc_comment)) {
        throw new Exception("The following test has not been assigned to a test batch: " . $test);
      }
    }

    /**
     * {@inheritdoc}
     */
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void {
      $facade->registerSubscriber(new class($this) implements PreparationStartedSubscriber {

        public function __construct(private EnsurePHPUnitBatchingTestExtension $thisClass) {}

        /**
         * {@inheritdoc}
         */
        public function notify(PreparationStarted $event): void {
          $this->thisClass->ensureBatch($event->test()->id());
        }

      });
    }

  }
}
