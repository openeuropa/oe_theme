<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\CorporateBlocks;

use Symfony\Component\DomCrawler\Crawler;
use Drupal\Component\Render\FormattableMarkup;

/**
 * Test footer block rendering.
 */
class CorporateBlocksFooterTest extends CorporateBlocksTestBase {

  /**
   * Test European Commission footer core block rendering.
   */
  public function testEcFooterCoreBlockRendering(): void {
    $test_data = [];
    $html = $this->renderCorporateBlocksFooter('ec', $test_data);
    $crawler = new Crawler($html);

    // Make sure that footer block is present.
    $this->assertFooterPresence($crawler, 'core', 4);

    $section = $crawler->filter('footer.ecl-footer-core section.ecl-footer-core__section1');

    $actual = $section->filter('a.ecl-footer-core__title');
    $this->assertEquals($test_data['site_name']['label'], $actual->text());
    $this->assertEquals($test_data['site_name']['href'], $actual->attr('href'));

    $actual = $section->filter('div.ecl-footer-core__description');
    $expected = new FormattableMarkup('This site is managed by the @name', ['@name' => 'ACP–EU Joint Assembly']);
    $this->assertEquals($expected, $actual->text());

    $section = $crawler->filter('footer.ecl-footer-core section.ecl-footer-core__section2');
    $this->assertLinkList($section, $test_data['class_navigation']);

    $section = $crawler->filter('footer.ecl-footer-core section.ecl-footer-core__section3');
    $this->assertLinkList($section, $test_data['service_navigation']);

    $section = $crawler->filter('footer.ecl-footer-core section.ecl-footer-core__section4');
    $this->assertLinkList($section, $test_data['legal_navigation']);
  }

  /**
   * Test European Commission footer standardised block rendering.
   */
  public function testEcFooterStandardisedBlockRendering(): void {
    $this->configFactory->getEditable('oe_theme.settings')->set('branding', 'standardised')->save();
    $test_data = [];
    $html = $this->renderCorporateBlocksFooter('ec', $test_data);
    $crawler = new Crawler($html);

    // Make sure that footer block is present.
    $this->assertFooterPresence($crawler, 'standardised', 7);

    $section = $crawler->filter('footer.ecl-footer-standardised section.ecl-footer-standardised__section1');

    $actual = $section->filter('a.ecl-footer-standardised__title');
    $this->assertEquals('OpenEuropa', $actual->text());
    $expected = \Drupal::urlGenerator()->generateFromRoute('<front>', [], ['absolute' => TRUE]);
    $this->assertEquals($expected, $actual->attr('href'));

    $actual = $section->filter('div.ecl-footer-standardised__description');
    $expected = new FormattableMarkup('This site is managed by the @name', ['@name' => 'ACP–EU Joint Assembly']);
    $this->assertEquals($expected, $actual->text());

    $section = $crawler->filter('footer.ecl-footer-standardised section.ecl-footer-standardised__section7');

    $actual = $section->filter('a.ecl-footer-standardised__title');
    $this->assertEquals($test_data['site_name']['label'], $actual->text());
    $this->assertEquals($test_data['site_name']['href'], $actual->attr('href'));

    $section = $crawler->filter('footer.ecl-footer-standardised section.ecl-footer-standardised__section8');
    $this->assertLinkList($section, $test_data['service_navigation']);

    $section = $crawler->filter('footer.ecl-footer-standardised section.ecl-footer-standardised__section9');
    $this->assertLinkList($section, $test_data['legal_navigation']);
  }

