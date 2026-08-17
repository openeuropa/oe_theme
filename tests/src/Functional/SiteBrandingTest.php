<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\Functional;

use Behat\Mink\Element\NodeElement;
use Drupal\Tests\BrowserTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
use OpenEuropa\TestingUtilities\Traits\CachedDatabaseInstallTrait;

/**
 * Tests the site branding provided by the theme.
 *
 * @group batch6
 */
class SiteBrandingTest extends BrowserTestBase {

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

    // Allow visitors to register so the registration page is reachable.
    $this->config('user.settings')->set('register', 'visitors')->save();

    // Add a non-EU language.
    $language = ConfigurableLanguage::createFromLangcode('is');
    $language->setThirdPartySetting('oe_multilingual', 'category', 'non_eu');
    $language->save();
  }

  /**
   * Tests the site branding provided by the theme.
   */
  public function testSiteBranding(): void {
    // The breadcrumb and the page header are visible everywhere.
    foreach (['<front>', 'user/register'] as $page) {
      $this->drupalGet($page);
      $this->assertSession()->elementExists('css', '.ecl-breadcrumb');
      $this->assertSession()->elementExists('css', '.ecl-page-header');
    }

    // The European Union header logo exposes the correct accessibility
    // attributes and localised mobile and desktop logos.
    $this->setComponentLibrary('eu');
    $eu_logos = [
      'en' => 'https://european-union.europa.eu/index_en',
      'bg' => 'https://european-union.europa.eu/index_bg',
      'fr' => 'https://european-union.europa.eu/index_fr',
      // Non-EU language falls back to the language-neutral link.
      'is' => 'https://european-union.europa.eu',
    ];
    foreach ($eu_logos as $langcode => $link) {
      $this->drupalGetInLanguage('<front>', $langcode);
      $this->assertHeaderLogo($link, 'Home - European Union', 'European Union flag', 'European Union');
    }
    // The logo renders consistently on non-front pages too.
    $this->drupalGetInLanguage('user/register', 'en');
    $this->assertHeaderLogo('https://european-union.europa.eu/index_en', 'Home - European Union', 'European Union flag', 'European Union');

    // The footer logo is available even when a non-EU language is selected.
    $this->drupalGetInLanguage('<front>', 'is');
    $this->assertSession()->elementExists('css', 'footer a.ecl-site-footer__logo-link .ecl-site-footer__logo-image');

    // The European Commission header logo exposes the correct accessibility
    // attributes.
    $this->setComponentLibrary('ec');
    $ec_logos = [
      'en' => 'https://commission.europa.eu/index_en',
      'bg' => 'https://commission.europa.eu/index_bg',
      // Non-EU language falls back to the language-neutral link.
      'is' => 'https://commission.europa.eu',
    ];
    foreach ($ec_logos as $langcode => $link) {
      $this->drupalGetInLanguage('<front>', $langcode);
      $this->assertHeaderLogo($link, 'Home - European Commission', 'European Commission logo', 'European Commission');
    }
    // The logo renders consistently on non-front pages too.
    $this->drupalGetInLanguage('user/register', 'en');
    $this->assertHeaderLogo('https://commission.europa.eu/index_en', 'Home - European Commission', 'European Commission logo', 'European Commission');
  }

  /**
   * Sets the theme's component library.
   *
   * @param string $component_library
   *   The component library machine name, either 'ec' or 'eu'.
   */
  protected function setComponentLibrary(string $component_library): void {
    $this->configFactory->getEditable('oe_theme.settings')
      ->set('component_library', $component_library)
      ->save();
  }

  /**
   * Navigates to a path rendered in the given interface language.
   *
   * @param string $path
   *   The internal path or route.
   * @param string $langcode
   *   The language code.
   */
  protected function drupalGetInLanguage(string $path, string $langcode): void {
    $language = ConfigurableLanguage::load($langcode);
    $this->drupalGet($path, ['language' => $language]);
  }

  /**
   * Asserts the header logo attributes.
   *
   * @param string $link
   *   The expected logo link.
   * @param string $label
   *   The expected logo aria-label.
   * @param string $alt
   *   The expected logo image alt attribute.
   * @param string $title
   *   The expected logo picture title attribute.
   */
  protected function assertHeaderLogo(string $link, string $label, string $alt, string $title): void {
    $logo_link = $this->assertSession()->elementExists('css', 'header .ecl-site-header__logo-link');
    $this->assertEquals($link, $logo_link->getAttribute('href'));
    $this->assertEquals($label, $logo_link->getAttribute('aria-label'));
    $picture = $logo_link->find('css', 'picture');
    $this->assertInstanceOf(NodeElement::class, $picture);
    $this->assertEquals($alt, $logo_link->find('css', 'img')->getAttribute('alt'));
    $this->assertEquals($title, $picture->getAttribute('title'));
  }

}
