<?php

declare(strict_types=1);

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
        'article.ecl-card .ecl-card__body div.ecl-content-block__title a.ecl-link',
      ],
      'link' => [
        [$this, 'assertLink'],
        $variant,
      ],
      'description' => [
        [$this, 'assertElementText'],
        $variant == 'extended' ? 'article.ecl-card div.ecl-card__body div.ecl-content-block__description p.ecl-paragraph' : 'article.ecl-card div.ecl-card__body div.ecl-content-block__description',
      ],
      'image' => [
        [$this, 'assertFeaturedItemImage'],
        $variant,
      ],
      'meta' => [
        [$this, 'assertPrimaryMeta'],
      ],
      'footer_items' => [
        [$this, 'assertFooterItems'],
      ],
      'badges' => [
        [$this, 'assertBadges'],
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
    $image_div_selector = 'article.ecl-card img.ecl-card__image';
    if (is_null($expected_image)) {
      $this->assertElementNotExists($image_div_selector, $crawler);
      return;
    }
    $image_div = $crawler->filter($image_div_selector);
    self::assertEquals($expected_image['alt'], $image_div->attr('alt'));
    self::assertStringContainsString($expected_image['src'], $image_div->attr('src'));
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
    $this->assertElementAttribute($expected_link['href'], 'article.ecl-card .ecl-card__body div.ecl-content-block__title a.ecl-link', 'href', $crawler);

    // If the variant is extended, assert that the button is correct.
    if ($variant == 'extended') {
      $this->assertElementAttribute($expected_link['href'], 'article.ecl-card div.ecl-card__body div.ecl-content-block__description a.ecl-button--call', 'href', $crawler);
      $this->assertElementText($expected_link['label'], 'article.ecl-card div.ecl-card__body div.ecl-content-block__description a.ecl-button--call span.ecl-button__container span.ecl-button__label', $crawler);
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
  protected function assertFooterItems(?array $expected_info_items, Crawler $crawler): void {
    if (is_null($expected_info_items)) {
      $this->assertElementNotExists('article.ecl-card .ecl-card__body ul.ecl-content-block__secondary-meta-container', $crawler);
      return;
    }
    $info_elements = $crawler->filter('article.ecl-card div.ecl-card__body ul.ecl-content-block__secondary-meta-container li.ecl-content-block__secondary-meta-item');
    self::assertCount(count($expected_info_items), $info_elements, 'The expected info items do not match the found info items.');
    foreach ($expected_info_items as $index => $expected_info_item) {
      $info_element = $info_elements->eq($index);
      $icon_element = $info_element->filter('svg.ecl-icon.ecl-icon--s.ecl-content-block__secondary-meta-icon use');
      $this::assertStringContainsString('#' . $expected_info_item['icon'], $icon_element->attr('xlink:href'));
      $this->assertElementText($expected_info_item['text'], 'span.ecl-content-block__secondary-meta-label', $info_element);
    }
  }

  /**
   * Asserts the badge(s) of a card.
   *
   * @param array|null $expected_badges
   *   The expected badges.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertBadges(?array $expected_badges, Crawler $crawler): void {
    $base_selector = 'article.ecl-card div.ecl-card__body ul.ecl-content-block__label-container li.ecl-content-block__label-item';
    if (is_null($expected_badges)) {
      $this->assertElementNotExists('span.ecl-label', $crawler);
      return;
    }
    foreach ($expected_badges as $badge) {
      if (!isset($badge['label']) || !isset($badge['variant'])) {
        continue;
      }
      $selector = $base_selector . ' span.ecl-label.ecl-label--' . $badge['variant'];
      self::assertStringContainsString($badge['label'], $crawler->filter($selector)->text());
    }
  }

  /**
   * Asserts the primary meta items of a card.
   *
   * @param aray|null $expected_items
   *   The expected items.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertPrimaryMeta(?array $expected_items, Crawler $crawler): void {
    if (is_null($expected_items)) {
      $this->assertElementNotExists('article.ecl-card .ecl-card__body ul.ecl-content-block__primary-meta-container', $crawler);
      return;
    }
    $primary_meta_items = $crawler->filter('article.ecl-card .ecl-card__body ul.ecl-content-block__primary-meta-container li.ecl-content-block__primary-meta-item');
    self::assertCount(count($expected_items), $primary_meta_items, 'The expected primary meta items do not match the found meta items.');
    foreach ($expected_items as $index => $expected_item) {
      $primary_item = $primary_meta_items->eq($index);
      $this->assertEquals($expected_item, $primary_item->text());
    }
  }

  /**
   * {@inheritdoc}
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
