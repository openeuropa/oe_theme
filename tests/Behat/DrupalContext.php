<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Behat;

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Testwork\Hook\Scope\AfterSuiteScope;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Class DrupalContext.
 */
class DrupalContext extends RawDrupalContext {

  /**
   * Installs the test module before executing any tests.
   *
   * @param \Behat\Testwork\Hook\Scope\BeforeSuiteScope $scope
   *   The hook scope.
   *
   * @BeforeSuite
   */
  public static function installTestModule(BeforeSuiteScope $scope): void {
    \Drupal::service('module_installer')->install(['oe_theme_test']);
  }

  /**
   * Uninstalls the test module after all the tests have run.
   *
   * @param \Behat\Testwork\Hook\Scope\AfterSuiteScope $scope
   *   The hook scope.
   *
   * @AfterSuite
   */
  public static function uninstallTestModule(AfterSuiteScope $scope): void {
    \Drupal::service('module_installer')->uninstall(['oe_theme_test']);
  }

  /**
   * Setup demo site.
   *
   * @param \Behat\Behat\Hook\Scope\BeforeScenarioScope $scope
   *   The Hook scope.
   *
   * @BeforeScenario @demo
   */
  public function setupDemo(BeforeScenarioScope $scope): void {
    \Drupal::service('module_installer')->install(['oe_theme_demo']);
  }

  /**
   * Revert demo site setup.
   *
   * @param \Behat\Behat\Hook\Scope\AfterScenarioScope $scope
   *   The Hook scope.
   *
   * @AfterScenario @demo
   */
  public function revertDemoSetup(AfterScenarioScope $scope): void {
    \Drupal::service('module_installer')->uninstall(['oe_theme_demo']);
  }

}
