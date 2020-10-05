<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Behat;

use Behat\Behat\Hook\Scope\AfterFeatureScope;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Testwork\Hook\Scope\AfterSuiteScope;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\Tests\oe_content\Traits\EntityLoadingTrait;

/**
 * Class DrupalContext.
 */
class DrupalContext extends RawDrupalContext {

  use EntityLoadingTrait;

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
    $theme_name = \Drupal::theme()->getActiveTheme()->getName();
    \Drupal::configFactory()->getEditable($theme_name . '.settings')
      ->set('branding', 'standardised')->save();
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
    $theme_name = \Drupal::theme()->getActiveTheme()->getName();
    \Drupal::configFactory()->getEditable($theme_name . '.settings')
      ->set('branding', 'core')->save();
  }

  /**
   * Selects option in select field in a region.
   *
   * @When I select :option from :select in the :region region
   */
  public function selectOption(string $select, string $option, string $region): void {
    $session = $this->getSession();
    $regionObj = $session->getPage()->find('region', $region);
    if (!$regionObj) {
      throw new \Exception(sprintf('No region "%s" found on the page %s.', $region, $session->getCurrentUrl()));
    }
    $regionObj->selectFieldOption($select, $option);
  }

  /**
   * Assert viewing content given its type and title.
   *
   * @param string $title
   *   Content title.
   *
   * @Given I am visiting the :title content
   * @Given I visit the :title content
   */
  public function iAmViewingTheContent($title): void {
    $node = $this->loadEntityByLabel('node', $title);
    $this->visitPath($node->toUrl()->toString());
  }

  /**
   * Enables the datetime_testing module.
   *
   * @param \Behat\Behat\Hook\Scope\BeforeFeatureScope $scope
   *   The scope.
   *
   * @BeforeFeature @datetime_testing
   */
  public static function enableDatetimeTesting(BeforeFeatureScope $scope): void {
    \Drupal::service('module_installer')->install(['datetime_testing']);
  }

  /**
   * Disables the datetime_testing module.
   *
   * @param \Behat\Behat\Hook\Scope\AfterFeatureScope $scope
   *   The scope.
   *
   * @AfterFeature @datetime_testing
   */
  public static function disableDatetimeTesting(AfterFeatureScope $scope): void {
    \Drupal::service('module_installer')->uninstall(['datetime_testing']);
  }

}
