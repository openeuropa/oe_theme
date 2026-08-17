<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\Functional;

use Drupal\Tests\BrowserTestBase;
use OpenEuropa\TestingUtilities\Traits\CachedDatabaseInstallTrait;

/**
 * Tests the theme showcase (demo) features.
 *
 * @group batch6
 */
class ThemeShowcaseTest extends BrowserTestBase {

  use CachedDatabaseInstallTrait;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'oe_search',
    'oe_multilingual',
    'oe_theme_helper',
    'oe_theme_demo',
    'oe_corporate_blocks',
    'oe_corporate_site_info',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->cacheDbInstall = TRUE;
    parent::setUp();

    // Enable and set OpenEuropa Theme as default.
    $this->container->get('theme_installer')->install(['oe_theme']);
    \Drupal::service('plugin.manager.sdc')->clearCachedDefinitions();
    $this->config('system.theme')->set('default', 'oe_theme')->save();
    $this->container->set('theme.registry', NULL);
    $this->configFactory = $this->container->get('config.factory');

    $this->configFactory->getEditable('oe_theme.settings')->set('branding', 'standardised')->save();
  }

  /**
   * Tests the theme showcase (demo) features.
   */
  public function testThemeShowcase(): void {
    $assert = $this->assertSession();
    $this->drupalGet('<front>');

    // The demo site header features placeholder blocks.
    $assert->elementExists('css', '.ecl-site-header .ecl-site-header__logo-image');
    $assert->elementExists('css', '.ecl-site-header .ecl-search-form');
    $assert->elementExists('css', '.ecl-site-header .ecl-site-header__language-container');
    $assert->elementExists('css', '.ecl-site-header .ecl-menu');
    $assert->elementExists('css', '.ecl-site-header .ecl-site-header__site-name');

    // The demo site navigation features placeholder menu links.
    $navigation = $assert->elementExists('css', '.ecl-menu');
    foreach (['About', 'Priorities', 'Contacts'] as $link) {
      $this->assertNotNull($navigation->findLink($link), sprintf('Link "%s" not found in the navigation.', $link));
    }

    $priorities = $assert->elementExists('css', '.ecl-menu__item:nth-child(3) .ecl-menu__mega');
    $priority_links = [
      'Democratic change',
      'Digital single market',
      'Energy union and climate',
      'Internal market',
      'Jobs, growth and investment',
      'Justice and fundamental rights',
      'Migration',
      'Monetary union',
    ];
    foreach ($priority_links as $link) {
      $this->assertNotNull($priorities->findLink($link), sprintf('Link "%s" not found in the priorities dropdown menu.', $link));
    }

    $about = $assert->elementExists('css', '.ecl-menu__item:nth-child(2) .ecl-menu__mega');
    foreach (['Commission at work', 'Departments'] as $link) {
      $this->assertNotNull($about->findLink($link), sprintf('Link "%s" not found in the about dropdown menu.', $link));
    }

    // Changing the ECL branding switches the site header style. The demo
    // defaults to the standardised branding.
    $this->assertSiteHeader();

    $this->configFactory->getEditable('oe_theme.settings')->set('branding', 'core')->save();
    $this->drupalGet('<front>');
    $this->assertSiteHeader();
  }

  /**
   * Asserts that the site header matches the given ECL branding.
   */
  protected function assertSiteHeader(): void {
    $assert = $this->assertSession();
    $assert->elementExists('css', 'a.ecl-site-header__logo-link .ecl-site-header__logo-image');
    $assert->elementExists('css', '.ecl-site-header__top .ecl-site-header__action .ecl-site-header__language-selector');
    $assert->elementExists('css', '.ecl-site-header__top .ecl-site-header__action .ecl-site-header__search-container');

    // Both brandings render the site header banner as long as a menu is placed,
    // which is the case on the demo site.
    $assert->elementExists('css', '.ecl-site-header__banner');
    $assert->elementExists('css', '.ecl-site-header--has-menu .ecl-menu');
  }

}
