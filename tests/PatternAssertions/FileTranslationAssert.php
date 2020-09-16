<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\PatternAssertions;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the file translations pattern.
 */
class FileTranslationAssert extends FileAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions($variant): array {
    $assertions = parent::getAssertions($variant);
    $assertions['translations'] = [
      [$this, 'assertTranslations'],
    ];
    return $assertions;
  }

  /**
   * Asserts the file translations on the pattern.
   *
   * @param array $expected_translations
   *   The expected translation values.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertTranslations(array $expected_translations, Crawler $crawler): void {
    if (is_null($expected_translations)) {
      $this->assertElementNotExists('div.ecl-file div.ecl-file__translation-container ul.ecl-file__translation-list li.ecl-file__translation-item', $crawler);
      return;
    }
    $translation_file_elements = $crawler->filter('div.ecl-file div.ecl-file__translation-container ul.ecl-file__translation-list li.ecl-file__translation-item');
    self::assertCount(count($expected_translations), $translation_file_elements);
    foreach ($expected_translations as $index => $expected_translation) {
      $this->assertTranslation($expected_translation, $translation_file_elements->eq($index));
    }
  }

  /**
   * Asserts the file translation on the pattern.
   *
   * @param array $expected_file
   *   The expected file values.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertTranslation(array $expected_file, Crawler $crawler): void {
    // Assert information.
    $file_info_element = $crawler->filter('div.ecl-file__translation-info');
    $this->assertElementText($expected_file['title'], ' div.ecl-file__title', $file_info_element);
    $this->assertElementText($expected_file['meta'], 'div.ecl-file__meta', $file_info_element);

    // Assert download link.
    $this->assertElementAttribute($expected_file['url'], 'a.ecl-file__translation-download', 'href', $crawler);
  }

}
