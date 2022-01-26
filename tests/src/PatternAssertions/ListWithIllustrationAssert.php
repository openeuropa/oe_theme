<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\PatternAssertions;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the list with illustration pattern.
 */
class ListWithIllustrationAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions($variant): array {
    return [
      'column' => [
        [$this, 'assertColumns'],
      ],
      'zebra' => [
        [$this, 'assertZebra'],
      ],
      'squared' => [
        [$this, 'assertSquared'],
      ],
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
    self::assertElementExists('.ecl-list-illustration', $crawler);
  }

  /**
   * Asserts the columns of the list.
   *
   * @param int $column
   *   The expected number of columns.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertColumns(int $column, Crawler $crawler): void {
    $column_selector = '.ecl-list-illustration--col-' . $column;
    self::assertElementExists($column_selector, $crawler);
  }

  /**
   * Asserts if the list uses zebra pattern or not.
   *
   * @param bool $zebra
   *   Whether the zebra pattern is enabled or not.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertZebra(bool $zebra, Crawler $crawler): void {
    if ($zebra) {
      self::assertElementExists('.ecl-list-illustration--zebra', $crawler);
    }
    else {
      self::assertElementNotExists('.ecl-list-illustration--zebra', $crawler);
    }
  }

  /**
   * Asserts if the list uses square images or not.
   *
   * @param bool $squared
   *   Whether the "squared" option is enabled or not.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertSquared(bool $squared, Crawler $crawler): void {
    if ($squared) {
      // Few square images can exist.
      $element = $crawler->filter('.ecl-list-illustration__image--square');
      self::assertTrue((bool) $element->count());
    }
    else {
      self::assertElementNotExists('.ecl-list-illustration__image--square', $crawler);
    }
  }

  /**
   * Asserts the item with an illustration from the list.
   *
   * @param array $expected_items
   *   The expected list items.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertItems(array $expected_items, Crawler $crawler): void {
    $item_elements = $crawler->filter('.ecl-list-illustration__item');
    self::assertCount(count($expected_items), $item_elements, 'The expected list items do not match the found list items.');
    foreach ($expected_items as $index => $expected_item) {
      $item_element = $item_elements->eq($index);
      if (isset($expected_item['title'])) {
        self::assertElementText($expected_item['title'], '.ecl-list-illustration__title', $item_element);
      }
      else {
        self::assertElementNotExists('.ecl-list-illustration__title', $item_element);
      }
      if (isset($expected_item['description'])) {
        self::assertElementText($expected_item['description'], '.ecl-list-illustration__description', $item_element);
      }
      else {
        self::assertElementNotExists('.ecl-list-illustration__description', $item_element);
      }
      if (isset($expected_item['image'])) {
        self::assertElementExists('.ecl-list-illustration__image', $item_element);
        $image_element = $item_element->filter('.ecl-list-illustration__image');
        self::assertEquals($expected_item['image']['alt'], $image_element->attr('aria-label'));
        self::assertStringContainsString($expected_item['image']['src'], $image_element->attr('style'));
      }
      if (isset($expected_item['icon'])) {
        $icon_element = $item_element->filter('svg.ecl-icon use');
        $this::assertStringContainsString('#' . $expected_item['icon'], $icon_element->attr('xlink:href'));
      }
    }
  }

}
