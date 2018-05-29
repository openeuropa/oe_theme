<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Core\Url;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the rendering of the pager component.
 */
class PagerTest extends AbstractKernelTestBase {

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
   * @todo rework
   *
   * @throws \Exception
   */
  public function testMultiplePagers(): void {
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

    // Assert the count of pagers.
    $pagers_count = $crawler->filter('nav.ecl-pager');
    $this->assertCount(3, $pagers_count);

    // Check the first pager variant (all elements visible).
    $first_pager = $crawler->filter('nav:first-of-type');
    // Assert that the pager contain 'next' and 'previous' page links.
    $previous = $first_pager->filter('li.ecl-pager__item--previous');
    $this->assertSpecialPagerElement($previous, TRUE, $this->generatePagerUrl('<none>', 6), 'Go to previous page');
    $this->assertContains(self::PREVIOUS_PAGE_LINK_TEXT, $first_pager->text());
    $next = $first_pager->filter('li.ecl-pager__item--next');
    $this->assertSpecialPagerElement($next, TRUE, $this->generatePagerUrl('<none>', 8), 'Go to next page');
    $this->assertContains(self::NEXT_PAGE_LINK_TEXT, $first_pager->text());
    // Assert that the pager contain two ellipsis elements.
    $ellipsis_count = $first_pager->filter('li.ecl-pager__item--ellipsis');
    $this->assertCount(2, $ellipsis_count);
    // Assert the current page number.
    $current_page_number = $first_pager->filter('li.ecl-pager__item--current')->text();
    $this->assertContains('Page 7', $current_page_number);

    // Check the second variant (pager set on the first page).
    $second_pager = $crawler->filter('nav:nth-of-type(2)');
    // Assert that the pager contains only the 'next' page link.
    $this->assertContains(self::NEXT_PAGE_LINK_TEXT, $second_pager->text());
    $this->assertNotContains(self::PREVIOUS_PAGE_LINK_TEXT, $second_pager->text());

    // Assert that the pager is set on the first page.
    $first_page_number = $second_pager->filter('li.ecl-pager__item--current')->text();
    $this->assertContains('Page 1', $first_page_number);

    // Check the third variant (pager set on the last page).
    $third_pager = $crawler->filter('nav:nth-of-type(3)');
    // Assert that the pager contains only the 'previous' page link.
    $this->assertContains(self::PREVIOUS_PAGE_LINK_TEXT, $third_pager->text());
    $this->assertNotContains(self::NEXT_PAGE_LINK_TEXT, $third_pager->text());

    // Assert that the pager is set on the last page.
    $first_page_number = $third_pager->filter('li.ecl-pager__item--current')->text();
    $this->assertContains('Page 15', $first_page_number);
  }

