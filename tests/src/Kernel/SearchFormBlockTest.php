<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\Kernel;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests that the search form is properly displayed.
 *
 * @group batch2
 */
class SearchFormBlockTest extends AbstractKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
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
    $actual = $crawler->filter('form.ecl-search-form');
    $this->assertCount(1, $actual);
    // Make sure that the wrapper element is present with the correct classes.
    $actual = $crawler->filter('div.ecl-form-group.ecl-form-group--text-input');
    $this->assertCount(1, $actual);
    // Make sure that search form block rendered correctly.
    $actual = $crawler->filter('input.ecl-text-input.ecl-search-form__text-input');
    $this->assertCount(1, $actual);
    $actual = $crawler->filter('button.ecl-button.ecl-search-form__button');
    $this->assertCount(1, $actual);
  }

}
