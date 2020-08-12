<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\CorporateBlocks;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Test footer block rendering.
 */
class CorporateBlocksFooterTest extends CorporateBlocksTestBase {

  /**
   * Test European Commission footer block rendering.
   */
  public function testEcFooterBlockRendering(): void {
    // Override config "oe_corporate_blocks.ec_data.footer" with test.
    /* @var $config_obj \Drupal\Core\Config\Config */
    $config_obj = \Drupal::service('config.factory')->getEditable('oe_corporate_blocks.ec_data.footer');
    $test_data = $this->getFixtureContent('ec_footer.yml');
    $config_obj->setData($test_data);
    $config_obj->save();

    // Setup and render footer block.
    $config = [
      'id' => 'oe_corporate_blocks_ec_footer',
      'label' => 'OpenEuropa footer block',
      'provider' => 'oe_corporate_blocks',
      'label_display' => '0',
    ];

    $render = $this->buildBlock('oe_corporate_blocks_ec_footer', $config);

    $html = (string) $this->container->get('renderer')->renderRoot($render);
    $crawler = new Crawler($html);

    // Make sure that footer block is present.
    $actual = $crawler->filter('footer.ecl-footer-core');
    $this->assertCount(1, $actual);

    // Make sure that footer block rendered correctly.
    $actual = $crawler->filter('footer.ecl-footer-core .ecl-footer-core__container .ecl-footer-core__section');
    $this->assertCount(4, $actual);

    $first_column = $crawler->filter('footer.ecl-footer-core .ecl-footer-core__container .ecl-footer-core__section:nth-child(1)');

    $actual = $first_column->filter('a.ecl-footer-core__title');
    $this->assertEquals($test_data['site_name']['label'], trim($actual->text()));
    $this->assertEquals($test_data['site_name']['href'], trim($actual->attr('href')));
    $actual = $first_column->filter('div.ecl-footer-core__description');
    $this->assertEquals($test_data['content_owner_details'], trim($actual->text()));

    $second_column = $crawler->filter('footer.ecl-footer-core .ecl-footer-core__container .ecl-footer-core__section:nth-child(2)');

    $actual = $second_column->filter('ul li:nth-child(1) > a');
    $this->assertEquals($test_data['class_navigation'][0]['href'], $actual->attr('href'));
    $this->assertEquals($test_data['class_navigation'][0]['label'], $actual->text());

    $actual = $second_column->filter('ul li:nth-child(2) > a');
    $this->assertEquals($test_data['class_navigation'][1]['href'], $actual->attr('href'));
    $this->assertEquals($test_data['class_navigation'][1]['label'], $actual->text());

    $third_column = $crawler->filter('footer.ecl-footer-core .ecl-footer-core__container .ecl-footer-core__section:nth-child(3)');

    $actual = $third_column->filter('ul li:nth-child(1) > a');
    $this->assertEquals($test_data['service_navigation'][0]['href'], $actual->attr('href'));
    $this->assertEquals($test_data['service_navigation'][0]['label'], trim($actual->text()));

    $actual = $third_column->filter('ul li:nth-child(2) > a');
    $this->assertEquals($test_data['service_navigation'][1]['href'], $actual->attr('href'));
    $this->assertEquals($test_data['service_navigation'][1]['label'], trim($actual->text()));

    $forth_column = $crawler->filter('footer.ecl-footer-core .ecl-footer-core__container .ecl-footer-core__section:nth-child(4)');

    $actual = $forth_column->filter('ul li:nth-child(1) > a');
    $this->assertEquals($test_data['legal_navigation'][0]['href'], $actual->attr('href'));
    $this->assertEquals($test_data['legal_navigation'][0]['label'], trim($actual->text()));

    $actual = $forth_column->filter('ul li:nth-child(2) > a');
    $this->assertEquals($test_data['legal_navigation'][1]['href'], $actual->attr('href'));
    $this->assertEquals($test_data['legal_navigation'][1]['label'], trim($actual->text()));
  }

