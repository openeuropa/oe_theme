<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\PatternAssertions;

use Drupal\Core\Url;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the list item pattern.
 */
class ListItemAssert extends BasePatternAssert {

  public function assertPattern(array $expected, string $html): void {
    
  }

  /**
   * Asserts the variant of the pattern used.
   *
   * @param string $expected
   *   The expected variant.
   * @param string $html
   *   The pattern html.
   */
  public function assertVariant(string $expected, string $html): void {
    $crawler = new Crawler($html);
  }


//  protected function getFieldXpathMapping(): array {
//    return [
//      'title' => [[$this, 'assertLink'], 'ecl-content-item-date__title']
//    ];
//  }
//
//  protected function assertLink(Url $url) {
//
//  }

}
