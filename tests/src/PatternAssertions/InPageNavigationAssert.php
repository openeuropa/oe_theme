<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\PatternAssertions;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for in-page-navigation.
 */
class InPageNavigationAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions($variant): array {
    return [
      'title' => [
        [$this, 'assertElementText'],
        'nav.ecl-inpage-navigation .ecl-inpage-navigation__title',
      ],
      'list' => [
        [$this, 'assertList'],
        'nav.ecl-inpage-navigation ecl-inpage-navigation__body',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {
    $crawler = new Crawler($html);
    $page_header = $crawler->filter('nav.ecl-inpage-navigation');
    self::assertCount(1, $page_header);
  }

  /**
   * Asserts the in-page-navigation links list.
   *
   * @param array|null $expected
   *   The expected description values.
   * @param string $variant
   *   The variant of the pattern being checked.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertList($expected, string $variant, Crawler $crawler): void {
    $this->assertElementExists('ul.ecl-inpage-navigation__list', $crawler);

    $actual = [];
    $crawler->filter('ul.ecl-inpage-navigation__list .ecl-inpage-navigation__item a')->each(function (Crawler $node) use (&$actual) {
      $actual[] = [
        'label' => $node->text(),
        'href' => $node->attr('href'),
      ];
    });

    self::assertEquals($expected, $actual);
  }

}
