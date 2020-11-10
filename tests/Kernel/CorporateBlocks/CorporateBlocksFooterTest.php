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
    $actual = $crawler->filter('footer.ecl-footer-core');
    $this->assertCount(1, $actual);

    // Make sure that footer block rendered correctly.
    $actual = $crawler->filter('footer.ecl-footer-core .ecl-footer-core__container .ecl-footer-core__section');
    $this->assertCount(4, $actual);

    $section = $crawler->filter('footer.ecl-footer-core section.ecl-footer-core__section1');

    $actual = $section->filter('a.ecl-footer-core__title');
    $this->assertEquals($test_data['site_name']['label'], $actual->text());
    $this->assertEquals($test_data['site_name']['href'], $actual->attr('href'));
    $actual = $section->filter('div.ecl-footer-core__description');
    $expected = new FormattableMarkup('This site is managed by the @name', ['@name' => 'ACP–EU Joint Assembly']);
    $this->assertEquals($expected, $actual->text());

    $section = $crawler->filter('footer.ecl-footer-core section.ecl-footer-core__section2');

    $actual = $section->filter('ul li:nth-child(1) > a');
    $this->assertEquals($test_data['class_navigation'][0]['href'], $actual->attr('href'));
    $this->assertEquals($test_data['class_navigation'][0]['label'], $actual->text());

    $actual = $section->filter('ul li:nth-child(2) > a');
    $this->assertEquals($test_data['class_navigation'][1]['href'], $actual->attr('href'));
    $this->assertEquals($test_data['class_navigation'][1]['label'], $actual->text());

    $section = $crawler->filter('footer.ecl-footer-core section.ecl-footer-core__section3');

    $actual = $section->filter('ul li:nth-child(1) > a');
    $this->assertEquals($test_data['service_navigation'][0]['href'], $actual->attr('href'));
    $this->assertEquals($test_data['service_navigation'][0]['label'], $actual->text());

    $actual = $section->filter('ul li:nth-child(2) > a');
    $this->assertEquals($test_data['service_navigation'][1]['href'], $actual->attr('href'));
    $this->assertEquals($test_data['service_navigation'][1]['label'], $actual->text());

    $section = $crawler->filter('footer.ecl-footer-core section.ecl-footer-core__section4');

    $actual = $section->filter('ul li:nth-child(1) > a');
    $this->assertEquals($test_data['legal_navigation'][0]['href'], $actual->attr('href'));
    $this->assertEquals($test_data['legal_navigation'][0]['label'], $actual->text());

    $actual = $section->filter('ul li:nth-child(2) > a');
    $this->assertEquals($test_data['legal_navigation'][1]['href'], $actual->attr('href'));
    $this->assertEquals($test_data['legal_navigation'][1]['label'], $actual->text());
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
    $actual = $crawler->filter('footer.ecl-footer-standardised');
    $this->assertCount(1, $actual);

    // Make sure that footer block rendered correctly.
    $actual = $crawler->filter('footer.ecl-footer-standardised .ecl-footer-standardised__container .ecl-footer-standardised__section');
    $this->assertCount(7, $actual);

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

    $actual = $section->filter('ul li:nth-child(1) > a');
    $this->assertEquals($test_data['service_navigation'][0]['href'], $actual->attr('href'));
    $this->assertEquals($test_data['service_navigation'][0]['label'], $actual->text());

    $actual = $section->filter('ul li:nth-child(2) > a');
    $this->assertEquals($test_data['service_navigation'][1]['href'], $actual->attr('href'));
    $this->assertEquals($test_data['service_navigation'][1]['label'], $actual->text());

    $section = $crawler->filter('footer.ecl-footer-standardised section.ecl-footer-standardised__section9');

    $actual = $section->filter('ul li:nth-child(1) > a');
    $this->assertEquals($test_data['legal_navigation'][0]['href'], $actual->attr('href'));
    $this->assertEquals($test_data['legal_navigation'][0]['label'], $actual->text());

    $actual = $section->filter('ul li:nth-child(2) > a');
    $this->assertEquals($test_data['legal_navigation'][1]['href'], $actual->attr('href'));
    $this->assertEquals($test_data['legal_navigation'][1]['label'], $actual->text());
  }

  /**
   * Test European Union footer core block rendering.
   */
  public function testEuFooterCoreBlockRendering(): void {
    $test_data = [];
    $html = $this->renderCorporateBlocksFooter('eu', $test_data);
    $crawler = new Crawler($html);

    // Make sure that footer block is present.
    $actual = $crawler->filter('footer.ecl-footer-core');
    $this->assertCount(1, $actual);

    // Make sure that footer block rendered correctly.
    $actual = $crawler->filter('footer.ecl-footer-core .ecl-footer-core__container section.ecl-footer-core__section');
    $this->assertCount(6, $actual);

    $section = $crawler->filter('footer.ecl-footer-core section.ecl-footer-core__section1');

    $actual = $section->filter('.ecl-footer-core__description');
    $expected = new FormattableMarkup('This site is managed by the @name', ['@name' => 'ACP–EU Joint Assembly']);
    $this->assertEquals($expected, $actual->html());

    $actual = $section->filter('a img.ecl-footer-core__logo-image-mobile');
    $this->assertCount(1, $actual);
    $actual = $section->filter('a img.ecl-footer-core__logo-image-desktop');
    $this->assertCount(1, $actual);

    $section = $crawler->filter('footer.ecl-footer-core section.ecl-footer-core__section3 .ecl-footer-core__section:nth-child(1)');

    $actual = $section->filter('.ecl-footer-core__title');
    $this->assertEquals('Contact title', $actual->html());

    $actual = $section->filter('.ecl-footer-core__list-item:nth-child(1)');
    $this->assertEquals('<a href="https://europa.eu/contact1" class="ecl-link ecl-link--standalone ecl-footer-core__link">Contact link 1</a>', $actual->html());

    $actual = $section->filter('.ecl-footer-core__list-item:nth-child(2)');
    $this->assertEquals('<a href="https://europa.eu/contact2" class="ecl-link ecl-link--standalone ecl-footer-core__link">Contact link 2</a>', $actual->html());

    $section = $crawler->filter('footer.ecl-footer-core section.ecl-footer-core__section3 .ecl-footer-core__section:nth-child(2)');
    $actual = $section->filter('.ecl-footer-core__title');
    $this->assertEquals('Social media title', $actual->last()->text());

    $actual = $section->filter('.ecl-footer-core__list-item:nth-child(1)');
    $this->assertEquals('<a href="https://europa.eu/social_media1" class="ecl-link ecl-link--standalone ecl-footer-core__link">Social media link 1</a>', $actual->html());

    $section = $crawler->filter('footer.ecl-footer-core section.ecl-footer-core__section3 .ecl-footer-core__section:nth-child(3)');
    $actual = $section->filter('.ecl-footer-core__title');
    $this->assertEquals('Legal links title', $actual->text());

    $actual = $section->filter('.ecl-footer-core__list-item:nth-child(1)');
    $this->assertEquals('<a href="https://europa.eu/legal_links1" class="ecl-link ecl-link--standalone ecl-footer-core__link">Legal link 1</a>', $actual->html());

    $actual = $section->filter('.ecl-footer-core__list-item:nth-child(2)');
    $this->assertEquals('<a href="https://europa.eu/legal_links2" class="ecl-link ecl-link--standalone ecl-footer-core__link">Legal link 2</a>', $actual->html());

    $section = $crawler->filter('footer.ecl-footer-core section.ecl-footer-core__section4');

    $actual = $section->filter('.ecl-footer-core__title');
    $this->assertEquals('Institution links title', $actual->text());

    $actual = $section->filter('.ecl-footer-core__list-item:nth-child(1)');
    $this->assertEquals('<a href="https://europa.eu/institution_links1" class="ecl-link ecl-link--standalone ecl-footer-core__link">Institution link 1</a>', $actual->html());

    $actual = $section->filter('.ecl-footer-core__list-item:nth-child(2)');
    $this->assertEquals('<a href="https://europa.eu/institution_links2" class="ecl-link ecl-link--standalone ecl-footer-core__link">Institution link 2</a>', $actual->html());
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
    $actual = $crawler->filter('footer.ecl-footer-standardised');
    $this->assertCount(1, $actual);

    // Make sure that footer block rendered correctly.
    $actual = $crawler->filter('footer.ecl-footer-standardised .ecl-footer-standardised__container section.ecl-footer-standardised__section');
    $this->assertCount(10, $actual);

    $section = $crawler->filter('footer.ecl-footer-standardised section.ecl-footer-standardised__section1');

    $actual = $section->filter('a.ecl-footer-standardised__title');
    $this->assertEquals('OpenEuropa', $actual->text());
    $expected = \Drupal::urlGenerator()->generateFromRoute('<front>', [], ['absolute' => TRUE]);
    $this->assertEquals($expected, $actual->attr('href'));

    $actual = $section->filter('.ecl-footer-standardised__description');
    $expected = new FormattableMarkup('This site is managed by the @name', ['@name' => 'ACP–EU Joint Assembly']);
    $this->assertEquals($expected, $actual->html());

    $section = $crawler->filter('footer.ecl-footer-standardised section.ecl-footer-standardised__section7');

    $actual = $section->filter('a img.ecl-footer-standardised__logo-image-mobile');
    $this->assertCount(1, $actual);
    $actual = $section->filter('a img.ecl-footer-standardised__logo-image-desktop');
    $this->assertCount(1, $actual);

    $section = $crawler->filter('footer.ecl-footer-standardised section.ecl-footer-standardised__section8 .ecl-footer-standardised__section:nth-child(1)');

    $actual = $section->filter('.ecl-footer-standardised__title');
    $this->assertEquals('Contact title', $actual->html());

    $actual = $section->filter('.ecl-footer-standardised__list-item:nth-child(1)');
    $this->assertEquals('<a href="https://europa.eu/contact1" class="ecl-link ecl-link--standalone ecl-footer-standardised__link">Contact link 1</a>', $actual->html());

    $actual = $section->filter('.ecl-footer-standardised__list-item:nth-child(2)');
    $this->assertEquals('<a href="https://europa.eu/contact2" class="ecl-link ecl-link--standalone ecl-footer-standardised__link">Contact link 2</a>', $actual->html());

    $section = $crawler->filter('footer.ecl-footer-standardised section.ecl-footer-standardised__section8 .ecl-footer-standardised__section:nth-child(2)');
    $actual = $section->filter('.ecl-footer-standardised__title');
    $this->assertEquals('Social media title', $actual->last()->text());

    $actual = $section->filter('.ecl-footer-standardised__list-item:nth-child(1)');
    $this->assertEquals('<a href="https://europa.eu/social_media1" class="ecl-link ecl-link--standalone ecl-footer-standardised__link">Social media link 1</a>', $actual->html());

    $section = $crawler->filter('footer.ecl-footer-standardised section.ecl-footer-standardised__section8 .ecl-footer-standardised__section:nth-child(3)');
    $actual = $section->filter('.ecl-footer-standardised__title');
    $this->assertEquals('Legal links title', $actual->text());

    $actual = $section->filter('.ecl-footer-standardised__list-item:nth-child(1)');
    $this->assertEquals('<a href="https://europa.eu/legal_links1" class="ecl-link ecl-link--standalone ecl-footer-standardised__link">Legal link 1</a>', $actual->html());

    $actual = $section->filter('.ecl-footer-standardised__list-item:nth-child(2)');
    $this->assertEquals('<a href="https://europa.eu/legal_links2" class="ecl-link ecl-link--standalone ecl-footer-standardised__link">Legal link 2</a>', $actual->html());

    $section = $crawler->filter('footer.ecl-footer-standardised section.ecl-footer-standardised__section9');

    $actual = $section->filter('.ecl-footer-standardised__title');
    $this->assertEquals('Institution links title', $actual->text());

    $actual = $section->filter('.ecl-footer-standardised__list-item:nth-child(1)');
    $this->assertEquals('<a href="https://europa.eu/institution_links1" class="ecl-link ecl-link--standalone ecl-footer-standardised__link">Institution link 1</a>', $actual->html());

    $actual = $section->filter('.ecl-footer-standardised__list-item:nth-child(2)');
    $this->assertEquals('<a href="https://europa.eu/institution_links2" class="ecl-link ecl-link--standalone ecl-footer-standardised__link">Institution link 2</a>', $actual->html());
  }

}
