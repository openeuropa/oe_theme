<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Drupal\Tests\BrowserTestBase;
use Symfony\Component\Yaml\Yaml;
use Behat\Mink\Element\NodeElement;
use Drupal\Component\Utility\Html;

/**
 * Test footer block rendering.
 *
 * @group batch3
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
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Enable and set OpenEuropa Theme as default.
    $this->container->get('theme_installer')->install(['oe_theme']);
    $this->config('system.theme')->set('default', 'oe_theme')->save();
    $this->container->set('theme.registry', NULL);

    // Rebuild the ui_pattern definitions to collect the ones provided by
    // oe_theme itself.
    $this->container->get('plugin.manager.ui_patterns')->clearCachedDefinitions();
    $this->configFactory = $this->container->get('config.factory');
    $this->linkManager = $this->container->get('oe_corporate_blocks.footer_link_manager');

    // Set the internal domain config to detect external links properly.
    $this->config('oe_theme_helper.internal_domains')->set('internal_domain', '/(^|^[^:]+:\/\/|[^\.]+\.)europa\.eu/m')->save();
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

    $section = $assert->elementExists('css', 'footer.ecl-site-footer div.ecl-site-footer__column:nth-child(1) div.ecl-site-footer__section:nth-child(1)');

    $actual = $section->find('css', 'h2.ecl-site-footer__title a');
    $this->assertEquals($data['corporate_site_link']['label'], $actual->getText());
    $this->assertEquals($data['corporate_site_link']['href'], $actual->getAttribute('href'));

    // Site owner is not set yet, lets make sure we don't have a description.
    $assert->elementNotExists('css', 'div.ecl-site-footer__description');

    $section = $assert->elementExists('css', 'footer.ecl-site-footer div.ecl-site-footer__column:nth-child(2) div.ecl-site-footer__section:nth-child(1)');
    $items = $data['class_navigation'];

    foreach ($items as $key => $expected) {
      $index = $key + 1;
      $actual = $section->find('css', "ul li:nth-child({$index}) > a");
      $this->assertListLink($actual, 'core', $expected);
    }

    $section = $assert->elementExists('css', 'footer.ecl-site-footer div.ecl-site-footer__column:nth-child(2) div.ecl-site-footer__section:nth-child(2)');
    $items = $data['service_navigation'];

    foreach ($items as $key => $expected) {
      $index = $key + 1;
      $actual = $section->find('css', "ul li:nth-child({$index}) > a");
      $this->assertListLink($actual, 'core', $expected);
    }

    $section = $assert->elementExists('css', 'footer.ecl-site-footer div.ecl-site-footer__column:nth-child(2) div.ecl-site-footer__section:nth-child(3)');
    $items = $data['legal_navigation'];

    foreach ($items as $key => $expected) {
      $index = $key + 1;
      $actual = $section->find('css', "ul li:nth-child({$index}) > a");
      $this->assertListLink($actual, 'core', $expected);
    }

    // Update settings, assert footer changed.
    $this->updateSiteSettings('http://publications.europa.eu/resource/authority/corporate-body/ACP-EU_JA', 'EC Site Name');
    $this->drupalGet('<front>');

    $actual = $assert->elementExists('css', 'div.ecl-site-footer__description');
    $this->assertEquals('This site is managed by the ACP–EU Joint Assembly', $actual->getText());

    // Test European Commission footer standardised block rendering.
    $this->configFactory->getEditable('oe_theme.settings')->set('branding', 'standardised')->save();
    $data = $this->getFixtureContent('ec_footer.yml');
    $this->overrideCorporateBlocksFooter('ec', $data);

    $this->drupalGet('<front>');
    $assert = $this->assertSession();

    // Make sure that footer block is present.
    $this->assertFooterPresence('standardised', 4);

    $section = $assert->elementExists('css', 'footer.ecl-site-footer div.ecl-site-footer__row:nth-child(1) div.ecl-site-footer__column:nth-child(1) div.ecl-site-footer__section:nth-child(1)');

    $actual = $section->find('css', 'h2.ecl-site-footer__title a');
    $this->assertEquals('EC Site Name', $actual->getText());
    $this->assertEquals('http://web:8080/build/', $actual->getAttribute('href'));

    $actual = $section->find('css', 'div.ecl-site-footer__description');
    $this->assertEquals('This site is managed by the ACP–EU Joint Assembly', $actual->getText());

    $section = $assert->elementExists('css', 'footer.ecl-site-footer div.ecl-site-footer__row:nth-child(2) div.ecl-site-footer__column:nth-child(1) div.ecl-site-footer__section:nth-child(1)');
    $actual = $section->find('css', 'h2.ecl-site-footer__title a');
    $this->assertEquals($data['corporate_site_link']['label'], $actual->getText());
    $this->assertEquals($data['corporate_site_link']['href'], $actual->getAttribute('href'));

    $section = $assert->elementExists('css', 'footer.ecl-site-footer div.ecl-site-footer__row:nth-child(2) div.ecl-site-footer__column:nth-child(2) div.ecl-site-footer__section:nth-child(1)');
    $items = $data['service_navigation'];

    foreach ($items as $key => $expected) {
      $index = $key + 1;
      $actual = $section->find('css', "ul li:nth-child({$index}) > a");
      $this->assertListLink($actual, 'standardised', $expected);
    }

    $section = $assert->elementExists('css', 'footer.ecl-site-footer div.ecl-site-footer__row:nth-child(2) div.ecl-site-footer__column:nth-child(3) div.ecl-site-footer__section:nth-child(1)');
    $items = $data['legal_navigation'];

    foreach ($items as $key => $expected) {
      $index = $key + 1;
      $actual = $section->find('css', "ul li:nth-child({$index}) > a");
      $this->assertListLink($actual, 'standardised', $expected);
    }

    // Update settings, assert footer changed.
    $this->updateSiteSettings('http://publications.europa.eu/resource/authority/corporate-body/DG11', 'EC Standardised Site Name');
    $this->drupalGet('<front>');

    $section = $assert->elementExists('css', 'footer.ecl-site-footer div.ecl-site-footer__row:nth-child(1) div.ecl-site-footer__column:nth-child(1) div.ecl-site-footer__section:nth-child(1)');

    $actual = $section->find('css', 'h2.ecl-site-footer__title a');
    $this->assertEquals('EC Standardised Site Name', $actual->getText());
    $this->assertEquals('http://web:8080/build/', $actual->getAttribute('href'));

    $actual = $section->find('css', 'div.ecl-site-footer__description');
    $this->assertEquals('This site is managed by the DG XI – Internal Market', $actual->getText());

    // Test European Union footer core block rendering.
    $this->configFactory->getEditable('oe_theme.settings')->set('component_library', 'eu')->save();
    $this->configFactory->getEditable('oe_theme.settings')->set('branding', 'core')->save();

    $data = $this->getFixtureContent('eu_footer.yml');
    $this->overrideCorporateBlocksFooter('eu', $data);

    $this->drupalGet('<front>');
    $assert = $this->assertSession();

    // Make sure that footer block is present.
    $this->assertFooterPresence('core', 5);

    $section = $assert->elementExists('css', 'footer.ecl-site-footer div.ecl-site-footer__column:nth-child(1) div.ecl-site-footer__section:nth-child(1)');

    $actual = $assert->elementExists('css', 'div.ecl-site-footer__description');
    $this->assertEquals('This site is managed by the European Commission, DG XI – Internal Market', $actual->getText());

    // Assert presence of ecl logo in footer.
    $this->assertEclLogoPresence($section, 'core');

    $column = $assert->elementExists('css', 'footer.ecl-site-footer div.ecl-site-footer__column:nth-child(2)');
    $subsection = $assert->elementExists('css', '.ecl-site-footer__section:nth-child(1)', $column);

    $actual = $subsection->find('css', '.ecl-site-footer__title');
    $this->assertEquals('Contact title', $actual->getText());

    $items = $data['contact'];

    foreach ($items as $key => $expected) {
      $index = $key + 1;
      $actual = $subsection->find('css', "ul li:nth-child({$index}) > a");
      $this->assertListLink($actual, 'core', $expected);
    }

    $subsection = $assert->elementExists('css', '.ecl-site-footer__section:nth-child(2)', $column);

    $actual = $subsection->find('css', '.ecl-site-footer__title');
    $this->assertEquals('Social media title', $actual->getText());

    foreach ($data['social_media'] as $key => $expected) {
      $index = $key + 1;
      $actual = $subsection->find('css', "ul li:nth-child({$index}) > a");
      $this->assertListLink($actual, 'core', $expected);
    }

    $subsection = $assert->elementExists('css', '.ecl-site-footer__section:nth-child(3)', $column);

    $actual = $subsection->find('css', '.ecl-site-footer__title');
    $this->assertEquals('Legal links title', $actual->getText());

    $items = $data['legal_links'];

    foreach ($items as $key => $expected) {
      $index = $key + 1;
      $actual = $subsection->find('css', "ul li:nth-child({$index}) > a");
      $this->assertListLink($actual, 'core', $expected);
    }

    $section = $assert->elementExists('css', 'footer.ecl-site-footer div.ecl-site-footer__column:nth-child(3) div.ecl-site-footer__section:nth-child(1)');

    $actual = $section->find('css', '.ecl-site-footer__title');
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

    $actual = $assert->elementExists('css', 'div.ecl-site-footer__description');
    $this->assertEquals('This site is managed by the European Commission, Directorate-General for Budget', $actual->getText());

    // Test European Union footer standardised block rendering.
    $this->configFactory->getEditable('oe_theme.settings')->set('branding', 'standardised')->save();

    $data = $this->getFixtureContent('eu_footer.yml');
    $this->overrideCorporateBlocksFooter('eu', $data);

    $this->drupalGet('<front>');
    $assert = $this->assertSession();

    // Make sure that footer block is present.
    $this->assertFooterPresence('standardised', 6);

    $section = $assert->elementExists('css', 'footer.ecl-site-footer div.ecl-site-footer__row:nth-child(1) div.ecl-site-footer__column:nth-child(1) div.ecl-site-footer__section:nth-child(1)');

    $actual = $section->find('css', 'h2.ecl-site-footer__title a');
    $this->assertEquals('EU Site Name', $actual->getText());
    $this->assertEquals('http://web:8080/build/', $actual->getAttribute('href'));

    $actual = $assert->elementExists('css', 'div.ecl-site-footer__description');
    $this->assertEquals('This site is managed by the European Commission, Directorate-General for Budget', $actual->getText());

    $section = $assert->elementExists('css', 'footer.ecl-site-footer div.ecl-site-footer__row:nth-child(2) div.ecl-site-footer__column:nth-child(1) div.ecl-site-footer__section:nth-child(1)');

    $actual = $section->find('css', 'div.ecl-site-footer__description');
    $this->assertEquals('Discover more on <a href="https://europa.eu/" class="ecl-link ecl-link--standalone">europa.eu</a>', trim($actual->getHtml()));

    // Assert presence of ecl logo in footer.
    $this->assertEclLogoPresence($section, 'standardised');

    $column = $assert->elementExists('css', 'footer.ecl-site-footer div.ecl-site-footer__row:nth-child(2) div.ecl-site-footer__column:nth-child(2)');
    $subsection = $assert->elementExists('css', '.ecl-site-footer__section:nth-child(1)', $column);

    $actual = $subsection->find('css', '.ecl-site-footer__title');
    $this->assertEquals('Contact title', $actual->getText());

    $items = $data['contact'];

    foreach ($items as $key => $expected) {
      $index = $key + 1;
      $actual = $subsection->find('css', "ul li:nth-child({$index}) > a");
      $this->assertListLink($actual, 'standardised', $expected);
    }

    $subsection = $assert->elementExists('css', '.ecl-site-footer__section:nth-child(2)', $column);

    $actual = $subsection->find('css', '.ecl-site-footer__title');
    $this->assertEquals('Social media title', $actual->getText());

    $items = $data['social_media'];

    foreach ($items as $key => $expected) {
      $index = $key + 1;
      $actual = $subsection->find('css', "ul li:nth-child({$index}) > a");
      $this->assertListLink($actual, 'standardised', $expected);
    }

    $subsection = $assert->elementExists('css', '.ecl-site-footer__section:nth-child(3)', $column);

    $actual = $subsection->find('css', '.ecl-site-footer__title');
    $this->assertEquals('Legal links title', $actual->getText());

    $items = $data['legal_links'];

    foreach ($items as $key => $expected) {
      $index = $key + 1;
      $actual = $subsection->find('css', "ul li:nth-child({$index}) > a");
      $this->assertListLink($actual, 'standardised', $expected);
    }

    $section = $assert->elementExists('css', 'footer.ecl-site-footer div.ecl-site-footer__row:nth-child(2) div.ecl-site-footer__column:nth-child(3) div.ecl-site-footer__section:nth-child(1)');

    $actual = $section->find('css', '.ecl-site-footer__title');
    $this->assertEquals('Institution links title', $actual->getText());

    $items = $data['institution_links'];

    foreach ($items as $key => $expected) {
      $index = $key + 1;
      $actual = $section->find('css', "ul li:nth-child({$index}) > a");
      $this->assertListLink($actual, 'standardised', $expected);
    }

    // Add a few custom footer links, one by one to assert section distribution.
    $this->createGeneralLink('Custom contact 1', 'contact_us');
    $this->drupalGet('<front>');

    // Assert column 2 (centre column) has the item,
    // and column 3 (right column) is empty.
    $column = $assert->elementExists('css', 'footer.ecl-site-footer div.ecl-site-footer__row:nth-child(1) div.ecl-site-footer__column:nth-child(2)');
    $subsection = $assert->elementExists('css', '.ecl-site-footer__section:nth-child(1)', $column);

    $actual = $assert->elementExists('css', '.ecl-site-footer__title', $subsection);
    $this->assertEquals('Contact us', $actual->getText());

    $actual = $subsection->find('css', 'ul li:nth-child(1) > a');
    $expected = [
      'label' => 'Custom contact 1',
      'href' => 'http://example.com/custom-contact-1',
    ];
    $this->assertListLink($actual, 'standardised', $expected, TRUE);
    // We should have the external svg icon present.
    $icon = $actual->find('css', 'svg.ecl-icon.ecl-icon--xs.ecl-link__icon use');
    $this->assertStringContainsString('external', $icon->getAttribute('xlink:href'));

    $assert->elementNotExists('css', 'footer.ecl-site-footer div.ecl-site-footer__row:nth-child(1) div.ecl-site-footer__column:nth-child(3) div.ecl-site-footer__section:nth-child(1)');

    // Assert each column 2 and 3 each have 1 item.
    $this->createGeneralLink('Custom about 1', 'about_us');
    $this->drupalGet('<front>');

    $column = $assert->elementExists('css', 'footer.ecl-site-footer div.ecl-site-footer__row:nth-child(1) div.ecl-site-footer__column:nth-child(2)');
    $subsection = $assert->elementExists('css', '.ecl-site-footer__section:nth-child(1)', $column);

    $actual = $assert->elementExists('css', '.ecl-site-footer__title', $subsection);
    $this->assertEquals('Contact us', $actual->getText());

    $actual = $subsection->find('css', 'ul li:nth-child(1) > a');
    $expected = [
      'label' => 'Custom contact 1',
      'href' => 'http://example.com/custom-contact-1',
    ];
    $this->assertListLink($actual, 'standardised', $expected, TRUE);
    // We should have the external icon present.
    $icon = $actual->find('css', 'svg.ecl-icon.ecl-icon--xs.ecl-link__icon use');
    $this->assertStringContainsString('external', $icon->getAttribute('xlink:href'));

    $column = $assert->elementExists('css', 'footer.ecl-site-footer div.ecl-site-footer__row:nth-child(1) div.ecl-site-footer__column:nth-child(3)');
    $subsection = $assert->elementExists('css', '.ecl-site-footer__section:nth-child(1)', $column);

    $actual = $assert->elementExists('css', '.ecl-site-footer__title', $subsection);
    $this->assertEquals('About us', $actual->getText());

    $actual = $subsection->find('css', 'ul li:nth-child(1) > a');
    $expected = [
      'label' => 'Custom about 1',
      'href' => 'http://example.com/custom-about-1',
    ];
    $this->assertListLink($actual, 'standardised', $expected, TRUE);
    // We should have the external icon present.
    $icon = $actual->find('css', 'svg.ecl-icon.ecl-icon--xs.ecl-link__icon use');
    $this->assertStringContainsString('external', $icon->getAttribute('xlink:href'));

    // Add one more, assert odd goes into column 2, even goes into column 3.
    $this->createGeneralLink('Custom related 1', 'related_sites');
    $this->drupalGet('<front>');

    $column = $assert->elementExists('css', 'footer.ecl-site-footer div.ecl-site-footer__row:nth-child(1) div.ecl-site-footer__column:nth-child(2)');
    $subsection = $assert->elementExists('css', '.ecl-site-footer__section:nth-child(1)', $column);

    $actual = $assert->elementExists('css', '.ecl-site-footer__title', $subsection);
    $this->assertEquals('Contact us', $actual->getText());

    $actual = $subsection->find('css', 'ul li:nth-child(1) > a');
    $expected = [
      'label' => 'Custom contact 1',
      'href' => 'http://example.com/custom-contact-1',
    ];
    $this->assertListLink($actual, 'standardised', $expected, TRUE);
    // We should have the external icon present.
    $icon = $actual->find('css', 'svg.ecl-icon.ecl-icon--xs.ecl-link__icon use');
    $this->assertStringContainsString('external', $icon->getAttribute('xlink:href'));

    $subsection = $assert->elementExists('css', '.ecl-site-footer__section:nth-child(2)', $column);

    $actual = $assert->elementExists('css', '.ecl-site-footer__title', $subsection);
    $this->assertEquals('Related sites', $actual->getText());

    $actual = $subsection->find('css', 'ul li:nth-child(1) > a');
    $expected = [
      'label' => 'Custom related 1',
      'href' => 'http://example.com/custom-related-1',
    ];
    $this->assertListLink($actual, 'standardised', $expected, TRUE);
    // We should have the external icon present.
    $icon = $actual->find('css', 'svg.ecl-icon.ecl-icon--xs.ecl-link__icon use');
    $this->assertStringContainsString('external', $icon->getAttribute('xlink:href'));

    $column = $assert->elementExists('css', 'footer.ecl-site-footer div.ecl-site-footer__row:nth-child(1) div.ecl-site-footer__column:nth-child(3)');
    $subsection = $assert->elementExists('css', '.ecl-site-footer__section:nth-child(1)', $column);

    $actual = $assert->elementExists('css', '.ecl-site-footer__title', $subsection);
    $this->assertEquals('About us', $actual->getText());

    $actual = $subsection->find('css', 'ul li:nth-child(1) > a');
    $expected = [
      'label' => 'Custom about 1',
      'href' => 'http://example.com/custom-about-1',
    ];
    $this->assertListLink($actual, 'standardised', $expected, TRUE);
    // We should have the external icon present.
    $icon = $actual->find('css', 'svg.ecl-icon.ecl-icon--xs.ecl-link__icon use');
    $this->assertStringContainsString('external', $icon->getAttribute('xlink:href'));

    // Add the follow us, assert it goes last, into the centre column.
    // We have 3 sub-sections plus follow, distribution should be 2 and 2.
    $this->createSocialLink('Social 1', 'facebook');
    $this->createSocialLink('Social 2', 'instagram');
    $this->drupalGet('<front>');

    $column = $assert->elementExists('css', 'footer.ecl-site-footer div.ecl-site-footer__row:nth-child(1) div.ecl-site-footer__column:nth-child(2)');
    $subsection = $assert->elementExists('css', '.ecl-site-footer__section:nth-child(1)', $column);

    $actual = $assert->elementExists('css', '.ecl-site-footer__title', $subsection);
    $this->assertEquals('Contact us', $actual->getText());

    $actual = $subsection->find('css', 'ul li:nth-child(1) > a');
    $expected = [
      'label' => 'Custom contact 1',
      'href' => 'http://example.com/custom-contact-1',
    ];
    $this->assertListLink($actual, 'standardised', $expected, TRUE);
    // We should have the external icon present.
    $icon = $actual->find('css', 'svg.ecl-icon.ecl-icon--xs.ecl-link__icon use');
    $this->assertStringContainsString('external', $icon->getAttribute('xlink:href'));

    $subsection = $assert->elementExists('css', '.ecl-site-footer__section:nth-child(2)', $column);

    $actual = $assert->elementExists('css', '.ecl-site-footer__title', $subsection);
    $this->assertEquals('Follow us', $actual->getText());

    $social_link = $subsection->find('css', 'ul li:nth-child(1) > a');
    $social_label = $subsection->find('css', 'ul li:nth-child(1) > a span.ecl-link__label');
    $expected = [
      'label' => 'Social 1',
      'href' => 'http://example.com/social-1',
      'icon_name' => 'facebook',
    ];
    $this->assertSocialLink($social_label, $social_link, $expected);

    $social_link = $subsection->find('css', 'ul li:nth-child(2) > a');
    $social_label = $subsection->find('css', 'ul li:nth-child(2) > a span.ecl-link__label');
    $expected = [
      'label' => 'Social 2',
      'href' => 'http://example.com/social-2',
      'icon_name' => 'instagram',
    ];
    $this->assertSocialLink($social_label, $social_link, $expected);

    $column = $assert->elementExists('css', 'footer.ecl-site-footer div.ecl-site-footer__row:nth-child(1) div.ecl-site-footer__column:nth-child(3)');
    $subsection = $assert->elementExists('css', '.ecl-site-footer__section:nth-child(1)', $column);

    $actual = $assert->elementExists('css', '.ecl-site-footer__title', $subsection);
    $this->assertEquals('About us', $actual->getText());

    $actual = $subsection->find('css', 'ul li:nth-child(1) > a');
    $expected = [
      'label' => 'Custom about 1',
      'href' => 'http://example.com/custom-about-1',
    ];
    $this->assertListLink($actual, 'standardised', $expected, TRUE);
    // We should have the external icon present.
    $icon = $actual->find('css', 'svg.ecl-icon.ecl-icon--xs.ecl-link__icon use');
    $this->assertStringContainsString('external', $icon->getAttribute('xlink:href'));

    // Assert previous last in section 2 moved to the right into section 3.
    $subsection = $assert->elementExists('css', '.ecl-site-footer__section:nth-child(2)', $column);

    $actual = $assert->elementExists('css', '.ecl-site-footer__title', $subsection);
    $this->assertEquals('Related sites', $actual->getText());

    $actual = $subsection->find('css', 'ul li:nth-child(1) > a');
    $expected = [
      'label' => 'Custom related 1',
      'href' => 'http://example.com/custom-related-1',
    ];
    $this->assertListLink($actual, 'standardised', $expected, TRUE);
    // We should have the external icon present.
    $icon = $actual->find('css', 'svg.ecl-icon.ecl-icon--xs.ecl-link__icon use');
    $this->assertStringContainsString('external', $icon->getAttribute('xlink:href'));

    // Assert adding a custom section in backend appears,
    // and is placed in the footer on the right column.
    $this->createSection('section_1', 'Section 1');
    $this->createGeneralLink('Custom link 1', 'section_1');
    $this->drupalGet('<front>');

    $column = $assert->elementExists('css', 'footer.ecl-site-footer div.ecl-site-footer__row:nth-child(1) div.ecl-site-footer__column:nth-child(2)');
    $subsection = $assert->elementExists('css', '.ecl-site-footer__section:nth-child(1)', $column);

    $actual = $assert->elementExists('css', '.ecl-site-footer__title', $subsection);
    $this->assertEquals('Contact us', $actual->getText());

    $actual = $subsection->find('css', 'ul li:nth-child(1) > a');
    $expected = [
      'label' => 'Custom contact 1',
      'href' => 'http://example.com/custom-contact-1',
    ];
    $this->assertListLink($actual, 'standardised', $expected, TRUE);
    // We should have the external icon present.
    $icon = $actual->find('css', 'svg.ecl-icon.ecl-icon--xs.ecl-link__icon use');
    $this->assertStringContainsString('external', $icon->getAttribute('xlink:href'));

    // Since we have an even number there is no switch,
    // assert Related is back in section 2.
    $subsection = $assert->elementExists('css', '.ecl-site-footer__section:nth-child(2)', $column);

    $actual = $assert->elementExists('css', '.ecl-site-footer__title', $subsection);
    $this->assertEquals('Related sites', $actual->getText());

    $actual = $subsection->find('css', 'ul li:nth-child(1) > a');
    $expected = [
      'label' => 'Custom related 1',
      'href' => 'http://example.com/custom-related-1',
    ];
    $this->assertListLink($actual, 'standardised', $expected, TRUE);
    // We should have the external icon present.
    $icon = $actual->find('css', 'svg.ecl-icon.ecl-icon--xs.ecl-link__icon use');
    $this->assertStringContainsString('external', $icon->getAttribute('xlink:href'));

    // Here we assert Follow us is still on the left in section 2.
    $subsection = $assert->elementExists('css', '.ecl-site-footer__section:nth-child(3)', $column);

    $actual = $assert->elementExists('css', '.ecl-site-footer__title', $subsection);
    $this->assertEquals('Follow us', $actual->getText());

    $social_link = $subsection->find('css', 'ul li:nth-child(1) > a');
    $social_label = $subsection->find('css', 'ul li:nth-child(1) > a span.ecl-link__label');
    $expected = [
      'label' => 'Social 1',
      'href' => 'http://example.com/social-1',
      'icon_name' => 'facebook',
    ];
    $this->assertSocialLink($social_label, $social_link, $expected);

    $social_link = $subsection->find('css', 'ul li:nth-child(2) > a');
    $social_label = $subsection->find('css', 'ul li:nth-child(2) > a span.ecl-link__label');
    $expected = [
      'label' => 'Social 2',
      'href' => 'http://example.com/social-2',
      'icon_name' => 'instagram',
    ];
    $this->assertSocialLink($social_label, $social_link, $expected);

    $column = $assert->elementExists('css', 'footer.ecl-site-footer div.ecl-site-footer__row:nth-child(1) div.ecl-site-footer__column:nth-child(3)');
    $subsection = $assert->elementExists('css', '.ecl-site-footer__section:nth-child(1)', $column);

    $actual = $assert->elementExists('css', '.ecl-site-footer__title', $subsection);
    $this->assertEquals('About us', $actual->getText());

    $actual = $subsection->find('css', 'ul li:nth-child(1) > a');
    $expected = [
      'label' => 'Custom about 1',
      'href' => 'http://example.com/custom-about-1',
    ];
    $this->assertListLink($actual, 'standardised', $expected, TRUE);
    // We should have the external icon present.
    $icon = $actual->find('css', 'svg.ecl-icon.ecl-icon--xs.ecl-link__icon use');
    $this->assertStringContainsString('external', $icon->getAttribute('xlink:href'));

    $subsection = $assert->elementExists('css', '.ecl-site-footer__section:nth-child(2)', $column);

    $actual = $assert->elementExists('css', '.ecl-site-footer__title', $subsection);
    $this->assertEquals('Section 1', $actual->getText());

    $actual = $subsection->find('css', 'ul li:nth-child(1) > a');
    $expected = [
      'label' => 'Custom link 1',
      'href' => 'http://example.com/custom-link-1',
    ];
    $this->assertListLink($actual, 'standardised', $expected, TRUE);
    // We should have the external icon present.
    $icon = $actual->find('css', 'svg.ecl-icon.ecl-icon--xs.ecl-link__icon use');
    $this->assertStringContainsString('external', $icon->getAttribute('xlink:href'));

    // Assert updating a general link also changes the footer content.
    $this->updateGeneralLink('custom-link-1', [
      'label' => 'Custom link altered',
      'url' => 'http://example.com/custom-link-altered',
    ]);
    $this->drupalGet('<front>');

    $column = $assert->elementExists('css', 'footer.ecl-site-footer div.ecl-site-footer__row:nth-child(1) div.ecl-site-footer__column:nth-child(3)');
    $subsection = $assert->elementExists('css', '.ecl-site-footer__section:nth-child(2)', $column);

    $actual = $assert->elementExists('css', '.ecl-site-footer__title', $subsection);
    $this->assertEquals('Section 1', $actual->getText());

    $actual = $subsection->find('css', 'ul li:nth-child(1) > a');
    $expected = [
      'label' => 'Custom link altered',
      'href' => 'http://example.com/custom-link-altered',
    ];
    $this->assertListLink($actual, 'standardised', $expected, TRUE);
    // We should have the external icon present.
    $icon = $actual->find('css', 'svg.ecl-icon.ecl-icon--xs.ecl-link__icon use');
    $this->assertStringContainsString('external', $icon->getAttribute('xlink:href'));

    // Update the link with local path, so the external icon won't be present.
    $this->updateGeneralLink('custom-link-1', [
      'label' => 'Custom link altered',
      'url' => '<front>',
    ]);
    $this->drupalGet('<front>');

    $column = $assert->elementExists('css', 'footer.ecl-site-footer div.ecl-site-footer__row:nth-child(1) div.ecl-site-footer__column:nth-child(3)');
    $subsection = $assert->elementExists('css', '.ecl-site-footer__section:nth-child(2)', $column);

    $actual = $assert->elementExists('css', '.ecl-site-footer__title', $subsection);
    $this->assertEquals('Section 1', $actual->getText());

    $actual = $subsection->find('css', 'ul li:nth-child(1) > a');
    $expected = [
      'label' => 'Custom link altered',
      'href' => '/build/',
    ];
    $this->assertListLink($actual, 'standardised', $expected);
    // We should not have any icon present.
    $assert->elementNotExists('css', 'svg.ecl-icon.ecl-icon--xs.ecl-link__icon', $actual);

    // Assert updating a section also changes the footer content.
    $this->updateSection('section_1', [
      'label' => 'Section altered',
      'weight' => -10,
    ]);
    $this->drupalGet('<front>');

    $column = $assert->elementExists('css', 'footer.ecl-site-footer div.ecl-site-footer__row:nth-child(1) div.ecl-site-footer__column:nth-child(3)');
    $subsection = $assert->elementExists('css', '.ecl-site-footer__section:nth-child(1)', $column);

    $actual = $assert->elementExists('css', '.ecl-site-footer__title', $subsection);
    $this->assertEquals('Section altered', $actual->getText());

    $subsection = $assert->elementExists('css', '.ecl-site-footer__section:nth-child(2)', $column);

    $actual = $assert->elementExists('css', '.ecl-site-footer__title', $subsection);
    $this->assertEquals('Related sites', $actual->getText());

    // Change component library to ec, assert other links structure.
    $this->configFactory->getEditable('oe_theme.settings')->set('component_library', 'ec')->save();
    $this->drupalGet('<front>');

    $column = $assert->elementExists('css', 'footer.ecl-site-footer div.ecl-site-footer__row:nth-child(1) div.ecl-site-footer__column:nth-child(2)');
    $subsection = $assert->elementExists('css', '.ecl-site-footer__section:nth-child(1)', $column);

    $actual = $assert->elementExists('css', '.ecl-site-footer__title', $subsection);
    $this->assertEquals('Contact us', $actual->getText());

    $actual = $subsection->find('css', 'ul li:nth-child(1) > a');
    $expected = [
      'label' => 'Custom contact 1',
      'href' => 'http://example.com/custom-contact-1',
    ];
    $this->assertListLink($actual, 'standardised', $expected, TRUE);
    // We should have the external icon present.
    $icon = $actual->find('css', 'svg.ecl-icon.ecl-icon--xs.ecl-link__icon use');
    $this->assertStringContainsString('external', $icon->getAttribute('xlink:href'));

    $subsection = $assert->elementExists('css', '.ecl-site-footer__section:nth-child(2)', $column);

    $actual = $assert->elementExists('css', '.ecl-site-footer__title', $subsection);
    $this->assertEquals('About us', $actual->getText());

    $actual = $subsection->find('css', 'ul li:nth-child(1) > a');
    $expected = [
      'label' => 'Custom about 1',
      'href' => 'http://example.com/custom-about-1',
    ];
    $this->assertListLink($actual, 'standardised', $expected, TRUE);
    // We should have the external icon present.
    $icon = $actual->find('css', 'svg.ecl-icon.ecl-icon--xs.ecl-link__icon use');
    $this->assertStringContainsString('external', $icon->getAttribute('xlink:href'));

    $subsection = $assert->elementExists('css', '.ecl-site-footer__section:nth-child(3)', $column);

    $actual = $assert->elementExists('css', '.ecl-site-footer__title', $subsection);
    $this->assertEquals('Follow us', $actual->getText());

    $social_link = $subsection->find('css', 'ul li:nth-child(1) > a');
    $social_label = $subsection->find('css', 'ul li:nth-child(1) > a span.ecl-link__label');
    $expected = [
      'label' => 'Social 1',
      'href' => 'http://example.com/social-1',
      'icon_name' => 'facebook-negative',
    ];
    $this->assertSocialLink($social_label, $social_link, $expected);

    $social_link = $subsection->find('css', 'ul li:nth-child(2) > a');
    $social_label = $subsection->find('css', 'ul li:nth-child(2) > a span.ecl-link__label');
    $expected = [
      'label' => 'Social 2',
      'href' => 'http://example.com/social-2',
      'icon_name' => 'instagram-negative',
    ];
    $this->assertSocialLink($social_label, $social_link, $expected);

    $column = $assert->elementExists('css', 'footer.ecl-site-footer div.ecl-site-footer__row:nth-child(1) div.ecl-site-footer__column:nth-child(3)');
    $subsection = $assert->elementExists('css', '.ecl-site-footer__section:nth-child(1)', $column);

    $actual = $assert->elementExists('css', '.ecl-site-footer__title', $subsection);
    $this->assertEquals('Section altered', $actual->getText());

    $actual = $subsection->find('css', 'ul li:nth-child(1) > a');
    $expected = [
      'label' => 'Custom link altered',
      'href' => '/build/',
    ];
    $this->assertListLink($actual, 'standardised', $expected);
    // We should not have any icon present.
    $assert->elementNotExists('css', 'svg.ecl-icon.ecl-icon--xs.ecl-link__icon', $actual);

    $subsection = $assert->elementExists('css', '.ecl-site-footer__section:nth-child(2)', $column);

    $actual = $assert->elementExists('css', '.ecl-site-footer__title', $subsection);
    $this->assertEquals('Related sites', $actual->getText());

    $actual = $subsection->find('css', 'ul li:nth-child(1) > a');
    $expected = [
      'label' => 'Custom related 1',
      'href' => 'http://example.com/custom-related-1',
    ];
    $this->assertListLink($actual, 'standardised', $expected, TRUE);
    // We should have the external icon present.
    $icon = $actual->find('css', 'svg.ecl-icon.ecl-icon--xs.ecl-link__icon use');
    $this->assertStringContainsString('external', $icon->getAttribute('xlink:href'));

    // Assert deleting links removes the section.
    $this->deleteEntity('footer_link_general', 'custom-about-1');
    $this->drupalGet('<front>');

    $column = $assert->elementExists('css', 'footer.ecl-site-footer div.ecl-site-footer__row:nth-child(1) div.ecl-site-footer__column:nth-child(2)');
    $subsection = $assert->elementExists('css', '.ecl-site-footer__section:nth-child(1)', $column);

    $actual = $assert->elementExists('css', '.ecl-site-footer__title', $subsection);
    $this->assertEquals('Contact us', $actual->getText());

    $actual = $subsection->find('css', 'ul li:nth-child(1) > a');
    $expected = [
      'label' => 'Custom contact 1',
      'href' => 'http://example.com/custom-contact-1',
    ];
    $this->assertListLink($actual, 'standardised', $expected, TRUE);
    // We should have the external icon present.
    $icon = $actual->find('css', 'svg.ecl-icon.ecl-icon--xs.ecl-link__icon use');
    $this->assertStringContainsString('external', $icon->getAttribute('xlink:href'));

    $subsection = $assert->elementExists('css', '.ecl-site-footer__section:nth-child(2)', $column);

    $actual = $assert->elementExists('css', '.ecl-site-footer__title', $subsection);
    $this->assertNotEquals('About us', $actual->getText());
    $this->assertEquals('Follow us', $actual->getText());

    $social_link = $subsection->find('css', 'ul li:nth-child(1) > a');
    $this->assertNotEquals('Custom about 1', $social_link->getText());
    $social_label = $subsection->find('css', 'ul li:nth-child(1) > a span.ecl-link__label');
    $expected = [
      'label' => 'Social 1',
      'href' => 'http://example.com/social-1',
      'icon_name' => 'facebook-negative',
    ];
    $this->assertSocialLink($social_label, $social_link, $expected);

    $social_link = $subsection->find('css', 'ul li:nth-child(2) > a');
    $social_label = $subsection->find('css', 'ul li:nth-child(2) > a span.ecl-link__label');
    $expected = [
      'label' => 'Social 2',
      'href' => 'http://example.com/social-2',
      'icon_name' => 'instagram-negative',
    ];
    $this->assertSocialLink($social_label, $social_link, $expected);

    // Update the link with europa.eu path, so the external icon won't be
    // present.
    $this->updateGeneralLink('custom-link-1', [
      'label' => 'Custom link altered',
      'url' => 'http://ec.europa.eu/info',
    ]);
    $this->drupalGet('<front>');

    $column = $assert->elementExists('css', 'footer.ecl-site-footer div.ecl-site-footer__row:nth-child(1) div.ecl-site-footer__column:nth-child(3)');
    $subsection = $assert->elementExists('css', '.ecl-site-footer__section:nth-child(1)', $column);
    $actual = $subsection->find('css', 'ul li:nth-child(1) > a');
    $expected = [
      'label' => 'Custom link altered',
      'href' => 'http://ec.europa.eu/info',
    ];
    $this->assertListLink($actual, 'standardised', $expected);
    // We should not have any icon present.
    $assert->elementNotExists('css', 'svg.ecl-icon.ecl-icon--xs.ecl-link__icon', $actual);

    // Assert deleting sections in backend is reflected in the footer.
    $this->deleteEntity('footer_link_section', 'about_us');
    $this->deleteEntity('footer_link_section', 'contact_us');
    $this->deleteEntity('footer_link_section', 'related_sites');
    $this->deleteEntity('footer_link_section', 'section_1');
    $this->deleteEntity('footer_link_social', 'social-1');
    $this->deleteEntity('footer_link_social', 'social-2');
    $this->drupalGet('<front>');

    $assert->elementNotExists('css', 'footer.ecl-site-footer div.ecl-site-footer__row:nth-child(1) div.ecl-site-footer__column:nth-child(2) div.ecl-site-footer__section:nth-child(1)');
    $assert->elementNotExists('css', 'footer.ecl-site-footer div.ecl-site-footer__row:nth-child(1) div.ecl-site-footer__column:nth-child(3) div.ecl-site-footer__section:nth-child(1)');
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
    /** @var \Drupal\Core\Config\Config $config_obj */
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
    $this->assertSession()->elementsCount('css', "footer.ecl-site-footer .ecl-site-footer__container .ecl-site-footer__section", $expected_section_count);
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
   * @param bool $icon
   *   If the link should have an icon, defaults to FALSE.
   */
  protected function assertListLink(NodeElement $actual, string $branding, array $expected, bool $icon = FALSE): void {
    $this->assertEquals($expected['label'], $actual->getText());
    $this->assertEquals($expected['href'], $actual->getAttribute('href'));
    $icon_class = $icon ? 'ecl-link--icon ecl-link--icon-after ' : '';
    $this->assertEquals("ecl-link ecl-link--standalone {$icon_class}ecl-site-footer__link", $actual->getAttribute('class'));
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
    $icon = $link->find('css', 'svg.ecl-icon.ecl-icon--xs.ecl-link__icon use');
    $this->assertStringContainsString('icons-social-media.svg#' . $expected['icon_name'], $icon->getAttribute('xlink:href'));
    $this->assertEquals('ecl-link ecl-link--standalone ecl-link--icon ecl-link--icon-before ecl-site-footer__link', $link->getAttribute('class'));
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
    $this->assertSession()->elementsCount('css', "a img.ecl-site-footer__logo-image-mobile", 1, $section);
    $this->assertSession()->elementsCount('css', "a img.ecl-site-footer__logo-image-desktop", 1, $section);
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
    \Drupal::entityTypeManager()->getStorage('footer_link_general')->create([
      'id' => $id,
      'label' => $label,
      'url' => 'http://example.com/' . $id,
      'section' => $section,
      'weight' => 0,
    ])->save();
  }

  /**
   * Update a general link given it's id.
   *
   * @param string $id
   *   The link id.
   * @param array $data
   *   The link values.
   */
  protected function updateGeneralLink(string $id, array $data): void {
    $link = \Drupal::entityTypeManager()->getStorage('footer_link_general')->load($id);

    foreach ($data as $key => $value) {
      $link->set($key, $value);
    }

    $link->save();
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
    \Drupal::entityTypeManager()->getStorage('footer_link_social')->create([
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
   * @param string $id
   *   The section id.
   * @param string $label
   *   The section label.
   */
  protected function createSection(string $id, string $label): void {
    \Drupal::entityTypeManager()->getStorage('footer_link_section')->create([
      'id' => $id,
      'label' => $label,
      'weight' => 0,
    ])->save();
  }

  /**
   * Update a footer section given its id.
   *
   * @param string $id
   *   The section id.
   * @param array $data
   *   The section values.
   */
  protected function updateSection(string $id, array $data): void {
    $section = \Drupal::entityTypeManager()->getStorage('footer_link_section')->load($id);

    foreach ($data as $key => $value) {
      $section->set($key, $value);
    }

    $section->save();
  }

  /**
   * Delete a entity given its type and id.
   *
   * @param string $type
   *   The entity type.
   * @param string $id
   *   The section id.
   */
  protected function deleteEntity(string $type, string $id): void {
    $entity = \Drupal::entityTypeManager()->getStorage($type)->load($id);
    $entity->delete();
  }

}
