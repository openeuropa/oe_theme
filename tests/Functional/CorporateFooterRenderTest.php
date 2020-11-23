<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Drupal\Tests\BrowserTestBase;
use Symfony\Component\Yaml\Yaml;
use Drupal\Component\Render\FormattableMarkup;
use Behat\Mink\Element\NodeElement;

/**
 * Test footer block rendering.
 */
class CorporateFooterRenderTest extends BrowserTestBase {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'block',
    'oe_theme_helper',
    'oe_corporate_blocks',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Enable and set OpenEuropa Theme as default.
    $this->container->get('theme_installer')->install(['oe_theme']);
    $this->container->get('theme_handler')->setDefault('oe_theme');
    $this->container->set('theme.registry', NULL);

    // Rebuild the ui_pattern definitions to collect the ones provided by
    // oe_theme itself.
    $this->container->get('plugin.manager.ui_patterns')->clearCachedDefinitions();
    $this->configFactory = $this->container->get('config.factory');
  }

  /**
   * Test corporate footer block rendering.
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   * @SuppressWarnings(PHPMD.NPathComplexity)
   */
  public function testCorporateFooterRendering(): void {
    // First test European Commission footer core block rendering.
    $data = $this->getFixtureContent('ec_footer.yml');
    $this->overrideCorporateBlocksFooter('ec', $data);

    $this->drupalGet('<front>');
    $assert = $this->assertSession();

    // Make sure that footer block is present.
    $this->assertFooterPresence('core', 4);

    $section = $assert->elementExists('css', 'footer.ecl-footer-core section.ecl-footer-core__section1');

    $actual = $section->find('css', 'a.ecl-footer-core__title');
    $this->assertEquals($data['corporate_site_link']['label'], $actual->getText());
    $this->assertEquals($data['corporate_site_link']['href'], $actual->getAttribute('href'));

    // Site owner is not set yet, lets make sure we don't have a description.
    $assert->elementNotExists('css', 'div.ecl-footer-core__description');

    $section = $assert->elementExists('css', 'footer.ecl-footer-core section.ecl-footer-core__section2');
    $items = $data['class_navigation'];

    foreach ($items as $key => $expected) {
      $index = $key + 1;
      $actual = $section->find('css', "ul li:nth-child({$index}) > a");
      $this->assertListLink($actual, 'core', $expected);
    }

    $section = $assert->elementExists('css', 'footer.ecl-footer-core section.ecl-footer-core__section3');
    $items = $data['service_navigation'];

    foreach ($items as $key => $expected) {
      $index = $key + 1;
      $actual = $section->find('css', "ul li:nth-child({$index}) > a");
      $this->assertListLink($actual, 'core', $expected);
    }

    $section = $assert->elementExists('css', 'footer.ecl-footer-core section.ecl-footer-core__section4');
    $items = $data['legal_navigation'];

    foreach ($items as $key => $expected) {
      $index = $key + 1;
      $actual = $section->find('css', "ul li:nth-child({$index}) > a");
      $this->assertListLink($actual, 'core', $expected);
    }

    // Update settings, assert footer changed.
    $this->updateSiteSettings('http://publications.europa.eu/resource/authority/corporate-body/ACP-EU_JA', 'EC Site Name');
    $this->drupalGet('<front>');

    $actual = $assert->elementExists('css', 'div.ecl-footer-core__description');
    $expected = new FormattableMarkup('This site is managed by the @name', ['@name' => 'ACP–EU Joint Assembly']);
    $this->assertEquals($expected, $actual->getText());

    // Test European Commission footer standardised block rendering.
    $this->configFactory->getEditable('oe_theme.settings')->set('branding', 'standardised')->save();
    $data = $this->getFixtureContent('ec_footer.yml');
    $this->overrideCorporateBlocksFooter('ec', $data);

    $this->drupalGet('<front>');
    $assert = $this->assertSession();

    // Make sure that footer block is present.
    $this->assertFooterPresence('standardised', 7);

    $section = $assert->elementExists('css', 'footer.ecl-footer-standardised section.ecl-footer-standardised__section1');

    $actual = $section->find('css', 'a.ecl-footer-standardised__title');
    $this->assertEquals('EC Site Name', $actual->getText());
    $this->assertEquals('http://web:8080/build/', $actual->getAttribute('href'));

    $actual = $section->find('css', 'div.ecl-footer-standardised__description');
    $expected = new FormattableMarkup('This site is managed by the @name', ['@name' => 'ACP–EU Joint Assembly']);
    $this->assertEquals($expected, $actual->getText());

    $section = $assert->elementExists('css', 'footer.ecl-footer-standardised section.ecl-footer-standardised__section7');
    $actual = $section->find('css', 'a.ecl-footer-standardised__title');
    $this->assertEquals($data['corporate_site_link']['label'], $actual->getText());
    $this->assertEquals($data['corporate_site_link']['href'], $actual->getAttribute('href'));

    $section = $assert->elementExists('css', 'footer.ecl-footer-standardised section.ecl-footer-standardised__section8');
    $items = $data['service_navigation'];

    foreach ($items as $key => $expected) {
      $index = $key + 1;
      $actual = $section->find('css', "ul li:nth-child({$index}) > a");
      $this->assertListLink($actual, 'standardised', $expected);
    }

    $section = $assert->elementExists('css', 'footer.ecl-footer-standardised section.ecl-footer-standardised__section9');
    $items = $data['legal_navigation'];

    foreach ($items as $key => $expected) {
      $index = $key + 1;
      $actual = $section->find('css', "ul li:nth-child({$index}) > a");
      $this->assertListLink($actual, 'standardised', $expected);
    }

    // Update settings, assert footer changed.
    $this->updateSiteSettings('http://publications.europa.eu/resource/authority/corporate-body/DG11', 'EC Standardised Site Name');
    $this->drupalGet('<front>');

    $section = $assert->elementExists('css', 'footer.ecl-footer-standardised section.ecl-footer-standardised__section1');

    $actual = $section->find('css', 'a.ecl-footer-standardised__title');
    $this->assertEquals('EC Standardised Site Name', $actual->getText());
    $this->assertEquals('http://web:8080/build/', $actual->getAttribute('href'));

    $actual = $section->find('css', 'div.ecl-footer-standardised__description');
    $expected = new FormattableMarkup('This site is managed by the @name', ['@name' => 'DG XI – Internal Market']);
    $this->assertEquals($expected, $actual->getText());

    // Test European Union footer core block rendering.
    $this->configFactory->getEditable('oe_theme.settings')->set('component_library', 'eu')->save();
    $this->configFactory->getEditable('oe_theme.settings')->set('branding', 'core')->save();

    $data = $this->getFixtureContent('eu_footer.yml');
    $this->overrideCorporateBlocksFooter('eu', $data);

    $this->drupalGet('<front>');
    $assert = $this->assertSession();

    // Make sure that footer block is present.
    $this->assertFooterPresence('core', 6);

    $section = $assert->elementExists('css', 'footer.ecl-footer-core section.ecl-footer-core__section1');

    $actual = $assert->elementExists('css', 'div.ecl-footer-core__description');
    $expected = new FormattableMarkup('This site is managed by the @name', ['@name' => 'DG XI – Internal Market']);
    $this->assertEquals($expected, $actual->getText());

    // Assert presence of ecl logo in footer.
    $this->assertEclLogoPresence($section, 'core');

    $section = $assert->elementExists('css', 'footer.ecl-footer-core section.ecl-footer-core__section3 .ecl-footer-core__section:nth-child(1)');

    $actual = $section->find('css', '.ecl-footer-core__title');
    $this->assertEquals('Contact title', $actual->getText());

    $items = $data['contact'];

    foreach ($items as $key => $expected) {
      $index = $key + 1;
      $actual = $section->find('css', "ul li:nth-child({$index}) > a");
      $this->assertListLink($actual, 'core', $expected);
    }

    $section = $assert->elementExists('css', 'footer.ecl-footer-core section.ecl-footer-core__section3 .ecl-footer-core__section:nth-child(2)');

    $actual = $section->find('css', '.ecl-footer-core__title');
    $this->assertEquals('Social media title', $actual->getText());

    $items = $data['social_media'];

    foreach ($items as $key => $expected) {
      $index = $key + 1;
      $actual = $section->find('css', "ul li:nth-child({$index}) > a");
      $this->assertListLink($actual, 'core', $expected);
    }

    $section = $assert->elementExists('css', 'footer.ecl-footer-core section.ecl-footer-core__section3 .ecl-footer-core__section:nth-child(3)');

    $actual = $section->find('css', '.ecl-footer-core__title');
    $this->assertEquals('Legal links title', $actual->getText());

    $items = $data['legal_links'];

    foreach ($items as $key => $expected) {
      $index = $key + 1;
      $actual = $section->find('css', "ul li:nth-child({$index}) > a");
      $this->assertListLink($actual, 'core', $expected);
    }

    $section = $assert->elementExists('css', 'footer.ecl-footer-core section.ecl-footer-core__section4');

    $actual = $section->find('css', '.ecl-footer-core__title');
    $this->assertEquals('Institution links title', $actual->getText());

    $items = $data['institution_links'];

    foreach ($items as $key => $expected) {
      $index = $key + 1;
      $actual = $section->find('css', "ul li:nth-child({$index}) > a");
      $this->assertListLink($actual, 'core', $expected);
    }

    // Update settings, assert footer changed.
    $this->updateSiteSettings('http://publications.europa.eu/resource/authority/corporate-body/BUDG', 'EU Site Name');
    $this->drupalGet('<front>');

    $actual = $assert->elementExists('css', 'div.ecl-footer-core__description');
    $expected = new FormattableMarkup('This site is managed by the @name', ['@name' => 'Directorate-General for Budget']);
    $this->assertEquals($expected, $actual->getText());

    // Test European Union footer standardised block rendering.
    $this->configFactory->getEditable('oe_theme.settings')->set('branding', 'standardised')->save();

    $data = $this->getFixtureContent('eu_footer.yml');
    $this->overrideCorporateBlocksFooter('eu', $data);

    $this->drupalGet('<front>');
    $assert = $this->assertSession();

    // Make sure that footer block is present.
    $this->assertFooterPresence('standardised', 10);

    $section = $assert->elementExists('css', 'footer.ecl-footer-standardised section.ecl-footer-standardised__section1');

    $actual = $section->find('css', 'a.ecl-footer-standardised__title');
    $this->assertEquals('EU Site Name', $actual->getText());
    $this->assertEquals('http://web:8080/build/', $actual->getAttribute('href'));

    $actual = $section->find('css', 'div.ecl-footer-standardised__description');
    $expected = new FormattableMarkup('This site is managed by the @name', ['@name' => 'Directorate-General for Budget']);
    $this->assertEquals($expected, $actual->getText());

    $section = $assert->elementExists('css', 'footer.ecl-footer-standardised section.ecl-footer-standardised__section7');

    // Assert presence of ecl logo in footer.
    $this->assertEclLogoPresence($section, 'standardised');

    $section = $assert->elementExists('css', 'footer.ecl-footer-standardised section.ecl-footer-standardised__section8 .ecl-footer-standardised__section:nth-child(1)');

    $actual = $section->find('css', '.ecl-footer-standardised__title');
    $this->assertEquals('Contact title', $actual->getText());

    $items = $data['contact'];

    foreach ($items as $key => $expected) {
      $index = $key + 1;
      $actual = $section->find('css', "ul li:nth-child({$index}) > a");
      $this->assertListLink($actual, 'standardised', $expected);
    }

    $section = $assert->elementExists('css', 'footer.ecl-footer-standardised section.ecl-footer-standardised__section8 .ecl-footer-standardised__section:nth-child(2)');

    $actual = $section->find('css', '.ecl-footer-standardised__title');
    $this->assertEquals('Social media title', $actual->getText());

    $items = $data['social_media'];

    foreach ($items as $key => $expected) {
      $index = $key + 1;
      $actual = $section->find('css', "ul li:nth-child({$index}) > a");
      $this->assertListLink($actual, 'standardised', $expected);
    }

    $section = $assert->elementExists('css', 'footer.ecl-footer-standardised section.ecl-footer-standardised__section8 .ecl-footer-standardised__section:nth-child(3)');

    $actual = $section->find('css', '.ecl-footer-standardised__title');
    $this->assertEquals('Legal links title', $actual->getText());

    $items = $data['legal_links'];

    foreach ($items as $key => $expected) {
      $index = $key + 1;
      $actual = $section->find('css', "ul li:nth-child({$index}) > a");
      $this->assertListLink($actual, 'standardised', $expected);
    }

    $section = $assert->elementExists('css', 'footer.ecl-footer-standardised section.ecl-footer-standardised__section9');

    $actual = $section->find('css', '.ecl-footer-standardised__title');
    $this->assertEquals('Institution links title', $actual->getText());

    $items = $data['institution_links'];

    foreach ($items as $key => $expected) {
      $index = $key + 1;
      $actual = $section->find('css', "ul li:nth-child({$index}) > a");
      $this->assertListLink($actual, 'standardised', $expected);
    }
  }

  /**
   * Get fixture content.
   *
   * @param string $filepath
   *   File path.
   *
   * @return array
   *   A set of test data.
   */
  protected function getFixtureContent(string $filepath): array {
    return Yaml::parse(file_get_contents(__DIR__ . "/fixtures/{$filepath}"));
  }

  /**
   * Override corporate block footer config with test data.
   *
   * @param string $type
   *   The type of block, ec or eu.
   * @param array $test_data
   *   The test data for config and assertion.
   */
  protected function overrideCorporateBlocksFooter(string $type, array $test_data): void {
    /* @var $config_obj \Drupal\Core\Config\Config */
    $config_obj = $this->configFactory->getEditable("oe_corporate_blocks.{$type}_data.footer");
    $config_obj->setData($test_data);
    $config_obj->save();
  }

  /**
   * Assert footer block is present and has correct number of sections.
   *
   * @param string $branding
   *   Ecl branding, core/standardised.
   * @param int $expected_section_count
   *   The number of expected sections.
   */
  protected function assertFooterPresence(string $branding, int $expected_section_count): void {
    $this->assertSession()->elementExists('css', "footer.ecl-footer-{$branding}");
    $this->assertSession()->elementsCount('css', "footer.ecl-footer-{$branding} .ecl-footer-{$branding}__container .ecl-footer-{$branding}__section", $expected_section_count);
  }

  /**
   * Assert link has correct data and ecl classes.
   *
   * @param \Behat\Mink\Element\NodeElement $actual
   *   The footer section.
   * @param string $branding
   *   Ecl branding, core/standardised.
   * @param array $expected
   *   The expected data.
   */
  protected function assertListLink(NodeElement $actual, string $branding, array $expected): void {
    $this->assertEquals($expected['label'], $actual->getText());
    $this->assertEquals($expected['href'], $actual->getAttribute('href'));
    $this->assertEquals("ecl-link ecl-link--standalone ecl-footer-{$branding}__link", $actual->getAttribute('class'));
  }

  /**
   * Assert presence of ecl logo in footer.
   *
   * @param \Behat\Mink\Element\NodeElement $section
   *   The footer section.
   * @param string $branding
   *   Ecl branding, core/standardised.
   */
  protected function assertEclLogoPresence(NodeElement $section, string $branding): void {
    $this->assertSession()->elementsCount('css', "a img.ecl-footer-{$branding}__logo-image-mobile", 1, $section);
    $this->assertSession()->elementsCount('css', "a img.ecl-footer-{$branding}__logo-image-desktop", 1, $section);
  }

  /**
   * Update the config needed from the site settings form.
   *
   * @param string $site_owner
   *   The site owner.
   * @param string $site_name
   *   The name of the site.
   */
  protected function updateSiteSettings(string $site_owner, string $site_name): void {
    $config = $this->configFactory->getEditable('oe_corporate_site_info.settings');
    $config->set('site_owner', $site_owner);
    $config->save();

    $config = $this->configFactory->getEditable('system.site');
    $config->set('name', $site_name);
    $config->save();
  }

}
