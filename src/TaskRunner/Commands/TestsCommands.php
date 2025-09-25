<?php

declare(strict_types=1);

namespace Drupal\oe_theme\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use Robo\Exception\AbortTasksException;

/**
 * Defines ec-europa/toolkit commands to run tests.
 */
class TestsCommands extends AbstractCommands {

  /**
   * Run PHPUnit tests in parallel, by batches.
   *
   * The names of batches should be defined in toolkit.test.phpunit.batches.
   *
   * @command toolkit:test-phpunit-batches
   */
  public function toolkitTestPhpunitBatches() {
    $collection = $this->collectionBuilder();
    $batches = $this->getConfig()->get('toolkit.test.phpunit.batches');
    if (empty($batches) || !is_array($batches)) {
      throw new AbortTasksException('No batches defined in toolkit.test.phpunit.batches configuration.');
    }

    $this->say('Trying to run tests that are not assigned to any batch...');
    $phpunit_bin = $this->getBin('phpunit');
    $working_directory = $this->getWorkingDir();

    $unassigned_tests_result_file = $working_directory . '/junit-export/phpunit-unassigned-tests.xml';
    $collection->addTask($this->taskExec($phpunit_bin)
      ->option('log-junit', $unassigned_tests_result_file)
      ->option('exclude-group', implode(',', $batches))
    );

    // Check if there are unassigned tests.
    $collection->addCode(function () use ($unassigned_tests_result_file) {
      if (!file_exists($unassigned_tests_result_file)) {
        $this->yell("The result file does not exist: $unassigned_tests_result_file.", 40, 'red');
        return 1;
      }

      $xml = simplexml_load_file($unassigned_tests_result_file);
      if ($xml === FALSE) {
        $this->yell("Could not read $unassigned_tests_result_file file.", 40, 'red');
        return 1;
      }

      $unassigned_test_count = $xml->count();
      if ($unassigned_test_count > 0) {
        $this->yell("There are $unassigned_test_count tests that are not assigned to any batch.", 40, 'red');
        return 1;
      }

      return 0;
    });

    // Run tests in parallel batches.
    $parallel = $this->taskParallelExec()->printOutput();
    foreach ($batches as $batch_name) {
      $parallel->process("$phpunit_bin --group='$batch_name' --fail-on-empty-test-suite --log-junit='$working_directory/junit-export/phpunit-$batch_name.xml'");
    }

    $collection->addTask($parallel);
    return $collection;
  }

}
