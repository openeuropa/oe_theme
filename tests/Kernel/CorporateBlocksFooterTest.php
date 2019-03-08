<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Class CorporateBlocksFooterTest.
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
   * Test footer block rendering.
   */
  public function testFooterBlockRendering(): void {
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
    $actual = $crawler->filter('footer.ecl-footer div.ecl-footer__corporate-bottom div.ecl-row ul.ecl-footer__list li.ecl-footer__list-item');
    $this->assertCount(7, $actual);
  }

}
