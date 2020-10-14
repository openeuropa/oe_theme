<?php

declare(strict_types = 1);

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
        [$this, 'assertElementText'],
        '.ecl-page-header-core .ecl-page-header-core__meta',
      ],
      'title' => [
        [$this, 'assertElementText'],
        '.ecl-page-header-core h1.ecl-page-header-core__title',
      ],
      'description' => [
        [$this, 'assertElementText'],
        '.ecl-page-header-core .ecl-page-header-core__description',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {
    $crawler = new Crawler($html);
    $page_header = $crawler->filter('.ecl-page-header-core');
    self::assertCount(1, $page_header);
  }

}
