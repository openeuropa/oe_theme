<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\PatternAssertions;

use Symfony\Component\DomCrawler\Crawler;
use PHPUnit\Framework\Exception;

/**
 * Assertions for text_featured_media pattern.
 *
 *  @see ./templates/patterns/text_featured_media/text_featured_media.ui_patterns.yml
 */
class TextFeaturedMediaAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions($variant): array {
    return [
      'title' => [
        [$this, 'assertElementText'],
        'div.ecl-featured-item__heading',
      ],
      'text_title' => [
        [$this, 'assertElementText'],
        'div.ecl-featured-item__title',
      ],
      'image' => [
        [$this, 'assertImage'],
        'article.ecl-featured-item__container figure.ecl-media-container img',
      ],
      'video' => [
        [$this, 'assertElementHtml'],
        'article.ecl-featured-item__container figure.ecl-media-container div.ecl-media-container__media',
      ],
      'caption' => [
        [$this, 'assertElementText'],
        'article.ecl-featured-item__container figure figcaption.ecl-media-container__caption',
      ],
      'text' => [
        [$this, 'assertElementText'],
        'div.ecl-featured-item__item > div.ecl',
      ],
      'video_ratio' => [
        [$this, 'assertVideoRatio'],
        'article.ecl-featured-item__container figure.ecl-media-container div.ecl-media-container__media',
      ],
      'link' => [
        [$this, 'assertLink'],
      ],
      'highlighted' => [
        [$this, 'assertHighlighted'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {}

  /**
   * Asserts the video ratio of the pattern.
   *
   * @param string $expected_ratio
   *   The video ratio.
   * @param string $selector
   *   The CSS selector to find the element.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertVideoRatio(string $expected_ratio, string $selector, Crawler $crawler): void {
    self::assertElementExists($selector, $crawler);

    $element = $crawler->filter($selector);
    $existing_classes = $element->attr('class');
    $existing_classes = explode(' ', $existing_classes);

    $expected_ratio = str_replace(':', '-', $expected_ratio);
    if (!in_array('ecl-media-container__media--ratio-' . $expected_ratio, $existing_classes)) {
      throw new Exception(sprintf('The element with the selector %s does not use the ratio %s.', $selector, $expected_ratio));
    }
  }

  /**
   * Asserts the link of the pattern.
   *
   * @param array $expected_link
   *   Array with keys: 'label', 'path', 'icon'.
   *   'icon' can be 'external' or 'corner-arrow'.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertLink(array $expected_link, Crawler $crawler): void {
    $link_element = $crawler->filter('a.ecl-link.ecl-link--icon.ecl-link--icon-after.ecl-featured-item__link.ecl-u-mt-m.ecl-u-type-bold');
    self::assertEquals($expected_link['path'], $link_element->attr('href'));

    $label_element = $link_element->filter('span.ecl-link__label');
    self::assertEquals($expected_link['label'], $label_element->text());

    $svg = $link_element->filter('svg.ecl-icon.ecl-icon--s.ecl-icon--primary.ecl-link__icon use');
    self::assertStringContainsString('icons.svg#' . $expected_link['icon'], $svg->attr('xlink:href'));
  }

  /**
   * Asserts the highlighted value of the pattern.
   *
   * @param bool $highlighted
   *   Whether the item is highlighted or not.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertHighlighted(bool $highlighted, Crawler $crawler) {
    if (!$highlighted) {
      $this->assertElementNotExists('div.ecl-featured-item.ecl-featured-item--extended', $crawler);
      $this->assertElementExists('div.ecl-featured-item', $crawler);
      return;
    }
    $this->assertElementExists('div.ecl-featured-item.ecl-featured-item--extended', $crawler);
  }

  /**
   * {@inheritdoc}
   */
  protected function getPatternVariant(string $html): string {
    // The variant is extracted by checking the presence and properties of the
    // media and link.
    // The default variant is "left_simple".
    $crawler = new Crawler($html);

    $position_variant = 'left';
    $media_wrapper = $crawler->filter('.ecl-col-m-6');
    $media_position = $media_wrapper->filter('.ecl-u-order-m-last');
    if ($media_wrapper->count() && !$media_position->count()) {
      // If the media item is rendered but the position class is not set, then
      // we have one of the right variants.
      $position_variant = 'right';
    }

    $link_variant = 'simple';
    $link_element = $crawler->filter('a.ecl-link.ecl-link--icon');
    if ($link_element->count()) {
      $link_class = $link_element->attr('class');
      if (strpos($link_class, 'ecl-link--cta') !== FALSE) {
        // If we have a link set and the "ecl-link--cta" class is present, then
        // we have a featured variant.
        $link_variant = 'featured';
      }
    }

    return $position_variant . '_' . $link_variant;
  }

}
