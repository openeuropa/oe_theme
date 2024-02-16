<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\PatternAssertions;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the timeline pattern.
 *
 * @see ./templates/patterns/timeline/timeline.ui_patterns.yml
 */
class TimelineAssert extends BasePatternAssert {

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
    $base_selector = $this->getBaseItemClass();
    $list_item = $crawler->filter($base_selector);
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
    // Assert all labels are correct.
    $expected_labels = array_column($expected_items, 'label');
    $label_items = $crawler->filter($this->getBaseItemClass() . ' li.ecl-timeline__item div.ecl-timeline__label');
    self::assertCount(count($expected_labels), $label_items);
    foreach ($expected_labels as $index => $expected_label) {
      self::assertEquals($expected_label, trim($label_items->eq($index)->html()));
    }

    // Assert all titles are correct.
    $expected_titles = array_column($expected_items, 'title');
    $title_items = $crawler->filter($this->getBaseItemClass() . ' li.ecl-timeline__item .ecl-timeline__title');
    self::assertCount(count($expected_titles), $title_items);
    foreach ($expected_titles as $index => $expected_title) {
      self::assertEquals($expected_title, trim($title_items->eq($index)->text()));
    }

    // Assert all values are correct.
    $expected_values = array_column($expected_items, 'body');
    $value_items = $crawler->filter($this->getBaseItemClass() . ' li.ecl-timeline__item .ecl-timeline__content');
    self::assertCount(count($expected_labels), $value_items);
    foreach ($expected_values as $index => $expected_value) {
      self::assertEquals($expected_value, trim($value_items->eq($index)->text()));
    }
  }

  /**
   * Returns the base CSS selector for a field item.
   *
   * @return string
   *   The base selector.
   */
  protected function getBaseItemClass(): string {
    return 'ol.ecl-timeline';
  }

}
