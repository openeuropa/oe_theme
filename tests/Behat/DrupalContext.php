<?php

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
   * @BeforeScenario @demo
   */
  public function setupDemo(BeforeScenarioScope $scope) {
    \Drupal::service('module_installer')->install(['oe_theme_demo']);
  }

  /**
   * Revert demo site setup.
   *
   * @AfterScenario @demo
   */
  public function revertDemoSetup(AfterScenarioScope $scope) {
    \Drupal::service('module_installer')->uninstall(['oe_theme_demo']);
  }

}
