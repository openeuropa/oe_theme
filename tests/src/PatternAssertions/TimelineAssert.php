<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\PatternAssertions;

use PHPUnit\Framework\Exception;
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
      'color_mode' => [
        [$this, 'assertColorMode'],
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
   * Asserts the presence or absence of the color mode.
   *
   * @param string $color_mode
   *   The name of the color mode.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertColorMode(string $color_mode, Crawler $crawler): void {
    $color_modes = [
      'blue',
      'green-dark',
      'orange',
      'green',
      'purple',
      'blue-navy',
      'blue-electric',
      'blue-ocean',
      'green-lemon',
      'green-pine',
      'warm-grey',
      'red-crayola',
      'yellow-gold',
      'purple-violet',
      'red-tomato',
    ];
    if (empty($color_mode)) {
      foreach ($color_modes as $mode) {
        $this->assertElementNotExists("ol.ecl-timeline.ecl-color-mode--$mode", $crawler);
      }
      return;
    }

    if (!in_array($color_mode, $color_modes)) {
      throw new Exception("The color mode '$color_mode' is not supported.");
    }

    $this->assertElementExists("ol.ecl-timeline.ecl-color-mode--$color_mode", $crawler);
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
