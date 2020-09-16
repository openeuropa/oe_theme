<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\PatternAssertions;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the file translations pattern.
 */
class FileTeaserAssert extends FileTranslationAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions($variant): array {
    $assertions = parent::getAssertions($variant);
    $assertions['thumbnail'] = [
      [$this, 'assertImage'],
    ];
    $assertions['teaser'] = [
      [$this, 'assertElementText'],
      'div.ecl-file--thumbnail div.ecl-file__container div.ecl-file__detail div.ecl-file__detail-info div.ecl-file__description',
    ];
    $assertions['meta'] = [
      [$this, 'assertMeta'],
    ];
    return $assertions;
  }

  /**
   * Asserts the image of the pattern.
   *
   * @param array|null $expected_image
   *   The expected image.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertImage($expected_image, Crawler $crawler): void {
    if (is_null($expected_image)) {
      $this->assertElementNotExists('div.ecl-file--thumbnail div.ecl-file__container div.ecl-file__detail div.ecl-file__image img', $crawler);
      return;
    }
    $this->assertElementAttribute($expected_image['src'], 'div.ecl-file--thumbnail div.ecl-file__container div.ecl-file__detail img.ecl-file__image', 'src', $crawler);
    $this->assertElementAttribute($expected_image['alt'], 'div.ecl-file--thumbnail div.ecl-file__container div.ecl-file__detail img.ecl-file__image', 'alt', $crawler);
  }

  /**
   * Asserts the meta of the pattern.
   *
   * @param string|null $expected_meta
   *   The expected meta items.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertMeta($expected_meta, Crawler $crawler): void {
    if (is_null($expected_meta)) {
      $this->assertElementNotExists('div.ecl-file--thumbnail div.ecl-file__container div.ecl-file__detail div.ecl-file__detail-info div.ecl-file__detail-meta span.ecl-file__detail-meta-item', $crawler);
      return;
    }
    $this->assertElementText($expected_meta, 'div.ecl-file--thumbnail div.ecl-file__container div.ecl-file__detail div.ecl-file__detail-info div.ecl-file__detail-meta', $crawler);
  }

  /**
   * {@inheritdoc}
   */
  protected function assertFile(array $expected_file, Crawler $crawler): void {
    // Assert title.
    $this->assertElementText($expected_file['title'], 'div.ecl-file--thumbnail div.ecl-file__container div.ecl-file__detail div.ecl-file__detail-info div.ecl-file__title', $crawler);

    // Assert information.
    $file_info_element = $crawler->filter('div.ecl-file--thumbnail div.ecl-file__container div.ecl-file__info');
    $this->assertElementText($expected_file['language'], 'div.ecl-file__language', $file_info_element);
    $this->assertElementText($expected_file['meta'], 'div.ecl-file__meta', $file_info_element);

    // Assert download link.
    $this->assertElementAttribute($expected_file['url'], 'div.ecl-file--thumbnail div.ecl-file__container a.ecl-file__download', 'href', $crawler);
  }

  /**
   * {@inheritdoc}
   */
  protected function assertTranslation(array $expected_file, Crawler $crawler): void {
    // Assert details.
    $file_info_element = $crawler->filter('div.ecl-file__translation-detail');
    $this->assertElementText($expected_file['title'], ' div.ecl-file__translation-title', $file_info_element);
    $this->assertElementText($expected_file['description'], 'div.ecl-file__translation-description', $file_info_element);

    // Assert information.
    $file_info_element = $crawler->filter('div.ecl-file__translation-info');
    $this->assertElementText($expected_file['language'], ' div.ecl-file__translation-language', $file_info_element);
    $this->assertElementText($expected_file['meta'], 'div.ecl-file__translation-meta', $file_info_element);

    // Assert download link.
    $this->assertElementAttribute($expected_file['url'], 'a.ecl-file__translation-download', 'href', $crawler);
  }

}
