<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\CorporateBlocks;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Test footer block rendering.
 */
class CorporateBlocksFooterTest extends CorporateBlocksTestBase {

  /**
   * Test footer block rendering.
   */
  public function testFooterBlockRendering(): void {

    // Override config "oe_corporate_blocks.data.footer" with some custom data.
    /* @var $config_obj \Drupal\Core\Config\Config */
    $config_obj = \Drupal::service('config.factory')->getEditable('oe_corporate_blocks.data.footer');
    $test_data = $this->getTestConfigData();
    $config_obj->setData($test_data);
    $config_obj->save();

    // Setup and render footer block.
    $config = [
      'id' => 'oe_footer',
      'label' => 'OpenEuropa footer block',
      'provider' => 'oe_corporate_blocks',
      'label_display' => '0',
    ];

    $render = $this->buildBlock('oe_footer', $config);

    $html = (string) $this->container->get('renderer')->renderRoot($render);
    $crawler = new Crawler($html);

    // Make sure that footer block is present.
    $actual = $crawler->filter('footer.ecl-footer');
    $this->assertCount(1, $actual);

    // Make sure that footer block rendered correctly.
    $actual = $crawler->filter('footer.ecl-footer div.ecl-footer__corporate-top div.ecl-row div.ecl-footer__column');
    $this->assertCount(3, $actual);

    $first_column = $crawler->filter('footer.ecl-footer div.ecl-footer__corporate-top div.ecl-row div.ecl-footer__column:nth-child(1)');

    $actual = $first_column->filter('h2.ecl-footer__column-title');
    $this->assertEquals($test_data['about_ec_title'], trim($actual->text()));

    $actual = $first_column->filter('ul li:nth-child(1) > a');
    $this->assertEquals($test_data['about_ec_links'][0]['href'], $actual->attr('href'));
    $this->assertEquals($test_data['about_ec_links'][0]['label'], $actual->text());

    $actual = $first_column->filter('ul li:nth-child(2) > a');
    $this->assertEquals($test_data['about_ec_links'][1]['href'], $actual->attr('href'));
    $this->assertEquals($test_data['about_ec_links'][1]['label'], $actual->text());

    $second_column = $crawler->filter('footer.ecl-footer div.ecl-footer__corporate-top div.ecl-row div.ecl-footer__column:nth-child(2)');
    $actual = $second_column->filter('h2.ecl-footer__column-title');
    $this->assertEquals($test_data['social_media_title'], trim($actual->text()));

    $actual = $second_column->filter('ul li:nth-child(1) > a');
    $this->assertEquals($test_data['social_media_links'][0]['link']['href'], $actual->attr('href'));
    $this->assertEquals($test_data['social_media_links'][0]['link']['label'], trim($actual->text()));

    $actual = $second_column->filter('ul li:nth-child(2) > a');
    $this->assertEquals($test_data['social_media_links'][1]['link']['href'], $actual->attr('href'));
    $this->assertEquals($test_data['social_media_links'][1]['link']['label'], trim($actual->text()));

    $third_column = $crawler->filter('footer.ecl-footer div.ecl-footer__corporate-top div.ecl-row div.ecl-footer__column:nth-child(3)');
    $actual = $third_column->filter('h2.ecl-footer__column-title');
    $this->assertEquals($test_data['about_eu_title'], trim($actual->text()));

    $actual = $third_column->filter('ul li:nth-child(1) > a');
    $this->assertEquals($test_data['about_eu_links'][0]['href'], $actual->attr('href'));
    $this->assertEquals($test_data['about_eu_links'][0]['label'], trim($actual->text()));

    $actual = $third_column->filter('ul li:nth-child(2) > a');
    $this->assertEquals($test_data['about_eu_links'][1]['href'], $actual->attr('href'));
    $this->assertEquals($test_data['about_eu_links'][1]['label'], trim($actual->text()));

    $third_column = $crawler->filter('footer.ecl-footer div.ecl-footer__corporate-bottom div.ecl-row');

    $actual = $third_column->filter('ul li:nth-child(1) > a');
    $this->assertEquals($test_data['bottom_links'][0]['href'], $actual->attr('href'));
    $this->assertEquals($test_data['bottom_links'][0]['label'], trim($actual->text()));

    $actual = $third_column->filter('ul li:nth-child(2) > a');
    $this->assertEquals($test_data['bottom_links'][1]['href'], $actual->attr('href'));
    $this->assertEquals($test_data['bottom_links'][1]['label'], trim($actual->text()));

    $actual = $crawler->filter('footer.ecl-footer div.ecl-footer__corporate-bottom div.ecl-row ul.ecl-footer__list li.ecl-footer__list-item');
    $this->assertCount(2, $actual);

    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = \Drupal::service('config.factory');
    $custom_footer_data = $this->getTestCustomFooterConfigsData();
    foreach ($custom_footer_data as $config_name => $config_data) {
      $config_factory->getEditable($config_name)->setData($config_data)->save();
    }
    \Drupal::configFactory()->getEditable('system.site')->set('name', 'Site Identity')->save();
    $render = $this->buildBlock('oe_footer', $config);

    $html = (string) $this->container->get('renderer')->renderRoot($render);
    $crawler = new Crawler($html);

    // Make sure that custom footer block is present.
    $custom_footer = $crawler->filter('footer.ecl-footer div.ecl-footer__custom');
    $this->assertCount(1, $custom_footer);

    // Make sure that footer block rendered correctly.
    $custom_footer_columns = $crawler->filter('footer.ecl-footer div.ecl-footer__custom div.ecl-row div.ecl-footer__column');
    $this->assertCount(3, $custom_footer_columns);

    $first_column = $crawler->filter('footer.ecl-footer div.ecl-footer__custom div.ecl-row div.ecl-footer__column:nth-child(1)');

    $first_column_title = $first_column->filter('h2.ecl-footer__column-title');
    $this->assertEquals(\Drupal::configFactory()->getEditable('system.site')->get('name'), trim($first_column_title->text()));

    $second_column = $crawler->filter('footer.ecl-footer div.ecl-footer__custom div.ecl-row div.ecl-footer__column:nth-child(2)');
    $second_column_title = $second_column->filter('p.ecl-footer__custom-label');
    $this->assertEquals('Follow us:', trim($second_column_title->text()));

    $second_column_link1 = $second_column->filter('ul li:nth-child(1) > a');
    $this->assertEquals($custom_footer_data['oe_corporate_blocks.footer_link.social.facebook']['url'], $second_column_link1->attr('href'));
    $this->assertEquals($custom_footer_data['oe_corporate_blocks.footer_link.social.facebook']['label'], trim($second_column_link1->text()));

    $second_column_link2 = $second_column->filter('ul li:nth-child(2) > a');
    $this->assertEquals($custom_footer_data['oe_corporate_blocks.footer_link.social.twitter']['url'], $second_column_link2->attr('href'));
    $this->assertEquals($custom_footer_data['oe_corporate_blocks.footer_link.social.twitter']['label'], trim($second_column_link2->text()));

    $second_column_link3 = $second_column->filter('ul li:nth-child(3) > a');
    $this->assertEquals($custom_footer_data['oe_corporate_blocks.footer_link.social.other_social_media']['url'], $second_column_link3->attr('href'));
    $this->assertEquals($custom_footer_data['oe_corporate_blocks.footer_link.social.other_social_media']['label'], trim($second_column_link3->text()));

    $third_column = $crawler->filter('footer.ecl-footer div.ecl-footer__custom div.ecl-row div.ecl-footer__column:nth-child(3)');

    $third_column_link1 = $third_column->filter('ul li:nth-child(1) > a');
    $this->assertEquals($custom_footer_data['oe_corporate_blocks.footer_link.general.contact']['url'], $third_column_link1->attr('href'));
    $this->assertEquals($custom_footer_data['oe_corporate_blocks.footer_link.general.contact']['label'], trim($third_column_link1->text()));

    $third_column_link2 = $third_column->filter('ul li:nth-child(2) > a');
    $this->assertEquals($custom_footer_data['oe_corporate_blocks.footer_link.general.legal_notice']['url'], $third_column_link2->attr('href'));
    $this->assertEquals($custom_footer_data['oe_corporate_blocks.footer_link.general.legal_notice']['label'], trim($third_column_link2->text()));
  }

