<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\PatternAssertions;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the card pattern.
 */
class FeaturedItemAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions($variant): array {
    return [
      'title' => [
        [$this, 'assertElementText'],
        'article.ecl-card header.ecl-card__header h1.ecl-card__title a.ecl-link',
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
        [$this, 'assertImage'],
        $variant,
      ],
      'meta' => [
        [$this, 'assertElementText'],
        'article.ecl-card header.ecl-card__header div.ecl-card__meta',
      ],
      'infos' => [
        [$this, 'assertInfo'],
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
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertImage($expected_image, Crawler $crawler): void {
    $image_div = $crawler->filter('article.ecl-card header.ecl-card__header div.ecl-card__image');
    self::assertEquals($expected_image['alt'], $image_div->attr('aria-label'));
    self::assertContains($expected_image['src'], $image_div->attr('style'));
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
    $this->assertElementAttribute($expected_link['href'], 'article.ecl-card header.ecl-card__header h1.ecl-card__title a.ecl-link', 'href', $crawler);;

    // If the variant is extended, assert that the button is correct.
    if ($variant == 'extended') {
      $this->assertElementAttribute($expected_link['href'], 'article.ecl-card header.ecl-card__header h1.ecl-card__title a.ecl-link', 'href', $crawler);;
      $this->assertElementText($expected_link['label'], 'article.ecl-card header.ecl-card__header h1.ecl-card__title a.ecl-link span.ecl-button__container span.ecl-button__label', $crawler);
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
  protected function assertInfo($expected_info_items, Crawler $crawler): void {
    $info_elements = $crawler->filter('article.ecl-card footer.ecl-card__footer ul.ecl-card__info-container li.ecl-card__info-item');
    self::assertCount(count($expected_info_items), $info_elements);
    foreach ($expected_info_items as $index => $expected_info_item) {
      $info_element = $info_elements->eq($index);
      $icon_element = $info_element->filter('svg.ecl-icon.ecl-icon--xs use');
      $this::assertContains('#general--' . $expected_info_item['icon'], $icon_element->attr('xlink:href'));
      $this->assertElementText($expected_info_item['icon'], 'span.ecl-card__info-label', $info_element);
    }
  }

}
