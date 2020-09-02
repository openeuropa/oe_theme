<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\PatternAssertions;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the icons with text pattern.
 */
class IconsTextAssert extends BasePatternAssert {

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
    $list_item = $crawler->filter('ul.ecl-unordered-list.ecl-unordered-list--no-bullet');
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
    $list_elements = $crawler->filter('li.ecl-u-d-flex.ecl-u-align-items-center.ecl-unordered-list__item');
    self::assertCount(count($expected_items), $list_elements);
    foreach ($expected_items as $index => $expected_item) {
      $list_element = $list_elements->eq($index);
      self::assertEquals($expected_item['text'], trim($list_element->text()));
      $icon_element = $list_element->filter('svg.ecl-icon.ecl-icon--m.ecl-u-mr-s use');
      $this::assertContains('#general--' . $expected_item['icon'], $icon_element->attr('xlink:href'));
    }
  }

}
