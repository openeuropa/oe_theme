<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\PatternAssertions;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the file pattern.
 */
class FileAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions($variant): array {
    return [
      'button_label' => [
        [$this, 'assertElementText'],
        'div.ecl-file footer.ecl-file__footer a.ecl-file__download span.ecl-link__label span:first-child',
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
    $file_container = $crawler->filter('div.ecl-file article.ecl-file__container');
    $this->assertElementText($expected_file['title'], 'div.ecl-file__title', $file_container);
    $file_footer = $crawler->filter('div.ecl-file footer.ecl-file__footer');

    $this->assertElementText($expected_file['language'], 'span.ecl-file__language', $file_footer);
    $this->assertElementText($expected_file['meta'], 'span.ecl-file__meta', $file_footer);

    // Assert download link.
    $this->assertElementAttribute($expected_file['url'], 'div.ecl-file footer.ecl-file__footer a.ecl-file__download', 'href', $crawler);

    // Assert icon.
    self::assertCount(1, $crawler->filter('div.ecl-file article.ecl-file__container span.ecl-file__icon.wt-icon-phosphor--' . $expected_file['icon']));
  }

}
