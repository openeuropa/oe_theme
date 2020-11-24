<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Drupal\Tests\BrowserTestBase;
use Symfony\Component\Yaml\Yaml;
use Behat\Mink\Element\NodeElement;
use Drupal\Component\Utility\Html;

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
   * Footer link manager service.
   *
   * @var \Drupal\oe_corporate_blocks\FooterLinkManagerInterface
   */
  protected $linkManager;

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
    $this->linkManager = $this->container->get('oe_corporate_blocks.footer_link_manager');
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
    $this->assertEquals('This site is managed by the ACP–EU Joint Assembly', $actual->getText());

    // Test European Commission footer standardised block rendering.
    $this->configFactory->getEditable('oe_theme.settings')->set('branding', 'standardised')->save();
    $data = $this->getFixtureContent('ec_footer.yml');
    $this->overrideCorporateBlocksFooter('ec', $data);

    $this->drupalGet('<front>');
    $assert = $this->assertSession();

    // Make sure that footer block is present.
    $this->assertFooterPresence('standardised', 11);

    $section = $assert->elementExists('css', 'footer.ecl-footer-standardised section.ecl-footer-standardised__section1');

    $actual = $section->find('css', 'a.ecl-footer-standardised__title');
    $this->assertEquals('EC Site Name', $actual->getText());
    $this->assertEquals('http://web:8080/build/', $actual->getAttribute('href'));

    $actual = $section->find('css', 'div.ecl-footer-standardised__description');
    $this->assertEquals('This site is managed by the ACP–EU Joint Assembly', $actual->getText());

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
    $this->assertEquals('This site is managed by the DG XI – Internal Market', $actual->getText());

    // Add custom footer links.
    $this->createGeneralLink('Custom contact 1', 'contact_us');
    $this->createGeneralLink('Custom about 1', 'about_us');
    $this->createGeneralLink('Custom related 1', 'related_sites');
    $this->createSocialLink('Social 1', 'facebook');
    $this->createSocialLink('Social 2', 'instagram');
    $this->drupalGet('<front>');

    $section = $assert->elementExists('css', 'footer.ecl-footer-standardised section.ecl-footer-standardised__section2');
    $subsection = $assert->elementExists('css', '.ecl-footer-standardised__section:nth-child(1)', $section);

    $actual = $assert->elementExists('css', '.ecl-footer-standardised__title', $subsection);
    $this->assertEquals('Contact us', $actual->getText());

    $actual = $subsection->find('css', 'ul li:nth-child(1) > a');
    $expected = ['label' => 'Custom contact 1', 'href' => 'http://example.com/custom-contact-1'];
    $this->assertListLink($actual, 'standardised', $expected);

    $subsection = $assert->elementExists('css', '.ecl-footer-standardised__section:nth-child(2)', $section);

    $actual = $assert->elementExists('css', '.ecl-footer-standardised__title', $subsection);
    $this->assertEquals('Follow us on', $actual->getText());

    $social_link = $subsection->find('css', 'ul li:nth-child(1) > a');
    $social_label = $subsection->find('css', 'ul li:nth-child(1) > a span.ecl-link__label');
    $expected = ['label' => 'Social 1', 'href' => 'http://example.com/social-1'];
    $this->assertSocialLink($social_label, $social_link, $expected);

    $social_link = $subsection->find('css', 'ul li:nth-child(2) > a');
    $social_label = $subsection->find('css', 'ul li:nth-child(2) > a span.ecl-link__label');
    $expected = ['label' => 'Social 2', 'href' => 'http://example.com/social-2'];
    $this->assertSocialLink($social_label, $social_link, $expected);

    $section = $assert->elementExists('css', 'footer.ecl-footer-standardised section.ecl-footer-standardised__section3');
    $subsection = $assert->elementExists('css', '.ecl-footer-standardised__section:nth-child(1)', $section);

    $actual = $assert->elementExists('css', '.ecl-footer-standardised__title', $subsection);
    $this->assertEquals('About us', $actual->getText());

    $actual = $subsection->find('css', 'ul li:nth-child(1) > a');
    $expected = ['label' => 'Custom about 1', 'href' => 'http://example.com/custom-about-1'];
    $this->assertListLink($actual, 'standardised', $expected);

    $subsection = $assert->elementExists('css', '.ecl-footer-standardised__section:nth-child(2)', $section);

    $actual = $assert->elementExists('css', '.ecl-footer-standardised__title', $subsection);
    $this->assertEquals('Related sites', $actual->getText());

    $actual = $subsection->find('css', 'ul li:nth-child(1) > a');
    $expected = ['label' => 'Custom related 1', 'href' => 'http://example.com/custom-related-1'];
    $this->assertListLink($actual, 'standardised', $expected);

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
    $this->assertEquals('This site is managed by the DG XI – Internal Market', $actual->getText());

    // Assert presence of ecl logo in footer.
    $this->assertEclLogoPresence($section, 'core');

    $section = $assert->elementExists('css', 'footer.ecl-footer-core section.ecl-footer-core__section3');
    $subsection = $assert->elementExists('css', '.ecl-footer-core__section:nth-child(1)', $section);

    $actual = $subsection->find('css', '.ecl-footer-core__title');
    $this->assertEquals('Contact title', $actual->getText());

    $items = $data['contact'];

    foreach ($items as $key => $expected) {
      $index = $key + 1;
      $actual = $subsection->find('css', "ul li:nth-child({$index}) > a");
      $this->assertListLink($actual, 'core', $expected);
    }

    $subsection = $assert->elementExists('css', '.ecl-footer-core__section:nth-child(2)', $section);

    $actual = $subsection->find('css', '.ecl-footer-core__title');
    $this->assertEquals('Social media title', $actual->getText());

    foreach ($data['social_media'] as $key => $expected) {
      $index = $key + 1;
      $actual = $subsection->find('css', "ul li:nth-child({$index}) > a");
      $this->assertListLink($actual, 'core', $expected);
    }

    $subsection = $assert->elementExists('css', '.ecl-footer-core__section:nth-child(3)', $section);

    $actual = $subsection->find('css', '.ecl-footer-core__title');
    $this->assertEquals('Legal links title', $actual->getText());

    $items = $data['legal_links'];

    foreach ($items as $key => $expected) {
      $index = $key + 1;
      $actual = $subsection->find('css', "ul li:nth-child({$index}) > a");
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
    $this->assertEquals('This site is managed by the Directorate-General for Budget', $actual->getText());

    // Test European Union footer standardised block rendering.
    $this->configFactory->getEditable('oe_theme.settings')->set('branding', 'standardised')->save();

    $data = $this->getFixtureContent('eu_footer.yml');
    $this->overrideCorporateBlocksFooter('eu', $data);

    $this->drupalGet('<front>');
    $assert = $this->assertSession();

    // Make sure that footer block is present.
    $this->assertFooterPresence('standardised', 14);

    $section = $assert->elementExists('css', 'footer.ecl-footer-standardised section.ecl-footer-standardised__section1');

    $actual = $section->find('css', 'a.ecl-footer-standardised__title');
    $this->assertEquals('EU Site Name', $actual->getText());
    $this->assertEquals('http://web:8080/build/', $actual->getAttribute('href'));

    $actual = $assert->elementExists('css', 'div.ecl-footer-standardised__description');
    $this->assertEquals('This site is managed by the Directorate-General for Budget', $actual->getText());

    $section = $assert->elementExists('css', 'footer.ecl-footer-standardised section.ecl-footer-standardised__section7');

    $actual = $section->find('css', 'div.ecl-footer-standardised__description');
    $this->assertEquals('Discover more on <a href="https://europa.eu/" class="ecl-link ecl-link--standalone">europa.eu</a>', trim($actual->getHtml()));

    // Assert presence of ecl logo in footer.
    $this->assertEclLogoPresence($section, 'standardised');

    $section = $assert->elementExists('css', 'footer.ecl-footer-standardised section.ecl-footer-standardised__section8');
    $subsection = $assert->elementExists('css', '.ecl-footer-standardised__section:nth-child(1)', $section);

    $actual = $subsection->find('css', '.ecl-footer-standardised__title');
    $this->assertEquals('Contact title', $actual->getText());

    $items = $data['contact'];

    foreach ($items as $key => $expected) {
      $index = $key + 1;
      $actual = $subsection->find('css', "ul li:nth-child({$index}) > a");
      $this->assertListLink($actual, 'standardised', $expected);
    }

    $subsection = $assert->elementExists('css', '.ecl-footer-standardised__section:nth-child(2)', $section);

    $actual = $subsection->find('css', '.ecl-footer-standardised__title');
    $this->assertEquals('Social media title', $actual->getText());

    $items = $data['social_media'];

    foreach ($items as $key => $expected) {
      $index = $key + 1;
      $actual = $subsection->find('css', "ul li:nth-child({$index}) > a");
      $this->assertListLink($actual, 'standardised', $expected);
    }

    $subsection = $assert->elementExists('css', '.ecl-footer-standardised__section:nth-child(3)', $section);

    $actual = $subsection->find('css', '.ecl-footer-standardised__title');
    $this->assertEquals('Legal links title', $actual->getText());

    $items = $data['legal_links'];

    foreach ($items as $key => $expected) {
      $index = $key + 1;
      $actual = $subsection->find('css', "ul li:nth-child({$index}) > a");
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

    // Add new section and more footer links.
    $this->createSection('Section 1', 'section_1');
    $this->createGeneralLink('Custom link 1', 'section_1');
    $this->createSocialLink('Social 3', 'rss');
    $this->drupalGet('<front>');

    $section = $assert->elementExists('css', 'footer.ecl-footer-standardised section.ecl-footer-standardised__section2');
    $subsection = $assert->elementExists('css', '.ecl-footer-standardised__section:nth-child(1)', $section);

    $actual = $assert->elementExists('css', '.ecl-footer-standardised__title', $subsection);
    $this->assertEquals('Contact us', $actual->getText());

    $actual = $subsection->find('css', 'ul li:nth-child(1) > a');
    $expected = ['label' => 'Custom contact 1', 'href' => 'http://example.com/custom-contact-1'];
    $this->assertListLink($actual, 'standardised', $expected);

    $subsection = $assert->elementExists('css', '.ecl-footer-standardised__section:nth-child(2)', $section);

    $actual = $assert->elementExists('css', '.ecl-footer-standardised__title', $subsection);
    $this->assertEquals('Follow us on', $actual->getText());

    $social_link = $subsection->find('css', 'ul li:nth-child(1) > a');
    $social_label = $subsection->find('css', 'ul li:nth-child(1) > a span.ecl-link__label');
    $expected = ['label' => 'Social 1', 'href' => 'http://example.com/social-1'];
    $this->assertSocialLink($social_label, $social_link, $expected);

    $social_link = $subsection->find('css', 'ul li:nth-child(2) > a');
    $social_label = $subsection->find('css', 'ul li:nth-child(2) > a span.ecl-link__label');
    $expected = ['label' => 'Social 2', 'href' => 'http://example.com/social-2'];
    $this->assertSocialLink($social_label, $social_link, $expected);

    $social_link = $subsection->find('css', 'ul li:nth-child(3) > a');
    $social_label = $subsection->find('css', 'ul li:nth-child(3) > a span.ecl-link__label');
    $expected = ['label' => 'Social 3', 'href' => 'http://example.com/social-3'];
    $this->assertSocialLink($social_label, $social_link, $expected);

    $section = $assert->elementExists('css', 'footer.ecl-footer-standardised section.ecl-footer-standardised__section3');
    $subsection = $assert->elementExists('css', '.ecl-footer-standardised__section:nth-child(1)', $section);

    $actual = $assert->elementExists('css', '.ecl-footer-standardised__title', $subsection);
    $this->assertEquals('About us', $actual->getText());

    $actual = $subsection->find('css', 'ul li:nth-child(1) > a');
    $expected = ['label' => 'Custom about 1', 'href' => 'http://example.com/custom-about-1'];
    $this->assertListLink($actual, 'standardised', $expected);

    $subsection = $assert->elementExists('css', '.ecl-footer-standardised__section:nth-child(2)', $section);

    $actual = $assert->elementExists('css', '.ecl-footer-standardised__title', $subsection);
    $this->assertEquals('Related sites', $actual->getText());

    $actual = $subsection->find('css', 'ul li:nth-child(1) > a');
    $expected = ['label' => 'Custom related 1', 'href' => 'http://example.com/custom-related-1'];
    $this->assertListLink($actual, 'standardised', $expected);

    $subsection = $assert->elementExists('css', '.ecl-footer-standardised__section:nth-child(3)', $section);

    $actual = $assert->elementExists('css', '.ecl-footer-standardised__title', $subsection);
    $this->assertEquals('Section 1', $actual->getText());

    $actual = $subsection->find('css', 'ul li:nth-child(1) > a');
    $expected = ['label' => 'Custom link 1', 'href' => 'http://example.com/custom-link-1'];
    $this->assertListLink($actual, 'standardised', $expected);
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
   *   The link element.
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
   * Assert link has correct data and ecl classes.
   *
   * @param \Behat\Mink\Element\NodeElement $label
   *   The link label element.
   * @param \Behat\Mink\Element\NodeElement $link
   *   The link element.
   * @param array $expected
   *   The expected data.
   */
  protected function assertSocialLink(NodeElement $label, NodeElement $link, array $expected): void {
    $this->assertEquals($expected['label'], $label->getText());
    $this->assertEquals($expected['href'], $link->getAttribute('href'));
    $this->assertEquals('ecl-link ecl-link--standalone ecl-link--icon ecl-link--icon-before ecl-footer-standardised__link', $link->getAttribute('class'));
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

  /**
   * Create a general link given its label and section.
   *
   * @param string $label
   *   The link label.
   * @param string $section
   *   The link section.
   */
  protected function createGeneralLink(string $label, string $section = ''): void {
    $id = Html::getId($label);
    $link = \Drupal::entityTypeManager()->getStorage('footer_link_general')->create([
      'id' => $id,
      'label' => $label,
      'url' => 'http://example.com/' . $id,
      'section' => $section,
      'weight' => 0,
    ])->save();
  }

  /**
   * Create a social link given its label and network.
   *
   * @param string $label
   *   The link label.
   * @param string $network
   *   The social network machine name.
   */
  protected function createSocialLink(string $label, string $network): void {
    $id = Html::getId($label);
    $link = \Drupal::entityTypeManager()->getStorage('footer_link_social')->create([
      'id' => $id,
      'label' => $label,
      'url' => 'http://example.com/' . $id,
      'social_network' => $network,
      'weight' => 0,
    ])->save();
  }

  /**
   * Create a footer section given its label and id.
   *
   * @param string $label
   *   The section label.
   * @param string $id
   *   The section id.
   */
  protected function createSection(string $label, string $id): void {
    $section = \Drupal::entityTypeManager()->getStorage('footer_link_section')->create([
      'id' => $id,
      'label' => $label,
      'weight' => 0,
    ])->save();
  }

}
