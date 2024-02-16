<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\Traits;

/**
 * Trait for tests with enabled Javascript support.
 *
 * @todo Should be removed after Drupal 10.0.
 */
trait FunctionalJavascriptTrait {

  /**
   * Fail test on any console error.
   */
  public function failOnJavascriptErrors(): void {
    if (!empty($this->failOnJavascriptConsoleErrors) && \Drupal::service('module_handler')->moduleExists('js_testing_log_test')) {
      $errors = $this->getSession()->evaluateScript("JSON.parse(sessionStorage.getItem('js_testing_log_test.errors') || JSON.stringify([]))");
      if (!empty($errors)) {
        // Clear recorded in logs js errors.
        $this->getSession()->evaluateScript("sessionStorage.setItem('js_testing_log_test.errors', JSON.stringify([]));");
        $all_errors = implode("\n", $errors);
        throw new \RuntimeException('Javascript errors: ' . $all_errors);
      }
    }
  }

}