  /**
   * Test European Union footer core block rendering.
   */
  public function testEuFooterCoreBlockRendering(): void {
    $test_data = [];
    $html = $this->renderCorporateBlocksFooter('eu', $test_data);
    $crawler = new Crawler($html);

    // Make sure that footer block is present.
    $this->assertFooterPresence($crawler, 'core', 6);

    $section = $crawler->filter('footer.ecl-footer-core section.ecl-footer-core__section1');

    $actual = $section->filter('.ecl-footer-core__description');
    $expected = new FormattableMarkup('This site is managed by the @name', ['@name' => 'ACP–EU Joint Assembly']);
    $this->assertEquals($expected, $actual->html());

    // Assert presence of ecl logo in footer.
    $this->assertEclLogoPresence($section, 'core');

    $section = $crawler->filter('footer.ecl-footer-core section.ecl-footer-core__section3 .ecl-footer-core__section:nth-child(1)');

    $actual = $section->filter('.ecl-footer-core__title');
    $this->assertEquals('Contact title', $actual->html());

    $this->assertLinkList($section, $test_data['contact']);

    $section = $crawler->filter('footer.ecl-footer-core section.ecl-footer-core__section3 .ecl-footer-core__section:nth-child(2)');

    $actual = $section->filter('.ecl-footer-core__title');
    $this->assertEquals('Social media title', $actual->last()->text());

    $this->assertLinkList($section, $test_data['social_media']);

    $section = $crawler->filter('footer.ecl-footer-core section.ecl-footer-core__section3 .ecl-footer-core__section:nth-child(3)');

    $actual = $section->filter('.ecl-footer-core__title');
    $this->assertEquals('Legal links title', $actual->text());

    $this->assertLinkList($section, $test_data['legal_links']);

    $section = $crawler->filter('footer.ecl-footer-core section.ecl-footer-core__section4');

    $actual = $section->filter('.ecl-footer-core__title');
    $this->assertEquals('Institution links title', $actual->text());

    $this->assertLinkList($section, $test_data['institution_links']);
  }

  /**
   * Test European Union footer standardised block rendering.
   */
  public function testEuFooterStandardisedBlockRendering(): void {
    $this->configFactory->getEditable('oe_theme.settings')->set('branding', 'standardised')->save();
    $test_data = [];
    $html = $this->renderCorporateBlocksFooter('eu', $test_data);
    $crawler = new Crawler($html);

    // Make sure that footer block is present.
    $this->assertFooterPresence($crawler, 'standardised', 10);

    $section = $crawler->filter('footer.ecl-footer-standardised section.ecl-footer-standardised__section1');

    $actual = $section->filter('a.ecl-footer-standardised__title');
    $this->assertEquals('OpenEuropa', $actual->text());
    $expected = \Drupal::urlGenerator()->generateFromRoute('<front>', [], ['absolute' => TRUE]);
    $this->assertEquals($expected, $actual->attr('href'));

    $actual = $section->filter('.ecl-footer-standardised__description');
    $expected = new FormattableMarkup('This site is managed by the @name', ['@name' => 'ACP–EU Joint Assembly']);
    $this->assertEquals($expected, $actual->html());

    $section = $crawler->filter('footer.ecl-footer-standardised section.ecl-footer-standardised__section7');

    // Assert presence of ecl logo in footer.
    $this->assertEclLogoPresence($section, 'standardised');

    $section = $crawler->filter('footer.ecl-footer-standardised section.ecl-footer-standardised__section8 .ecl-footer-standardised__section:nth-child(1)');

    $actual = $section->filter('.ecl-footer-standardised__title');
    $this->assertEquals('Contact title', $actual->html());

    $this->assertLinkList($section, $test_data['contact']);

    $section = $crawler->filter('footer.ecl-footer-standardised section.ecl-footer-standardised__section8 .ecl-footer-standardised__section:nth-child(2)');
    $actual = $section->filter('.ecl-footer-standardised__title');
    $this->assertEquals('Social media title', $actual->last()->text());

    $this->assertLinkList($section, $test_data['social_media']);

    $section = $crawler->filter('footer.ecl-footer-standardised section.ecl-footer-standardised__section8 .ecl-footer-standardised__section:nth-child(3)');
    $actual = $section->filter('.ecl-footer-standardised__title');
    $this->assertEquals('Legal links title', $actual->text());

    $this->assertLinkList($section, $test_data['legal_links']);

    $section = $crawler->filter('footer.ecl-footer-standardised section.ecl-footer-standardised__section9');

    $actual = $section->filter('.ecl-footer-standardised__title');
    $this->assertEquals('Institution links title', $actual->text());

    $this->assertLinkList($section, $test_data['institution_links']);
  }

}
