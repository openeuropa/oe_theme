<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\PatternAssertions;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for text_featured_media pattern.
 *
 *  @see ./templates/patterns/text_featured_media/text_featured_media.ui_patterns.yml
 */
class FeaturedMediaAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions($variant): array {
    return [
      'title' => [
        [$this, 'assertElementText'],
        'div h2.ecl-u-type-heading-2.ecl-u-mt-2xl.ecl-u-mt-md-3xl.ecl-u-mb-l',
      ],
      'text' => [
        [$this, 'assertElementText'],
        'div .ecl-row div.ecl-col-md-6.ecl-editor',
      ],
      'caption' => [
        [$this, 'assertElementText'],
        'div.ecl-row figure figcaption.ecl-media-container__caption',
      ],
      'image' => [
        [$this, 'assertImage'],
      ],
      'video' => [
        [$this, 'assertVideo'],
      ],
      'video_ratio' => [
        [$this, 'assertRatio'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {
  }

  /**
   * Asserts the image of the pattern.
   *
   * @param array|null $expected_image
   *   The expected image values.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertImage($expected_image, Crawler $crawler): void {
    $image_div = $crawler->filter('div.ecl-row figure.ecl-media-container img');
    self::assertEquals($expected_image['alt'], $image_div->attr('alt'));
    self::assertEquals($expected_image['src'], $image_div->attr('src'));
  }

  /**
   * Asserts the video url of the pattern.
   *
   * @param string|null $expected_video
   *   The expected video url.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertVideo($expected_video, Crawler $crawler): void {
    $video = $crawler->filter('div .ecl-row figure.ecl-media-container iframe');
    self::assertEquals($expected_video, $video->attr('src'));
  }

  /**
   * Asserts the video ratio of the pattern.
   *
   * @param string|null $expected_ratio_class
   *   The expected class with the video ratio.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertRatio($expected_ratio_class, Crawler $crawler): void {
    self::assertElementExists('div .ecl-row figure.ecl-media-container div.' . $expected_ratio_class, $crawler);
  }

}
