<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\CorporateBlocks;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Test site switcher block rendering.
 */
class CorporateBlocksSiteSwitcherTest extends CorporateBlocksTestBase {

  /**
   * Test site switcher block rendering.
   */
  public function testSiteSwitcherBlockRendering(): void {

    // Override config "oe_corporate_blocks.data.site_switcher"
    // with some custom data.
    /* @var $config_obj \Drupal\Core\Config\Config */
    $config_obj = \Drupal::service('config.factory')->getEditable('oe_corporate_blocks.data.site_switcher');

    $test_data = $this->getTestConfigData();
    $config_obj->setData($test_data);
    $config_obj->save();

    // Setup and render site switcher block.
    $config = [
      'id' => 'oe_site_switcher',
      'label' => 'OpenEuropa site switcher block',
      'provider' => 'oe_corporate_blocks',
      'label_display' => '0',
    ];

    $render = $this->buildBlock('oe_site_switcher', $config);

    $html = (string) $this->container->get('renderer')->renderRoot($render);
    $crawler = new Crawler($html);

    // Make sure that site-switcher block is present.
    $actual = $crawler->filter('.ecl-site-switcher--header');
    $this->assertCount(1, $actual);

    // Make sure that site switcher block rendered correctly.
    $items = $crawler->filter('.ecl-site-switcher--header ul li');
    $this->assertCount(2, $items);

    $first_link = $crawler->filter('div.ecl-site-switcher.ecl-site-switcher--header > div > ul > li:nth-child(1) > a');
    $this->assertEquals($test_data['political_href'], $first_link->attr('href'));
    $this->assertEquals($test_data['political_label'], $first_link->text());

    $second_link = $crawler->filter('div.ecl-site-switcher.ecl-site-switcher--header > div > ul > li.ecl-site-switcher__option--is-selected > a');
    $this->assertEquals($test_data['info_href'], $second_link->attr('href'));
    $this->assertEquals($test_data['info_label'], $second_link->text());

    // Test rendering on change of active link.
    $config_obj->set('active', 'political');
    $config_obj->save();

    $render = $this->buildBlock('oe_site_switcher', $config);

    $html = (string) $this->container->get('renderer')->renderRoot($render);
    $crawler = new Crawler($html);

    $first_link = $crawler->filter('div.ecl-site-switcher.ecl-site-switcher--header > div > ul > li.ecl-site-switcher__option--is-selected:nth-child(1) > a');
    $this->assertEquals($test_data['political_href'], $first_link->attr('href'));
    $this->assertEquals($test_data['political_label'], $first_link->text());
  }

  /**
   * {@inheritdoc}
   */
  protected function getTestConfigData(): array {
    return [
      'info_label' => 'Info Lable',
      'info_href' => 'https://info.com/domain',
      'political_label' => 'Political label',
      'political_href' => 'https://digit.com/domain',
      'active' => 'info',
    ];
  }

}
