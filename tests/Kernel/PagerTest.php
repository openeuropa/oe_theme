<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the rendering of the pager component.
 */
class PagerTest extends AbstractKernelTest {
  /**
   * The count of rendered pagers.
   *
   * @var int
   */
  const PAGERS_COUNT = 3;

  /**
   * The 'previous' page link text.
   *
   * @var string
   */
  const PREVIOUS_PAGE_LINK_TEXT = '‹ Previous';

  /**
   * The 'next' page link text.
   *
   * @var string
   */
  const NEXT_PAGE_LINK_TEXT = 'Next ›';

  /**
   * Test rendering of the pager.
   *
   * @throws \Exception
   */
  public function testRendering(): void {
    // Set up a render array.
    $build['pager_0'] = [
      '#type' => 'pager',
      '#element' => 0,
    ];
    $build['pager_1'] = [
      '#type' => 'pager',
      '#element' => 1,
    ];
    $build['pager_2'] = [
      '#type' => 'pager',
      '#element' => 2,
    ];

    // Initialize pagers with some fake data.
    pager_default_initialize(100, 7, 0);
    pager_default_initialize(100, 7, 1);
    pager_default_initialize(100, 7, 2);

    // Set up the current page numbers for pagers.
    global $pager_page_array;
    $pager_page_array[0] = 6;
    $pager_page_array[1] = 0;
    $pager_page_array[2] = 14;

    // Rendering the markup.
    $html = $this->renderRoot($build);
    $crawler = new Crawler($html);

    $this->assertContains('Go to page', $html);

    // Assert the count of pagers.
    $pagers_count = $crawler->filter('nav.ecl-pager__wrapper')->count();
    $this->assertEquals(self::PAGERS_COUNT, $pagers_count);

    // Check the first pager variant (all elements visible).
    $first_pager = $crawler->filter('nav:first-of-type');
    // Assert that the pager contain 'next' and 'previous' page links.
    $this->assertContains(self::PREVIOUS_PAGE_LINK_TEXT, $first_pager->text());
    $this->assertContains(self::NEXT_PAGE_LINK_TEXT, $first_pager->text());
    // Assert that the pager contain two ellipsis elements.
    $ellipsis_count = $first_pager->filter('li.ecl-pager__item--ellipsis')->count();
    $this->assertEquals(2, $ellipsis_count);
    // Assert the current page number.
    $current_page_number = $first_pager->filter('li.ecl-pager__item--current')->text();
    $this->assertContains('Page 7', $current_page_number);

    // Check the second variant (pager set on the first page).
    $second_pager = $crawler->filter('nav:nth-of-type(2)');
    // Assert that the pager contains only the 'next' page link.
    $this->assertContains(self::NEXT_PAGE_LINK_TEXT, $second_pager->text());
    $this->assertNotContains(self::PREVIOUS_PAGE_LINK_TEXT, $second_pager->text());
    // Assert that the pager contains only one ellipsis element.
    $ellipsis_count = $second_pager->filter('li.ecl-pager__item--ellipsis')->count();
    $this->assertEquals(1, $ellipsis_count);
    // Assert that the pager is set on the first page.
    $first_page_number = $second_pager->filter('li.ecl-pager__item--current')->text();
    $this->assertContains('Page 1', $first_page_number);

    // Check the third variant (pager set on the last page).
    $third_pager = $crawler->filter('nav:nth-of-type(3)');
    // Assert that the pager contains only the 'previous' page link.
    $this->assertContains(self::PREVIOUS_PAGE_LINK_TEXT, $third_pager->text());
    $this->assertNotContains(self::NEXT_PAGE_LINK_TEXT, $third_pager->text());
    // Assert that the pager contains only one ellipsis element.
    $ellipsis_count = $third_pager->filter('li.ecl-pager__item--ellipsis')->count();
    $this->assertEquals(1, $ellipsis_count);
    // Assert that the pager is set on the last page.
    $first_page_number = $third_pager->filter('li.ecl-pager__item--current')->text();
    $this->assertContains('Page 15', $first_page_number);

  }

}