  /**
   * {@inheritdoc}
   */
  protected function getTestConfigData(): array {
    return [
      'about_ec_title' => 'First section title',
      'about_ec_links' => [
        [
          'label' => '1st section 1st link',
          'href' => 'http://example.com/1-1.html',
        ],
        [
          'label' => '1st section 2nd link',
          'href' => 'http://example.com/1-2.html',
        ],
      ],
      'social_media_title' => 'Second section title',
      'social_media_links' => [
        [
          'type' => 'social-network',
          'icon' => 'facebook',
          'link' => [
            'label' => '2nd section 1st link',
            'href' => 'http://example.com/2-1.html',
          ],
        ],
        [
          'type' => 'external',
          'link' => [
            'label' => '2nd section 2nd link',
            'href' => 'http://example.com/2-2.html',
          ],
        ],
      ],
      'about_eu_title' => 'Third section title',
      'about_eu_links' => [
        [
          'label' => '3rd section 1st link',
          'href' => 'http://example.com/3-1.html',
        ],
        [
          'label' => '3rd section 2nd link',
          'href' => 'http://example.com/3-2.html',
        ],
      ],
      'bottom_links' => [
        [
          'label' => '4th section 1st link',
          'href' => 'http://example.com/4-1.html',
        ],
        [
          'label' => '4th section 2nd link',
          'href' => 'http://example.com/4-2.html',
        ],
      ],
    ];
  }