  /**
   * Tests a single pager rendering.
   *
   * @param int $current_page
   *   The page to set as current.
   * @param int $total_pages
   *   The total number of pages available.
   * @param string $route_name
   *   The route name where the link should point. Defaults to '<none>'.
   *
   * @throws \Exception
   *   Thrown on rendering errors.
   *
   * @dataProvider singlePagerDataProvider
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   * @SuppressWarnings(PHPMD.NPathComplexity)
   */
  public function testSinglePager(int $current_page, int $total_pages, string $route_name = '<none>'): void {
    $build['pager'] = [
      '#type' => 'pager',
      '#route_name' => $route_name,
    ];

    pager_default_initialize($total_pages * 10, 10);

    global $pager_page_array;
    // Normalise the current page array to 0-based.
    $pager_page_array[0] = $current_page - 1;

    $html = $this->renderRoot($build);
    $crawler = new Crawler($html);

    // Assert the presence of the pager.
    $wrapper = $crawler->filter('nav.ecl-pager');
    $this->assertCount(1, $wrapper);

    // Assert that the current page set is correct.
    $current_page_element = $wrapper->filter('li.ecl-pager__item--current');
    $this->assertContains('Page ' . $current_page, $current_page_element->text());

    // By specifications, the following links should be visible:
    // - links to the previous two pages (if applicable);
    // - links to the next two pages (if applicable).
    $min = $current_page - 2;
    $max = $current_page + 2;
    // Re-center the min and max pages values. Also don't test the first and
    // last page links, as they are handled separately.
    $min = $min < 2 ? 2 : $min;
    $max = $max > ($total_pages - 1) ? $total_pages - 1 : $max;

    // Keep track of how many links should be visible.
    $links_count = 0;

    $links = $wrapper->filter('a');
    for ($i = $min; $i <= $max; $i++) {
      // The current page doesn't have a link.
      if ($i === $current_page) {
        continue;
      }

      $url = $this->generatePagerUrl($route_name, $i);
      $pager_item = $links->filter("[href='$url'][title='Go to page $i']");
      // There might be multiple links to the same page, like the previous
      // and the links. It's enough to assert that there is at least one link
      // with the expected url. The assertion on the links count will catch
      // missing links.
      $this->assertNotEmpty($pager_item);
      $links_count++;
    }

    // When the current page is not the first one, a link to the previous page
    // and one to the first page should be shown.
    $show_start_links = $current_page > 1;
    // Adjust the expected links count.
    $links_count += $show_start_links ? 2 : 0;

    $previous = $crawler->filter('li.ecl-pager__item--previous');
    $this->assertSpecialPagerElement($previous, $show_start_links, $this->generatePagerUrl($route_name, $current_page - 1), 'Go to previous page');
    $this->{$show_start_links ? 'assertContains' : 'assertNotContains'}(self::PREVIOUS_PAGE_LINK_TEXT, $crawler->html());

    $first_page = $crawler->filter('li.ecl-pager__item--first');
    $this->assertSpecialPagerElement($first_page, $show_start_links, $this->generatePagerUrl($route_name, 1), 'Go to page 1');

    // When the current page is not the last one, the link to the next page
    // and one to the last page should be shown.
    $show_end_links = $current_page < $total_pages;
    // Adjust the expected links count.
    $links_count += ($show_end_links) ? 2 : 0;

    $next = $crawler->filter('li.ecl-pager__item--next');
    $this->assertSpecialPagerElement($next, $show_end_links, $this->generatePagerUrl($route_name, $current_page + 1), 'Go to next page');
    $this->{$show_end_links ? 'assertContains' : 'assertNotContains'}(self::NEXT_PAGE_LINK_TEXT, $crawler->html());

    $last_page = $crawler->filter('li.ecl-pager__item--last');
    $this->assertSpecialPagerElement($last_page, $show_end_links, $this->generatePagerUrl($route_name, $total_pages), "Go to page $total_pages");

    // Verify that only the needed links have been rendered.
    $this->assertCount($links_count, $links);

    // Assert that ellipsis are shown in the correct number.
    $ellipsis = $wrapper->filter('li.ecl-pager__item--ellipsis');
    // When the total number of pages is 4 or less, no ellipsis should be shown.
    $ellipsis_count = 0;
    if ($total_pages > 4) {
      // One ellipsis is shown when more than 3 pages are left at the end.
      $ellipsis_count += (int) ($total_pages - $current_page) > 3;
      // One ellipsis is shown when more than 3 pages have passed from the 1st.
      $ellipsis_count += (int) ($current_page - 3) > 1;
    }
    $this->assertCount($ellipsis_count, $ellipsis);
  }

  /**
   * Data provider for the single pager test.
   *
   * @return array
   *   An array of pager test cases. Each case contains the current page and
   *   the number of total pages, using a 1-based array notation.
   */
  public function singlePagerDataProvider(): array {
    return [
      '1st page out of 15' => [1, 15],
      '2nd page out of 15' => [2, 15],
      '3rd page out of 15' => [3, 15],
      '4th page out of 15' => [4, 15],
      '5th page out of 15' => [5, 15],
      '6th page out of 15' => [6, 15],
      '7th page out of 15' => [7, 15],
      '8th page out of 15' => [8, 15],
      '9th page out of 15' => [9, 15],
      '10th page out of 15' => [10, 15],
      '11th page out of 15' => [11, 15],
      '12th page out of 15' => [12, 15],
      '13th page out of 15' => [13, 15],
      '14th page out of 15' => [14, 15],
      'last page out of 15' => [15, 15],
      '1st page out of 7' => [1, 7, 'user.admin_index'],
      'last page out of 7' => [7, 7, 'user.admin_index'],
      'middle page out of 7' => [4, 7, 'user.admin_index'],
      '6th page out of 7' => [6, 7, 'system.admin_reports'],
      '1st page out of 4' => [1, 4],
      '1st page out of 5' => [1, 5],
      '2nd page out of 6' => [2, 6],
    ];
  }

  /**
   * Handles assertions on pager elements.
   *
   * @param \Symfony\Component\DomCrawler\Crawler $element
   *   The element being tested.
   * @param bool $should_be_present
   *   Whether or not the element should be present.
   * @param string $url
   *   The url of the pager link, if the element is present.
   * @param string $title
   *   The title attribute of the pager link, if the element is present.
   */
  protected function assertSpecialPagerElement(Crawler $element, bool $should_be_present, string $url, string $title): void {
    $this->assertCount((int) $should_be_present, $element);
    if ($should_be_present) {
      $this->assertCount(1, $element->filter("a[href='$url'][title='$title']"));
    }
  }

  /**
   * Generates a pager url.
   *
   * @param string $route_name
   *   The route name where the link should point.
   * @param int $page
   *   The target page, using a 1-based notation for ease of use.
   * @param int $element
   *   The pager element. Defaults to the first one (0-based).
   *
   * @return string
   *   A string URL.
   */
  protected function generatePagerUrl(string $route_name, int $page, int $element = 0): string {
    $options = [
      'query' => pager_query_add_page([], $element, $page - 1),
    ];

    return Url::fromRoute($route_name, [], $options)->toString();
  }

}
