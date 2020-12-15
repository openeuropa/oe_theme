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
        'h2.ecl-u-type-heading-2.ecl-u-mt-2xl.ecl-u-mt-md-3xl.ecl-u-mb-l',
      ],
      'image' => [
        [$this, 'assertImage'],
        'div.ecl-row figure.ecl-media-container img',
      ],
      'video' => [
        [$this, 'assertElementHtml'],
        'div.ecl-row figure.ecl-media-container div.ecl-media-container__media',
      ],
      'caption' => [
        [$this, 'assertElementText'],
        'div.ecl-row figure figcaption.ecl-media-container__caption',
      ],
      'text' => [
        [$this, 'assertElementText'],
        'div.ecl-row > div.ecl-editor',
      ],
      'video_ratio' => [
        [$this, 'assertVideoRatio'],
        'div.ecl-row figure.ecl-media-container div.ecl-media-container__media',
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
   *   The expected class with the video ratio.
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
      throw new Exception(sprintf('Element with selector %s does not use ratio %s.', $selector, $expected_ratio));
    }
  }

}
