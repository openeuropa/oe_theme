<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\FunctionalJavascript;

use Behat\Mink\Element\NodeElement;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\oe_theme\Traits\FunctionalJavascriptTrait;
use OpenEuropa\TestingUtilities\Traits\CachedDatabaseInstallTrait;

/**
 * Tests the Javascript behaviours of the theme showcase (demo).
 *
 * @group batch3
 */
class ThemeShowcaseTest extends WebDriverTestBase {

  use CachedDatabaseInstallTrait;
  use FunctionalJavascriptTrait;

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
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function tearDown(): void {
    $this->failOnJavascriptErrors();

    parent::tearDown();
  }

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

    // The demo site uses the standardised branding.
    $this->configFactory->getEditable('oe_theme.settings')->set('branding', 'standardised')->save();
  }

  /**
   * Tests the Javascript behaviours of the theme showcase (demo).
   */
  public function testJsThemeShowcase(): void {
    // Use the European Union component library so the language selector button
    // shows the full language name.
    $this->configFactory->getEditable('oe_theme.settings')->set('component_library', 'eu')->save();

    // Force a desktop viewport: below the ECL desktop breakpoint the navigation
    // collapses into the mobile hamburger menu, where the top-level links are
    // hidden and cannot be hovered. For some reason, it doesn't get set by
    // phpunit at this size.
    $this->getSession()->resizeWindow(1920, 1080);
    $this->drupalGet('<front>');
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();

    // The navigation menu features dropdown menus. The submenu links are not
    // visible by default.
    $this->assertLinkNotVisible('Commission at work');
    $this->assertLinkNotVisible('Democratic change');

    // Hovering over "About" reveals its submenu, but not the priorities one.
    $page->findLink('About')->mouseOver();
    $this->assertLinkVisible('Commission at work');
    $this->assertLinkNotVisible('Democratic change');

    // Hovering over "Priorities" reveals its submenu, but not the about one.
    $page->findLink('Priorities')->mouseOver();
    $this->assertLinkNotVisible('Commission at work');
    $this->assertLinkVisible('Democratic change');

    // The language switcher dialog can be accessed. The overlay and the current
    // language selector are shown, but the overlay is not visible by default.
    $overlay = $assert->elementExists('css', '.ecl-site-header__language-container');
    $this->assertFalse($this->isVisible($overlay));
    $selector = $assert->elementExists('css', '.ecl-site-header__language-selector');
    $this->assertStringContainsString('English', $selector->getText());

    // Open the dialog.
    $this->openLanguageSwitcher();
    $this->assertTrue($this->isVisible($overlay));

    // All the EU official languages are available.
    $languages = [
      'български', 'español', 'čeština', 'dansk', 'Deutsch', 'eesti',
      'ελληνικά', 'English', 'français', 'Gaeilge', 'hrvatski', 'italiano',
      'latviešu', 'lietuvių', 'magyar', 'Malti', 'Nederlands', 'polski',
      'português', 'română', 'slovenčina', 'slovenščina', 'suomi', 'svenska',
    ];
    foreach ($languages as $language) {
      $this->assertNotNull($overlay->findLink($language), sprintf('Language link "%s" not found.', $language));
    }

    // English is the active language.
    $this->assertActiveLanguageLink('English');

    // Selecting another language navigates to its prefixed URL and updates the
    // selector.
    $overlay->findLink('polski')->click();
    $assert->addressMatches('#/pl#');
    $selector = $assert->elementExists('css', '.ecl-site-header__language-selector');
    $this->assertStringContainsString('polski', $selector->getText());

    // Re-opening the dialog shows Polish as the active language.
    $this->openLanguageSwitcher();
    $this->assertActiveLanguageLink('polski');

    // Closing the dialog hides the overlay again.
    $overlay = $assert->elementExists('css', '.ecl-site-header__language-container');
    $this->getSession()->getPage()->pressButton('Close');
    $this->assertFalse($this->isVisible($overlay));
  }

  /**
   * Opens the language switcher dialog.
   */
  protected function openLanguageSwitcher(): void {
    $this->getSession()->getPage()
      ->find('css', '.ecl-site-header a[data-ecl-language-selector]')
      ->click();
  }

  /**
   * Asserts that the given language link is the active one in the dialog.
   *
   * @param string $label
   *   The expected active language label.
   */
  protected function assertActiveLanguageLink(string $label): void {
    $selector = 'li.ecl-site-header__language-item a.ecl-site-header__language-link.ecl-site-header__language-link--active';
    $overlay = $this->assertSession()->elementExists('css', 'div#language-list-overlay');
    $this->assertSession()->elementsCount('css', $selector, 1, $overlay);
    $this->assertSession()->elementTextContains('css', $selector, $label);
  }

  /**
   * Asserts that a link is visually visible.
   *
   * @param string $link
   *   The link text.
   */
  protected function assertLinkVisible(string $link): void {
    $element = $this->getSession()->getPage()->findLink($link);
    $this->assertInstanceOf(NodeElement::class, $element);
    $this->assertTrue($this->isVisible($element), sprintf('Link "%s" is expected to be visible.', $link));
  }

  /**
   * Asserts that a link is not visually visible.
   *
   * @param string $link
   *   The link text.
   */
  protected function assertLinkNotVisible(string $link): void {
    $element = $this->getSession()->getPage()->findLink($link);
    $this->assertInstanceOf(NodeElement::class, $element);
    $this->assertFalse($this->isVisible($element), sprintf('Link "%s" is expected to not be visible.', $link));
  }

  /**
   * Checks whether an element is visually visible.
   *
   * @param \Behat\Mink\Element\NodeElement $element
   *   The element to check.
   *
   * @return bool
   *   TRUE if the element is visible.
   */
  protected function isVisible(NodeElement $element): bool {
    return $this->getSession()->getDriver()->isVisible($element->getXpath());
  }

}
