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
        $variant,
      ],
      'zebra' => [
        [$this, 'assertZebra'],
      ],
      'items' => [
        [$this, 'assertItems'],
        $variant,
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
   * @param string $variant
   *   The variant of the pattern being checked.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertColumns(int $column, string $variant, Crawler $crawler): void {
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
   * Asserts the item with an illustration from the list.
   *
   * @param array $expected_items
   *   The expected list items.
   * @param string $variant
   *   The variant of the pattern being checked.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertItems(array $expected_items, string $variant, Crawler $crawler): void {
    $item_elements = $crawler->filter('.ecl-list-illustration__image');
    self::assertCount(count($expected_items), $item_elements, 'The expected list items do not match the found list items.');
    foreach ($expected_items as $index => $expected_item) {
      $item_element = $item_elements->eq($index);
      if ($expected_item['title']) {
        self::assertElementText($expected_item['title'], '.ecl-list-illustration__title', $item_element);
      }
      else {
        self::assertElementNotExists('.ecl-list-illustration__title', $item_element);
      }
      if ($expected_item['description']) {
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
        if (strpos($variant, 'square') !== FALSE) {
          self::assertElementExists('.ecl-list-illustration__image--square', $item_element);
        }
      }
      if (isset($expected_item['icon'])) {
        $icon_element = $item_element->filter('svg.ecl-icon use');
        $this::assertStringContainsString('#' . $expected_item['icon'], $icon_element->attr('xlink:href'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getPatternVariant(string $html): string {
    $crawler = new Crawler($html);
    // Check whether it's horizontal or vertical.
    $variant = 'vertical';
    $list = $crawler->filter('.ecl-list-illustration');
    if (strpos($list->attr('class'), '.ecl-list-illustration--col') !== FALSE) {
      $variant = 'horizontal';
    }
    // Check whether we have images or icons.
    // If we have one image, we assume it's an image variant.
    $image_element = $crawler->filter('.ecl-list-illustration__item');
    if ($image_element->count()) {
      $variant .= '_images';
      // Check whether it's square or not.
      if (strpos($image_element->attr('class'), '.ecl-list-illustration__image--square') !== FALSE) {
        $variant .= '_square';
      }
      else {
        $variant .= '_landscape';
      }
      return $variant;
    }
    $variant .= '_icons';
    return $variant;
  }

}
