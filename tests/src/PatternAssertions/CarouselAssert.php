<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\PatternAssertions;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for carousel pattern.
 *
 *  @see ./templates/patterns/carousel/carousel.ui_patterns.yml
 */
class CarouselAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions($variant): array {
    return [
      'items' => [
        [$this, 'assertItems'],
      ],
      'full_width' => [
        [$this, 'assertFullWidth'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {}

  /**
   * Asserts the items of carousel pattern.
   *
   * @param array $expected_items
   *   The expected item values.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertItems(array $expected_items, Crawler $crawler): void {
    $items = $crawler->filter('div.ecl-carousel__container div.ecl-carousel__slides div.ecl-carousel__slide');
    self::assertCount(count($expected_items), $items);
    foreach ($expected_items as $index => $expected_item) {
      $item = $items->eq($index);
      // Assert carousel item (banner) variant.
      if (!isset($expected_item['variant'])) {
        $this->assertElementExists('section.ecl-page-banner--primary', $item);
      }
      else {
        $this->assertElementExists('section.ecl-page-banner--' . $expected_item['variant'], $item);
      }
      // Assert title.
      if (!isset($expected_item['title'])) {
        $this->assertElementNotExists('div.ecl-page-banner__title', $item);
      }
      else {
        $this->assertElementText($expected_item['title'], 'div.ecl-page-banner__title', $item);
      }
      // Assert description.
      if (!isset($expected_item['description'])) {
        $this->assertElementNotExists('p.ecl-page-banner__description', $item);
      }
      else {
        $this->assertElementText($expected_item['description'], 'p.ecl-page-banner__description', $item);
      }
      // Assert link and its label.
      if (!isset($expected_item['url'])) {
        $this->assertElementNotExists('div.ecl-page-banner__cta a', $item);
      }
      else {
        $this->assertElementAttribute($expected_item['url'], 'div.ecl-page-banner__cta a', 'href', $item);
        $this->assertElementText($expected_item['url_text'], 'div.ecl-page-banner__cta a span.ecl-link__label', $item);
      }
      // Assert image.
      if (!isset($expected_item['image'])) {
        $this->assertElementNotExists('div.ecl-page-banner__image', $item);
      }
      else {
        $this->assertElementAttribute('background-image:url(' . $expected_item['image'] . ')', 'div.ecl-page-banner__image', 'style', $item);
      }
    }
  }

  /**
   * Asserts the full width value of the pattern.
   *
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   * @param bool $full_width
   *   Whether the carousel is extended to full width.
   */
  protected function assertFullWidth(Crawler $crawler, bool $full_width = FALSE) {
    if (!$full_width) {
      $this->assertElementNotExists('div.ecl-carousel.ecl-carousel--full-width', $crawler);
      $this->assertElementExists('div.ecl-carousel', $crawler);
      return;
    }
    $this->assertElementExists('div.ecl-carousel.ecl-carousel--full-width', $crawler);
  }

}
