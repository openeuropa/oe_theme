<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Behat;

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Class DrupalContext.
 */
class DrupalContext extends RawDrupalContext {

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
  public function revertDemoSetup(AfterScenarioScope $scope) {
    \Drupal::service('module_installer')->uninstall(['oe_theme_demo']);
  }

}