  /**
   * Test data for the custom footer.
   */
  protected function getTestCustomFooterConfigsData(): array {
    return [
      'oe_corporate_blocks.footer_link.general.contact' => [
        'langcode' => 'en',
        'status' => TRUE,
        'dependencies' => [],
        'id' => 'contact',
        'label' => 'Custom Contact',
        'url' => 'https://ec.europa.eu/info/contact_en',
        'weight' => -10,
      ],
      'oe_corporate_blocks.footer_link.general.legal_notice' => [
        'langcode' => 'en',
        'status' => TRUE,
        'dependencies' => [],
        'id' => 'legal_notice',
        'label' => 'Custom Contact',
        'url' => 'https://ec.europa.eu/info/legal-notice_en',
        'weight' => -9,
      ],
      'oe_corporate_blocks.footer_link.social.facebook' => [
        'langcode' => 'en',
        'status' => TRUE,
        'dependencies' => [],
        'id' => 'facebook',
        'social_network' => 'facebook',
        'label' => 'Custom Facebook',
        'url' => 'https://www.facebook.com/EuropeanCommission',
        'weight' => -10,
      ],
      'oe_corporate_blocks.footer_link.social.other_social_media' => [
        'langcode' => 'en',
        'status' => TRUE,
        'dependencies' => [],
        'id' => 'other_social_media',
        'social_network' => '',
        'label' => 'Custom Other social media',
        'url' => 'https://europa.eu/european-union/contact/social-networks_en#n:+i:4+e:1+t:+s',
        'weight' => -8,
      ],
      'oe_corporate_blocks.footer_link.social.twitter' => [
        'langcode' => 'en',
        'status' => TRUE,
        'dependencies' => [],
        'id' => 'twitter',
        'social_network' => 'twitter',
        'label' => 'Custom Twitter',
        'url' => 'https://twitter.com/EU_commission',
        'weight' => -9,
      ],
    ];
  }

}
