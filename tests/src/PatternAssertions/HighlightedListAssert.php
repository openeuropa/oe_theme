<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\PatternAssertions;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the Highlighted list pattern.
 *
 * @see ./templates/patterns/highlighted_list/highlighted_list.ui_patterns.yml
 */
class HighlightedListAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions($variant): array {
    return [
      'title' => [
        [$this, 'assertElementText'],
        'div#highlighted-news-block h2.ecl-u-type-heading-2',
      ],
      'introduction' => [
        [$this, 'assertElementText'],
        'div#highlighted-news-block div.ecl-content-item-block__introduction',
      ],
      'highlighted_item' => [
        [$this, 'assertHighlightedItem'],
        'div#highlighted-news-block div.ecl-row div.ecl-col-l-8.ecl-u-d-flex.ecl-u-flex-column',
        $variant,
      ],
      'items' => [
        [$this, 'assertItems'],
        'div#highlighted-news-block div.ecl-row div.ecl-u-d-flex.ecl-u-flex-column',
        $variant,
      ],
      'see_more_label' => [
        [$this, 'assertElementText'],
        'div#highlighted-news-block > div.ecl-u-mt-m a.ecl-link.ecl-link--standalone .ecl-link__label',
      ],
      'see_more_url' => [
        [$this, 'assertElementAttribute'],
        'div#highlighted-news-block > div.ecl-u-mt-m a.ecl-link.ecl-link--standalone',
        'href',
      ],
      'detail' => [
        [$this, 'assertDetail'],
        'div#highlighted-news-block div.ecl.ecl-u-mt-s',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {
    $crawler = new Crawler($html);
    $highlighted_list = $crawler->filter('div#highlighted-news-block');
    self::assertCount(1, $highlighted_list);
  }

  /**
   * {@inheritdoc}
   */
  protected function getPatternVariant(string $html): string {
    $crawler = new Crawler($html);
    $four_columns = $crawler->filter('div#highlighted-news-block div.ecl-row div.ecl-col-l-3.ecl-u-d-flex.ecl-u-flex-column');
    if ($four_columns->count()) {
      return 'four_columns';
    }

    return 'default';
  }

  /**
   * Asserts the Highlighted item of the list.
   *
   * @param array|null $expected_highlighted_item
   *   The expected item array (image array, title, url, primary meta array
   *   and secondary meta).
   * @param string $selector
   *   The CSS selector to find the item.
   * @param string $variant
   *   The variant of the pattern being checked.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertHighlightedItem(?array $expected_highlighted_item, string $selector, string $variant, Crawler $crawler) {
    if (is_null($expected_highlighted_item) || $variant !== 'default') {
      $this->assertElementNotExists($selector, $crawler);
      return;
    }
    $this->assertElementExists($selector, $crawler);
    $highlighted_wrapper = $crawler->filter($selector);
    $list_item_assert = new ListItemAssert();
    $html = $highlighted_wrapper->html();
    $list_item_assert->assertPattern($expected_highlighted_item, $html);
    $list_item_assert->assertVariant('default', $html);
  }

  /**
   * Asserts the items on the right side of the highlighted list.
   *
   * @param array|null $expected_items
   *   The expected items.
   * @param string $selector
   *   The CSS selector to find the items' wrapper.
   * @param string $variant
   *   The variant of the pattern being checked.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   */
  protected function assertItems(?array $expected_items, string $selector, string $variant, Crawler $crawler): void {
    $selector = $variant === 'default' ? $selector . '.ecl-col-l-4' : $selector . '.ecl-col-l-3';
    if (is_null($expected_items)) {
      $this->assertElementNotExists($selector, $crawler);
      return;
    }

    $items_wrapper = $crawler->filter($selector);
    if ($variant === 'default') {
      $this->assertElementExists($selector, $crawler);
      self::assertCount(1, $items_wrapper);
    }
    else {
      self::assertCount(4, $items_wrapper);
    }
    // Assert the number of items.
    $items = $items_wrapper->filter('article');
    self::assertCount(count($expected_items), $items, 'The expected number of items does not correspond with the actual number of items in the list.');
    // Assert there is no description element for any of the items.
    $this->assertElementNotExists('.ecl-content-block__description', $items_wrapper);

    // Assert each item's info.
    $list_item_assert = new ListItemAssert();
    foreach ($expected_items as $index => $expected_item) {
      $html = $items->eq($index)->outerHtml();
      $list_item_assert->assertPattern($expected_item, $html);
      $list_item_assert->assertVariant('default', $html);
    }
  }

  /**
   * Asserts the detail text from the footer of the highlighted list.
   *
   * @param array|null $expected
   *   The expected description values.
   * @param string $selector
   *   The CSS selector to find the detail.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertDetail($expected, string $selector, Crawler $crawler): void {
    if (is_null($expected)) {
      $this->assertElementNotExists($selector, $crawler);
      return;
    }
    $this->assertElementExists($selector, $crawler);
    $detail_element = $crawler->filter($selector);
    if ($expected instanceof PatternAssertStateInterface) {
      $expected->assert($detail_element->html());
      return;
    }
    self::assertEquals($expected, $detail_element->filter('p')->html());
  }

}
