<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\PatternAssertions;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the file   pattern.
 */
class FileAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions($variant): array {
    return [
      'button_label' => [
        [$this, 'assertElementText'],
        'div.ecl-file div.ecl-file__container a.ecl-file__download',
      ],
      'file' => [
        [$this, 'assertFile'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {
  }

  /**
   * Asserts the file information on the pattern.
   *
   * @param array $expected_file
   *   The expected file values.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertFile(array $expected_file, Crawler $crawler): void {
    // Assert information.
    $file_info_element = $crawler->filter('div.ecl-file div.ecl-file__container div.ecl-file__info');
    $this->assertElementText($expected_file['title'], ' div.ecl-file__title', $file_info_element);
    $this->assertElementText($expected_file['language'], 'div.ecl-file__language', $file_info_element);
    $this->assertElementText($expected_file['meta'], 'div.ecl-file__meta', $file_info_element);

    // Assert download link.
    $this->assertElementAttribute($expected_file['url'], 'div.ecl-file div.ecl-file__container a.ecl-file__download', 'href', $crawler);

    // Assert icon.
    $crawler->filter('div.ecl-file div.ecl-file__container svg.ecl-file__icon use');
    self::assertContains($expected_file['icon'], $crawler->attr('xlink:href'));
  }

}
