<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Class SearchFormBlockTest.
 */
class SearchFormBlockTest extends AbstractKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'oe_search',
  ];

  /**
   * Test search form block rendering.
   */
  public function testSearchBlockRendering(): void {
    // Setup and render search form block.
    $config = [
      'id' => 'oe_search',
      'label' => 'OpenEuropa search block',
      'provider' => 'oe_search',
      'label_display' => '0',
    ];

    $render = $this->buildBlock('oe_search', $config);

    $html = (string) $this->container->get('renderer')->renderRoot($render);
    $crawler = new Crawler($html);

    // Make sure that search form block is present.
    $actual = $crawler->filter('form.ecl-search-form.ecl-search-form--');
    $this->assertCount(1, $actual);
    // Make sure that search form block rendered correctly.
    $actual = $crawler->filter('input.ecl-text-input.ecl-search-form__textfield');
    $this->assertCount(1, $actual);
    $actual = $crawler->filter('button.ecl-button.ecl-button--form.ecl-search-form__button');
    $this->assertCount(1, $actual);
  }

}
