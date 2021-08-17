<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\PatternAssertions;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the featured item pattern.
 */
class FeaturedItemAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions($variant): array {
    return [
      'title' => [
        [$this, 'assertElementText'],
        'article.ecl-card .ecl-card__body h1.ecl-card__title a.ecl-link',
      ],
      'link' => [
        [$this, 'assertLink'],
        $variant,
      ],
      'description' => [
        [$this, 'assertElementText'],
        $variant == 'extended' ? 'article.ecl-card div.ecl-card__body div.ecl-card__description p.ecl-paragraph' : 'article.ecl-card div.ecl-card__body div.ecl-card__description',
      ],
      'image' => [
        [$this, 'assertFeaturedItemImage'],
        $variant,
      ],
      'meta' => [
        [$this, 'assertElementText'],
        'article.ecl-card .ecl-card__body div.ecl-card__meta',
      ],
      'footer_items' => [
        [$this, 'assertFooterItems'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {
    $crawler = new Crawler($html);
    $base_selector = 'article.ecl-card';
    $list_item = $crawler->filter($base_selector);
    self::assertCount(1, $list_item);
  }

  /**
   * Asserts the image block of a card.
   *
   * @param array|null $expected_image
   *   The expected image values.
   * @param string $variant
   *   The variant of the pattern being checked.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertFeaturedItemImage($expected_image, string $variant, Crawler $crawler): void {
    $image_div_selector = 'article.ecl-card div.ecl-card__image';
    if (is_null($expected_image)) {
      $this->assertElementNotExists($image_div_selector, $crawler);
      return;
    }
    $image_div = $crawler->filter($image_div_selector);
    self::assertEquals($expected_image['alt'], $image_div->attr('aria-label'));
    self::assertStringContainsString($expected_image['src'], $image_div->attr('style'));
  }

  /**
   * Asserts the link of a card.
   *
   * @param array|null $expected_link
   *   The expected link values.
   * @param string $variant
   *   The variant of the pattern being checked.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertLink($expected_link, string $variant, Crawler $crawler): void {
    // Assert the title url points to the correct direction.
    $this->assertElementAttribute($expected_link['href'], 'article.ecl-card .ecl-card__body h1.ecl-card__title a.ecl-link', 'href', $crawler);

    // If the variant is extended, assert that the button is correct.
    if ($variant == 'extended') {
      $this->assertElementAttribute($expected_link['href'], 'article.ecl-card div.ecl-card__body div.ecl-card__description a.ecl-button--call', 'href', $crawler);
      $this->assertElementText($expected_link['label'], 'article.ecl-card div.ecl-card__body div.ecl-card__description a.ecl-button--call span.ecl-button__container span.ecl-button__label', $crawler);
    }
  }

  /**
   * Asserts the info block of a card.
   *
   * @param array|null $expected_info_items
   *   The expected info values.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertFooterItems($expected_info_items, Crawler $crawler): void {
    $info_elements = $crawler->filter('article.ecl-card div.ecl-card__body ul.ecl-card__info-container li.ecl-card__info-item');
    self::assertCount(count($expected_info_items), $info_elements, 'The expected info items do not match the found info items.');
    foreach ($expected_info_items as $index => $expected_info_item) {
      $info_element = $info_elements->eq($index);
      $icon_element = $info_element->filter('svg.ecl-icon.ecl-icon--xs use');
      $this::assertStringContainsString('#' . $expected_info_item['icon'], $icon_element->attr('xlink:href'));
      $this->assertElementText($expected_info_item['text'], 'span.ecl-card__info-label', $info_element);
    }
  }

  /**
   * {@inheritdoc}
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   */
  protected function getPatternVariant(string $html): string {
    $crawler = new Crawler($html);
    $extended_button = $crawler->filter('a.ecl-button--call');
    if ($extended_button->count()) {
      return 'extended';
    }
    return 'default';
  }

}
