<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\PatternAssertions;

use PHPUnit\Framework\Exception;
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
      'color_mode' => [
        [$this, 'assertColorMode'],
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
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   * @SuppressWarnings(PHPMD.NPathComplexity)
   */
  protected function assertItems(array $expected_items, Crawler $crawler): void {
    $items = $crawler->filter('div.ecl-carousel__container div.ecl-carousel__slides div.ecl-carousel__slide');
    self::assertCount(count($expected_items), $items);
    foreach ($expected_items as $index => $expected_item) {
      $item = $items->eq($index);
      // Assert carousel item (banner) variant.
      if (!isset($expected_item['variant']) || $expected_item['variant'] === 'plain-background') {
        $this->assertElementExists('section.ecl-banner.ecl-banner--box-bg-none.ecl-banner--color-dark.ecl-banner--l', $item);
      }
      elseif ($expected_item['variant'] === 'text-overlay') {
        $this->assertElementExists('section.ecl-banner.ecl-banner--box-bg-dark.ecl-banner--color-light', $item);
      }
      else {
        $this->assertElementExists('section.ecl-banner.ecl-banner--box-bg-light.ecl-banner--color-dark', $item);
      }
      if (isset($expected_item['color_mode'])) {
        $this->assertElementExists('section.ecl-banner.ecl-color-mode--' . $expected_item['color_mode'], $item);
      }
      // Assert title.
      if (!isset($expected_item['title'])) {
        $this->assertElementNotExists('div.ecl-banner__title span.ecl-banner__title-text', $item);
      }
      else {
        $this->assertElementText($expected_item['title'], 'div.ecl-banner__title span.ecl-banner__title-text', $item);
      }
      // Assert description.
      if (!isset($expected_item['description'])) {
        $this->assertElementNotExists('p.ecl-banner__description span.ecl-banner__description-text', $item);
      }
      else {
        $this->assertElementText($expected_item['description'], 'p.ecl-banner__description span.ecl-banner__description-text', $item);
      }
      // Assert link and its label.
      if (!isset($expected_item['url'])) {
        $this->assertElementNotExists('div.ecl-banner__cta a', $item);
      }
      else {
        $this->assertElementAttribute($expected_item['url'], 'div.ecl-banner__cta a.ecl-link--icon.ecl-banner__link-cta', 'href', $item);
        $this->assertElementText($expected_item['url_text'], 'div.ecl-banner__cta a span.ecl-link__label', $item);
      }
      // Assert image.
      if (!isset($expected_item['image']) ||
        (isset($expected_item['variant']) && $expected_item['variant'] === 'plain-background')) {
        $this->assertElementNotExists('picture.ecl-picture.ecl-banner__picture', $item);
      }
      else {
        $image_element = $item->filter('picture.ecl-picture.ecl-banner__picture img.ecl-banner__image');
        $this->assertStringContainsString($expected_item['image'], $image_element->attr('src'));
        if (isset($expected_item['image_alt'])) {
          $this->assertStringContainsString($expected_item['image_alt'], $image_element->attr('alt'));
        }
        else {
          $this->assertEquals('', $image_element->attr('alt'));
        }
      }
      if (!isset($expected_item['sources']) || (isset($expected_item['variant']) && $expected_item['variant'] === 'plain-background')) {
        $this->assertElementNotExists('picture source', $item);
      }
      else {
        $small_media = $item->filter('picture source:not([media])');
        $this->assertStringContainsString($expected_item['sources']['small'], $small_media->attr('srcset'));
        $medium_media = $item->filter('picture source[media="all and (min-width: 480px)"]');
        $this->assertStringContainsString($expected_item['sources']['medium'], $medium_media->attr('srcset'));
        $large_media = $item->filter('picture source[media="all and (min-width: 768px)"]');
        $this->assertStringContainsString($expected_item['sources']['large'], $large_media->attr('srcset'));
        $extra_large_media = $item->filter('picture source[media="all and (min-width: 996px)"]');
        $this->assertStringContainsString($expected_item['sources']['extra_large'], $extra_large_media->attr('srcset'));
        $extra_extra_large_media = $item->filter('picture source[media="all and (min-width: 1140px)"]');
        $this->assertStringContainsString($expected_item['sources']['extra_extra_large'], $extra_extra_large_media->attr('srcset'));
        $full_width_media = $item->filter('picture source[media="all and (min-width: 1368px)"]');
        $this->assertStringContainsString($expected_item['sources']['full_width'], $full_width_media->attr('srcset'));
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
        $this->assertElementNotExists("section.ecl-carousel.ecl-color-mode--$mode", $crawler);
      }
      return;
    }

    if (!in_array($color_mode, $color_modes)) {
      throw new Exception("The color mode '$color_mode' is not supported.");
    }

    $this->assertElementExists("section.ecl-carousel.ecl-color-mode--$color_mode", $crawler);
  }

}
