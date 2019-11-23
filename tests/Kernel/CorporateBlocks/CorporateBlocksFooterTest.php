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
    // Override config "oe_corporate_blocks.data.footer" with test.
    /* @var $config_obj \Drupal\Core\Config\Config */
    $config_obj = \Drupal::service('config.factory')->getEditable('oe_corporate_blocks.data.footer');
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
    $actual = $crawler->filter('footer.ecl-footer');
    $this->assertCount(1, $actual);

    // Make sure that footer block rendered correctly.
    $actual = $crawler->filter('footer.ecl-footer div.ecl-footer__sections div.ecl-row section.ecl-footer__section');
    $this->assertCount(3, $actual);

    $first_column = $crawler->filter('footer.ecl-footer div.ecl-footer__sections div.ecl-row section.ecl-footer__section:nth-child(1)');

    $actual = $first_column->filter('h1.ecl-footer__section-title');
    $this->assertEquals($test_data['about_ec_title'], trim($actual->text()));

    $actual = $first_column->filter('ul li:nth-child(1) > a');
    $this->assertEquals($test_data['about_ec_links'][0]['href'], $actual->attr('href'));
    $this->assertEquals($test_data['about_ec_links'][0]['label'], $actual->text());

    $actual = $first_column->filter('ul li:nth-child(2) > a');
    $this->assertEquals($test_data['about_ec_links'][1]['href'], $actual->attr('href'));
    $this->assertEquals($test_data['about_ec_links'][1]['label'], $actual->text());

    $second_column = $crawler->filter('footer.ecl-footer div.ecl-footer__sections div.ecl-row section.ecl-footer__section:nth-child(2)');
    $actual = $second_column->filter('h1.ecl-footer__section-title');
    $this->assertEquals($test_data['social_media_title'], trim($actual->text()));

    $actual = $second_column->filter('ul li:nth-child(1) > a');
    $this->assertEquals($test_data['social_media_links'][0]['link']['href'], $actual->attr('href'));
    $this->assertEquals(' ' . $test_data['social_media_links'][0]['link']['label'], $actual->text());

    $actual = $second_column->filter('ul li:nth-child(2) > a');
    $this->assertEquals($test_data['social_media_links'][1]['link']['href'], $actual->attr('href'));
    $this->assertEquals($test_data['social_media_links'][1]['link']['label'] . ' ', $actual->text());

    $third_column = $crawler->filter('footer.ecl-footer div.ecl-footer__sections div.ecl-row section.ecl-footer__section:nth-child(3)');
    $actual = $third_column->filter('h1.ecl-footer__section-title');
    $this->assertEquals($test_data['about_eu_title'], trim($actual->text()));

    $actual = $third_column->filter('ul li:nth-child(1) > a');
    $this->assertEquals($test_data['about_eu_links'][0]['href'], $actual->attr('href'));
    $this->assertEquals($test_data['about_eu_links'][0]['label'] . ' ', trim($actual->text()));

    $actual = $third_column->filter('ul li:nth-child(2) > a');
    $this->assertEquals($test_data['about_eu_links'][1]['href'], $actual->attr('href'));
    $this->assertEquals($test_data['about_eu_links'][1]['label'] . ' ', trim($actual->text()));

    $third_column = $crawler->filter('footer.ecl-footer div.ecl-footer__common div.ecl-footer__common-container');

    $actual = $third_column->filter('a:nth-child(1)');
    $this->assertEquals($test_data['bottom_links'][0]['href'], $actual->attr('href'));
    $this->assertEquals($test_data['bottom_links'][0]['label'], trim($actual->text()));

    $actual = $third_column->filter('a:nth-child(2)');
    $this->assertEquals($test_data['bottom_links'][1]['href'], $actual->attr('href'));
    $this->assertEquals($test_data['bottom_links'][1]['label'], trim($actual->text()));

    $actual = $crawler->filter('footer.ecl-footer div.ecl-footer__common div.ecl-footer__common-container a.ecl-footer__common-link');
    $this->assertCount(2, $actual);
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
    $actual = $crawler->filter('footer.ecl-footer');
    $this->assertCount(1, $actual);

    // Make sure that footer block rendered correctly.
    $actual = $crawler->filter('footer.ecl-footer div.ecl-footer__sections div.ecl-row section.ecl-footer__section');
    $this->assertCount(2, $actual);

    $first_column = $crawler->filter('footer.ecl-footer div.ecl-footer__sections div.ecl-row section.ecl-footer__section:nth-child(1)');

    $actual = $first_column->filter('h1.ecl-footer__section-title');
    $this->assertEquals('Contact the EU', trim($actual->first()->text()));

    $actual = $first_column->filter('ul.ecl-footer__section-list li.ecl-footer__section-item:nth-child(1)');
    $this->assertEquals('Contact 1 <a class="ecl-footer__section-link ecl-link ecl-link--standalone" href="#">link</a>', trim($actual->html()));

    $actual = $first_column->filter('ul.ecl-footer__section-list li.ecl-footer__section-item:nth-child(2)');
    $this->assertEquals('Contact 2 <a class="ecl-footer__section-link ecl-link ecl-link--standalone" href="#">link</a>', trim($actual->html()));

    $actual = $first_column->filter('h1.ecl-footer__section-title');
    $this->assertEquals('Find an EU social media account', trim($actual->last()->text()));

    $second_column = $crawler->filter('footer.ecl-footer div.ecl-footer__sections div.ecl-row section.ecl-footer__section:nth-child(2)');
    $actual = $second_column->filter('h1.ecl-footer__section-title');
    $this->assertEquals('EU institution', trim($actual->text()));

    $actual = $second_column->filter('ul.ecl-footer__section-list li.ecl-footer__section-item:nth-child(1) > a');
    $this->assertEquals('https://europa.eu/institution_links1', $actual->attr('href'));
    $this->assertEquals('Institution link 1', trim($actual->text()));

    $actual = $second_column->filter('ul.ecl-footer__section-list li.ecl-footer__section-item:nth-child(2) > a');
    $this->assertEquals('https://europa.eu/institution_links2', $actual->attr('href'));
    $this->assertEquals('Institution link 2', trim($actual->text()));

    $common = $crawler->filter('footer.ecl-footer .ecl-footer__common');

    $actual = $common->filter('a:nth-child(1)');
    $this->assertEquals('https://europa.eu/other_links1', $actual->attr('href'));
    $this->assertEquals('Other link 1', trim($actual->text()));

    $actual = $common->filter('a:nth-child(2)');
    $this->assertEquals('https://europa.eu/other_links2', $actual->attr('href'));
    $this->assertEquals('Other link 2', trim($actual->text()));
  }

}
