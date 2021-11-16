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
    $assertions['thumbnail'] = [
      [$this, 'assertImage'],
      'div.ecl-file--thumbnail div.ecl-file__container div.ecl-file__detail img.ecl-file__image',
    ];
    $assertions['highlighted'] = [
      [$this, 'assertHighlighted'],
      'div.ecl-file--thumbnail div.ecl-file__container div.ecl-file__detail div.ecl-file__detail-info div.ecl-file__label span.ecl-label.ecl-label--highlight',
    ];
    return $assertions;
  }

  /**
   * Asserts the file translations on the pattern.
   *
   * @param array|null $expected_translations
   *   The expected translation values.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertTranslations($expected_translations, Crawler $crawler): void {
    if (is_null($expected_translations)) {
      $this->assertElementNotExists('div.ecl-file div.ecl-file__translation-container ul.ecl-file__translation-list li.ecl-file__translation-item', $crawler);
      return;
    }
    $translation_file_elements = $crawler->filter('div.ecl-file div.ecl-file__translation-container ul.ecl-file__translation-list li.ecl-file__translation-item:not([class*="ecl-file__translation-description"])');
    self::assertCount(count($expected_translations), $translation_file_elements, \sprintf(
      'The amount of found translations (%s) does not match the amount of expected translations (%s).',
      $translation_file_elements->count(),
      count($expected_translations)
    ));
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
    $this->assertElementText($expected_file['title'], 'div.ecl-file__translation-title', $crawler->filter('div.ecl-file__translation-detail'));
    $this->assertElementText($expected_file['meta'], 'div.ecl-file__translation-meta', $crawler->filter('div.ecl-file__translation-info'));

    // Assert download link.
    $this->assertElementAttribute($expected_file['url'], 'a.ecl-file__translation-download', 'href', $crawler);
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
    $file_info_element = $crawler->filter('div.ecl-file.ecl-file--thumbnail div.ecl-file__container div.ecl-file__info');
    $this->assertElementText($expected_file['title'], 'div.ecl-file__detail-info div.ecl-file__title', $crawler->filter('div.ecl-file.ecl-file--thumbnail div.ecl-file__container div.ecl-file__detail'));
    $this->assertElementText($expected_file['language'], 'div.ecl-file__language', $file_info_element);
    $this->assertElementText($expected_file['meta'], 'div.ecl-file__meta', $file_info_element);

    // Assert download link.
    $this->assertElementAttribute($expected_file['url'], 'div.ecl-file.ecl-file--thumbnail div.ecl-file__container a.ecl-file__download', 'href', $crawler);
  }

  /**
   * Asserts the highlighted badge of the file.
   *
   * @param bool $expected
   *   Whether the file is highlighted or not.
   * @param string $selector
   *   The CSS selector to find the element.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertHighlighted(bool $expected, string $selector, Crawler $crawler): void {
    if (!$expected) {
      $this->assertElementNotExists($selector, $crawler);
      return;
    }
    $this->assertElementExists($selector, $crawler);
    $element = $crawler->filter($selector);
    self::assertStringContainsString('Highlighted', $element->text());
  }

}
