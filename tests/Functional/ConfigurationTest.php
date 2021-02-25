<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests that theme configuration is correctly applied.
 */
class ConfigurationTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'config',
    'system',
    'oe_theme_helper',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Enable and set OpenEuropa Theme as default.
    $this->container->get('theme_installer')->install(['oe_theme', 'oe_theme_subtheme_test']);
  }

  /**
   * Test that the the default libraries are loaded correctly.
   */
  public function testDefaultLibraryLoading(): void {
    foreach (['oe_theme', 'oe_theme_subtheme_test'] as $active_theme) {
      $this->container->get('theme_handler')->setDefault($active_theme);
      $this->container->set('theme.registry', NULL);

      $this->drupalGet('<front>');

      // Assert that we load the EC component library by default.
      $this->assertLinkContainsHref('/oe_theme/dist/ec/styles/ecl-ec-preset-website.css');
      $this->assertLinkContainsHref('/oe_theme/dist/ec/styles/ecl-ec-preset-website-print.css');
      $this->assertLinkContainsHref('/oe_theme/css/style-ec.css');

      $this->assertScriptContainsSrc('/oe_theme/dist/ec/scripts/ecl-ec-preset-website.js');
      $this->assertScriptContainsSrc('/oe_theme/js/ecl_auto_init.js');

      // Assert that we do not load the EU component library by default.
      $this->assertLinkNotContainsHref('/oe_theme/dist/eu/styles/ecl-eu-preset-website.css');
      $this->assertLinkNotContainsHref('/oe_theme/dist/eu/styles/ecl-eu-preset-website-print.css');
      $this->assertLinkNotContainsHref('/oe_theme/css/style-eu.css');

      $this->assertScriptNotContainsSrc('/oe_theme/dist/eu/scripts/ecl-eu-preset-website.js');

      // Assert that the ECL Editor preset is always loaded.
      $this->assertLinkContainsHref('/oe_theme/dist/styles/ecl-ec-preset-editor.css');
    }
  }

  /**
   * Test that the correct library is loaded after changing theme settings.
   */
  public function testChangeComponentLibrary(): void {
    foreach (['oe_theme', 'oe_theme_subtheme_test'] as $active_theme) {
      $this->container->get('theme_handler')->setDefault($active_theme);
      $this->container->set('theme.registry', NULL);

      $page = $this->getSession()->getPage();
      $assert_session = $this->assertSession();

      // Create a user that does have permission to administer theme settings.
      $user = $this->drupalCreateUser(['administer themes']);
      $this->drupalLogin($user);

      // Visit theme administration page.
      $this->drupalGet('/admin/appearance/settings/' . $active_theme);

      // Assert configuration select is properly rendered.
      $assert_session->selectExists('Component library');
      $assert_session->optionExists('Component library', 'European Commission');
      $assert_session->optionExists('Component library', 'European Union');

      // Select EU component library and save configuration.
      $page->selectFieldOption('Component library', 'European Union');
      $page->pressButton('Save configuration');

      // Visit font page.
      $this->drupalGet('<front>');

      // Assert that we load the EU component library.
      $this->assertLinkContainsHref('/oe_theme/dist/eu/styles/ecl-eu-preset-website.css');
      $this->assertLinkContainsHref('/oe_theme/dist/eu/styles/ecl-eu-preset-website-print.css');
      $this->assertLinkContainsHref('/oe_theme/css/style-eu.css');

      $this->assertScriptContainsSrc('/oe_theme/dist/eu/scripts/ecl-eu-preset-website.js');
      $this->assertScriptContainsSrc('/oe_theme/js/ecl_auto_init.js');

      // Assert that the favicon provided by the theme is being used.
      $this->assertSession()->responseContains('/' . $active_theme . '/favicon.ico');

      // Assert that we do not load the EC component library.
      $this->assertLinkNotContainsHref('/oe_theme/dist/ec/styles/ecl-ec-preset-website.css');
      $this->assertLinkNotContainsHref('/oe_theme/dist/ec/styles/ecl-ec-preset-website-print.css');
      $this->assertLinkNotContainsHref('/oe_theme/css/style-ec.css');

      $this->assertScriptNotContainsSrc('/oe_theme/dist/ec/scripts/ecl-ec-preset-website.js');

      // Assert that the ECL Editor preset is always loaded.
      $this->assertLinkContainsHref('/oe_theme/dist/styles/ecl-ec-preset-editor.css');

      // Visit theme administration page.
      $this->drupalGet('/admin/appearance/settings/' . $active_theme);

      // Select EC component library and save configuration.
      $page->selectFieldOption('Component library', 'European Commission');
      $page->pressButton('Save configuration');

      // Visit font page.
      $this->drupalGet('<front>');

      // Assert that we load the EC component library by default.
      $this->assertLinkContainsHref('/oe_theme/dist/ec/styles/ecl-ec-preset-website.css');
      $this->assertLinkContainsHref('/oe_theme/dist/ec/styles/ecl-ec-preset-website-print.css');
      $this->assertLinkContainsHref('/oe_theme/css/style-ec.css');

      $this->assertScriptContainsSrc('/oe_theme/dist/ec/scripts/ecl-ec-preset-website.js');
      $this->assertScriptContainsSrc('/oe_theme/js/ecl_auto_init.js');

      // Assert that the favicon provided by the theme is being used.
      $this->assertSession()->responseContains('/' . $active_theme . '/favicon.ico');

      // Assert that we do not load the EU component library by default.
      $this->assertLinkNotContainsHref('/oe_theme/dist/eu/styles/ecl-eu-preset-website.css');
      $this->assertLinkNotContainsHref('/oe_theme/dist/eu/styles/ecl-eu-preset-website-print.css');
      $this->assertLinkNotContainsHref('/oe_theme/css/style-eu.css');

      $this->assertScriptNotContainsSrc('/oe_theme/dist/eu/scripts/ecl-eu-preset-website.js');

      // Assert that the ECL Editor preset is always loaded.
      $this->assertLinkContainsHref('/oe_theme/dist/styles/ecl-ec-preset-editor.css');
    }
  }

  /**
   * Test that the correct layout is used after changing theme branding setting.
   */
  public function testChangeEclBranding(): void {
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();
    foreach (['oe_theme', 'oe_theme_subtheme_test'] as $active_theme) {
      $this->container->get('theme_handler')->setDefault($active_theme);
      $this->container->set('theme.registry', NULL);

      // Create a user that does have permission to administer theme settings.
      $user = $this->drupalCreateUser(['administer themes']);
      $this->drupalLogin($user);

      // Visit theme administration page.
      $this->drupalGet('/admin/appearance/settings/' . $active_theme);

      // Assert configuration select is properly rendered.
      $assert_session->selectExists('Branding');
      $assert_session->optionExists('Branding', 'Core');
      $assert_session->optionExists('Branding', 'Standardised');
      $assert_session->fieldValueEquals('Branding', 'core');

      // Visit font page.
      $this->drupalGet('<front>');

      // Make sure that classes for Core template is present.
      $assert_session->elementExists('css', 'header.ecl-site-header-core div.ecl-site-header-core__top');
      $assert_session->elementExists('css', 'header.ecl-site-header-core div.ecl-site-header-core__top div.ecl-site-header-core__action');

      // Visit theme administration page.
      $this->drupalGet('/admin/appearance/settings/' . $active_theme);

      // Select Standardised branding and save configuration.
      $page->selectFieldOption('Branding', 'Standardised');
      $page->pressButton('Save configuration');

      // Visit font page.
      $this->drupalGet('<front>');

      // Make sure that classes for Standardised branding is present.
      $assert_session->elementExists('css', 'header.ecl-site-header-standardised div.ecl-site-header-standardised__top');
      $assert_session->elementExists('css', 'header.ecl-site-header-standardised div.ecl-site-header-standardised__top div.ecl-site-header-standardised__action');
    }
  }

  /**
   * Assert that current response contians a link tag with given href.
   *
   * @param string $href
   *   Partial content of the href attribute.
   */
  protected function assertLinkContainsHref(string $href): void {
    $this->assertSession()->responseMatches('<link .*href=\".*' . preg_quote($href) . '\?\w+\" \/>');
  }

  /**
   * Assert that current response does not contian a link tag with given href.
   *
   * @param string $href
   *   Partial content of the href attribute.
   */
  protected function assertLinkNotContainsHref(string $href): void {
    $this->assertSession()->responseNotMatches('<link .*href=\".*' . preg_quote($href) . '\?\w+\" \/>');
  }

  /**
   * Assert that current response contians a script tag with given src.
   *
   * @param string $src
   *   Partial content of the src attribute.
   */
  protected function assertScriptContainsSrc(string $src): void {
    $this->assertSession()->responseMatches('<script .*src=\".*' . preg_quote($src) . '\?\w+\">');
  }

  /**
   * Assert that current response doe not contian a script tag with given src.
   *
   * @param string $src
   *   Partial content of the src attribute.
   */
  protected function assertScriptNotContainsSrc(string $src): void {
    $this->assertSession()->responseNotMatches('<script .*src=\".*' . preg_quote($src) . '\?\w+\">');
  }

}
