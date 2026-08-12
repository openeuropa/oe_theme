<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\Functional;

use Behat\Mink\Element\NodeElement;
use Drupal\Tests\BrowserTestBase;
use OpenEuropa\TestingUtilities\Traits\CachedDatabaseInstallTrait;

/**
 * Tests that corporate blocks are shown throughout the site.
 *
 * @group batch7
 */
class CorporateBlocksTest extends BrowserTestBase {

  use CachedDatabaseInstallTrait;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   *
   * Kept identical to CorporateFooterRenderTest and SiteBrandingTest so the
   * three share the same CachedDatabaseInstallTrait fingerprint and reuse a
   * single cached database install.
   */
  protected static $modules = [
    'block',
    'oe_search',
    'oe_multilingual',
    'oe_theme_helper',
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
    $this->config('system.theme')->set('default', 'oe_theme')->save();
    $this->container->set('theme.registry', NULL);
    $this->configFactory = $this->container->get('config.factory');

    // Allow visitors to register so the registration page is reachable.
    $this->config('user.settings')->set('register', 'visitors')->save();
  }

  /**
   * Tests that the corporate blocks are available throughout the site.
   */
  public function testCorporateBlocks(): void {
    $assert = $this->assertSession();

    $pages = [
      '<front>',
      'user/register',
    ];

    foreach ($pages as $page) {
      // By default, the European Commission footer is displayed.
      $this->drupalGet($page);
      $this->assertCorporateFooter('https://commission.europa.eu/index_en', 'European Commission logo');

      // The search form is rendered in the header.
      $assert->elementExists('css', '.ecl-site-header .ecl-search-form');

      // Switching to the European Union style displays the European Union
      // footer instead.
      $this->configFactory->getEditable('oe_theme.settings')->set('component_library', 'eu')->save();
      $this->drupalGet($page);
      $this->assertCorporateFooter('https://european-union.europa.eu/index_en', 'European Union flag', 'European Union');

      // Switching back to the European Commission style restores it.
      $this->configFactory->getEditable('oe_theme.settings')->set('component_library', 'ec')->save();
      $this->drupalGet($page);
      $this->assertCorporateFooter('https://commission.europa.eu/index_en', 'European Commission logo');
    }
  }

  /**
   * Asserts the presence of a corporate footer with the given logo data.
   *
   * @param string $link
   *   The expected logo link.
   * @param string $img_alt
   *   The expected logo image alt attribute.
   * @param string|null $img_title
   *   The expected logo picture title attribute, if any.
   */
  protected function assertCorporateFooter(string $link, string $img_alt, ?string $img_title = NULL): void {
    $assert = $this->assertSession();
    // Make sure a corporate footer is present on the page.
    $assert->elementExists('css', 'footer.ecl-site-footer');

    /** @var \Behat\Mink\Element\NodeElement $logo_link */
    $logo_link = $assert->elementExists('css', '.ecl-site-footer__logo-link');
    $this->assertEquals($link, $logo_link->getAttribute('href'));

    /** @var \Behat\Mink\Element\NodeElement $picture */
    $picture = $logo_link->find('css', 'picture');
    $this->assertInstanceOf(NodeElement::class, $picture);
    $this->assertEquals($img_alt, $picture->find('css', 'img')->getAttribute('alt'));
    if ($img_title !== NULL) {
      $this->assertEquals($img_title, $picture->getAttribute('title'));
    }
  }

}
