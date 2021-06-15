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
        'div.ecl-row div.ecl-editor div.ecl-editor',
      ],
      'video_ratio' => [
        [$this, 'assertVideoRatio'],
        'div.ecl-row figure.ecl-media-container div.ecl-media-container__media',
      ],
      'link' => [
        [$this, 'assertLink'],
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
    $link_element = $crawler->filter('a.ecl-link.ecl-link--icon.ecl-link--icon-after.ecl-u-mt-m.ecl-u-mt-md-l.ecl-u-type-bold');
    self::assertEquals($expected_link['path'], $link_element->attr('href'));

    $label_element = $link_element->filter('span.ecl-link__label');
    self::assertEquals($expected_link['label'], $label_element->text());

    $svg = $link_element->filter('svg.ecl-icon.ecl-icon--s.ecl-icon--primary.ecl-link__icon use');
    self::assertContains('icons.svg#ui--' . $expected_link['icon'], $svg->attr('xlink:href'));
  }

  /**
   * {@inheritdoc}
   */
  protected function getPatternVariant(string $html): string {
    // Variant can be clearly recognised if image or video and link exist only.
    // In case if the link isn't provided then "left_simple", "right_simple"
    // will be returned based on the position of media.
    // In case if the image or video aren't provided then
    // "left_simple", "left_featured" will be returned based on the link style.
    // If image or video and link aren't provided then "left_simple" will be
    // returned as default value.
    $crawler = new Crawler($html);

    $position_variant = 'left';
    $media_position = $crawler->filter('.ecl-u-order-md-last');
    if (!$media_position->count()) {
      // Class doesn't exist.
      $position_variant = 'right';
    }

    $link_variant = 'simple';
    $link_element = $crawler->filter('a.ecl-link.ecl-link--icon');
    if ($link_element->count()) {
      // Link exists.
      $link_class = $link_element->attr('class');
      if (strpos($link_class, 'ecl-link--cta') !== FALSE) {
        $link_variant = 'featured';
      }
    }

    return $position_variant . '_' . $link_variant;
  }

}
