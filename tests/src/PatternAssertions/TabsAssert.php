<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\PatternAssertions;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the tabs pattern.
 *
 * @see ./templates/patterns/tabs/tabs.ui_patterns.yml
 */
class TabsAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions($variant): array {
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
    $list_item = $crawler->filter('div.ecl-tabs div.ecl-tabs__container');
    self::assertCount(1, $list_item);
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
    $item_elements = $crawler->filter('div.ecl-tabs__item:not(.ecl-tabs__item--more)');
    self::assertCount(count($expected_items), $item_elements);
    foreach ($expected_items as $index => $expected_item) {
      $item_element = $item_elements->eq($index);
      self::assertEquals($expected_item['label'], trim($item_element->text()));
      $this->assertElementAttribute($expected_item['path'], 'a.ecl-link', 'href', $item_element);
      if (!empty($expected_item['is_current'])) {
        $active_item = $item_element->filter('a.ecl-tabs__link--active');
        self::assertCount(1, $active_item);
      }
    }
  }

}
