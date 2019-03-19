<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Test footer block rendering.
 */
class CorporateBlocksFooterTest extends AbstractKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'oe_corporate_blocks',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig(['oe_corporate_blocks']);
  }

  /**
   * Array containing data to merge with config object.
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
  }

}