  /**
   * Test European Union footer block rendering.
   */
  public function testEuFooterBlockRendering(): void {
    // Override config "oe_corporate_blocks.eu_data.footer" with test data.
    /* @var $config_obj \Drupal\Core\Config\Config */
    $config_obj = \Drupal::service('config.factory')->getEditable('oe_corporate_blocks.eu_data.footer');
    $test_data = $this->getFixtureContent('eu_footer.yml');
    $config_obj->setData($test_data);
    $config_obj->save();

    // Setup and render footer block.
    $config = [
      'id' => 'oe_corporate_blocks_eu_footer',
      'label' => 'OpenEuropa footer block',
      'provider' => 'oe_corporate_blocks',
      'label_display' => '0',
    ];

    $render = $this->buildBlock('oe_corporate_blocks_eu_footer', $config);

    $html = (string) $this->container->get('renderer')->renderRoot($render);
    $crawler = new Crawler($html);

    // Make sure that footer block is present.
    $actual = $crawler->filter('footer.ecl-footer-core');
    $this->assertCount(1, $actual);

    // Make sure that footer block rendered correctly.
    $actual = $crawler->filter('footer.ecl-footer-core .ecl-footer-core__container section.ecl-footer-core__section');
    $this->assertCount(6, $actual);

    $first_column = $crawler->filter('footer.ecl-footer-core .ecl-footer-core__container section.ecl-footer-core__section:nth-child(1)');

    $actual = $first_column->filter('.ecl-footer-core__description');
    $this->assertEquals('Content owner details', trim($actual->html()));

    $actual = $first_column->filter('a img');
    $this->assertCount(2, $actual);

    $second_column = $crawler->filter('footer.ecl-footer-core .ecl-footer-core__container section.ecl-footer-core__section:nth-child(2) section.ecl-footer-core__section:nth-child(1)');

    $actual = $second_column->filter('.ecl-footer-core__title');
    $this->assertEquals('Contact title', trim($actual->html()));

    $actual = $second_column->filter('.ecl-footer-core__list-item:nth-child(1)');
    $this->assertEquals('<a href="https://europa.eu/contact1" class="ecl-link ecl-link--standalone ecl-footer-core__link">Contact link 1</a>', trim($actual->html()));

    $actual = $second_column->filter('.ecl-footer-core__list-item:nth-child(2)');
    $this->assertEquals('<a href="https://europa.eu/contact2" class="ecl-link ecl-link--standalone ecl-footer-core__link">Contact link 2</a>', trim($actual->html()));

    $third_column = $crawler->filter('footer.ecl-footer-core .ecl-footer-core__container section.ecl-footer-core__section:nth-child(2) section.ecl-footer-core__section:nth-child(2)');
    $actual = $third_column->filter('.ecl-footer-core__title');
    $this->assertEquals('Social media title', trim($actual->last()->text()));

    $actual = $third_column->filter('.ecl-footer-core__list-item:nth-child(1)');
    $this->assertEquals('<a href="https://europa.eu/social_media1" class="ecl-link ecl-link--standalone ecl-footer-core__link">Social media link 1</a>', trim($actual->html()));

    $forth_column = $crawler->filter('footer.ecl-footer-core .ecl-footer-core__container section.ecl-footer-core__section:nth-child(2) section.ecl-footer-core__section:nth-child(3)');
    $actual = $forth_column->filter('.ecl-footer-core__title');
    $this->assertEquals('Legal links title', trim($actual->text()));

    $actual = $forth_column->filter('.ecl-footer-core__list-item:nth-child(1)');
    $this->assertEquals('<a href="https://europa.eu/legal_links1" class="ecl-link ecl-link--standalone ecl-footer-core__link">Legal link 1</a>', trim($actual->html()));

    $actual = $forth_column->filter('.ecl-footer-core__list-item:nth-child(2)');
    $this->assertEquals('<a href="https://europa.eu/legal_links2" class="ecl-link ecl-link--standalone ecl-footer-core__link">Legal link 2</a>', trim($actual->html()));

    $fifth_column = $crawler->filter('footer.ecl-footer-core .ecl-footer-core__container section.ecl-footer-core__section4');

    $actual = $fifth_column->filter('.ecl-footer-core__title');
    $this->assertEquals('Institution links title', trim($actual->text()));

    $actual = $fifth_column->filter('.ecl-footer-core__list-item:nth-child(1)');
    $this->assertEquals('<a href="https://europa.eu/institution_links1" class="ecl-link ecl-link--standalone ecl-footer-core__link">Institution link 1</a>', trim($actual->html()));

    $actual = $fifth_column->filter('.ecl-footer-core__list-item:nth-child(2)');
    $this->assertEquals('<a href="https://europa.eu/institution_links2" class="ecl-link ecl-link--standalone ecl-footer-core__link">Institution link 2</a>', trim($actual->html()));
  }

}
