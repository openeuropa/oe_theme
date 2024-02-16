<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\PatternAssertions;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the icons with text pattern.
 *
 * @see ./templates/patterns/link_block/link_block.ui_patterns.yml
 */
class LinkBlockAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions($variant): array {
    return [
      'links' => [
        [$this, 'assertLinks'],
      ],
      'title' => [
        [$this, 'assertElementText'],
        'h5.ecl-u-type-heading-5.ecl-u-mt-xs.ecl-u-mb-none',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {
    $crawler = new Crawler($html);
    $list_item = $crawler->filter('ul.ecl-link-block__list.ecl-unordered-list--no-bullet');
    self::assertCount(1, $list_item);
  }

  /**
   * Asserts the links of the pattern.
   *
   * @param array $expected_items
   *   The expected item values.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertLinks(array $expected_items, Crawler $crawler): void {
    $list_elements = $crawler->filter('li.ecl-link-block__item.ecl-u-mt-xs.ecl-u-mb-xs');
    self::assertCount(count($expected_items), $list_elements);
    foreach ($expected_items as $index => $expected_item) {
      $link = $list_elements->eq($index)->filter('a')->first();
      self::assertEquals($expected_item['label'], trim($link->text()));
      self::assertEquals($expected_item['url'], trim($link->attr('href')));
    }
  }

}
