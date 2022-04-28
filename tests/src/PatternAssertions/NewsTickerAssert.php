<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\PatternAssertions;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the news ticker pattern.
 *
 * @see ./templates/patterns/news_ticker/news_ticker.ui_patterns.yml
 */
class NewsTickerAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions(string $variant): array {
    return [
      'items' => [
        [$this, 'assertItems'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {
    $crawler = new Crawler($html);
    self::assertElementExists('div.ecl-news-ticker', $crawler);
  }

  /**
   * Asserts the items of the pattern.
   *
   * @param array $expected_items
   *   The expected item values.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertItems(array $expected_items, Crawler $crawler): void {
    // Assert the number of items is correct.
    $items = $crawler->filter('div.ecl-news-ticker__container div.ecl-news-ticker__content ul.ecl-news-ticker__slides li.ecl-news-ticker__slide');
    // ECL clones the first and the last element to create the carousel effect
    // so there are 2 additional items rendered.
    self::assertCount(count($expected_items) + 2, $items, 'The expected news ticker items do not match the found news ticker items.');

    // Assert the news items have the correct info and link, if given.
    foreach ($expected_items as $index => $expected_item) {
      $actual_item = $items->eq($index);
      if (isset($expected_item['link'])) {
        $this->assertElementText($expected_item['content'], 'a.ecl-link.ecl-news-ticker__slide-text', $actual_item);
        $link = $actual_item->filter('a.ecl-link.ecl-news-ticker__slide-text');
        self::assertStringContainsString($expected_item['link'], $link->attr('href'));
        continue;
      }
      self::assertStringContainsString($expected_item['content'], $actual_item->text());
    }
  }

}
