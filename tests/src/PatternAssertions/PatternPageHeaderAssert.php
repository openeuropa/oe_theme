<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\PatternAssertions;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the page header pattern.
 */
class PatternPageHeaderAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions($variant): array {
    return [
      'meta' => [
        [$this, 'assertMetaElements'],
      ],
      'title' => [
        [$this, 'assertElementText'],
        '.ecl-page-header h1.ecl-page-header__title',
      ],
      'description' => [
        [$this, 'assertElementText'],
        '.ecl-page-header .ecl-page-header__description',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {
    $crawler = new Crawler($html);
    $page_header = $crawler->filter('.ecl-page-header');
    self::assertCount(1, $page_header);
  }

  /**
   * Asserts the meta items of the pattern.
   *
   * @param array $metas
   *   The expected meta item values.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertMetaElements(array $metas, Crawler $crawler): void {
    if (empty($metas)) {
      $this->assertElementNotExists('.ecl-page-header .ecl-page-header__meta', $crawler);
      return;
    }
    $meta_items = $crawler->filter('.ecl-page-header .ecl-page-header__meta .ecl-page-header__meta-item');
    self::assertCount(count($metas), $meta_items);
    foreach ($metas as $index => $meta) {
      self::assertEquals($meta, trim($meta_items->eq($index)->text()));
    }
  }

}
