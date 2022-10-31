<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\PatternAssertions;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the list item pattern.
 *
 * @see ./templates/patterns/list_item/list_item.ui_patterns.yml
 */
class ListItemAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions($variant): array {
    if ($variant == 'highlight') {
      return [
        'title' => [
          [$this, 'assertElementText'],
          'article.ecl-card header.ecl-card__header h1.ecl-content-block__title a.ecl-link',
        ],
        'url' => [
          [$this, 'assertElementAttribute'],
          'article.ecl-card header.ecl-card__header h1.ecl-content-block__title a.ecl-link',
          'href',
        ],
        'image' => [
          [$this, 'assertHighlightImage'],
        ],
      ];
    }

    return [
      'title' => [
        [$this, 'assertElementText'],
        'article.ecl-content-item div.ecl-content-item__content-block h1.ecl-content-block__title',
      ],
      'url' => [
        [$this, 'assertElementAttribute'],
        'article.ecl-content-item div.ecl-content-item__content-block h1.ecl-content-block__title a.ecl-link.ecl-link--standalone',
        'href',
      ],
      'meta' => [
        [$this, 'assertPrimaryMeta'],
      ],
      'secondary_meta' => [
        [$this, 'assertSecondaryMeta'],
      ],
      'date' => [
        [$this, 'assertDate'],
        $variant,
      ],
      'description' => [
        [$this, 'assertDescription'],
      ],
      'image' => [
        [$this, 'assertThumbnailImage'],
        $variant,
      ],
      'lists' => [
        [$this, 'assertLists'],
      ],
      'icon' => [
        [$this, 'assertIcon'],
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
    $list_item = $crawler->filter('article.ecl-content-item');
    self::assertCount(1, $list_item);
  }

  /**
   * {@inheritdoc}
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   */
  protected function getPatternVariant(string $html): string {
    $crawler = new Crawler($html);
    // Check whether it is a date pattern and if so, which one.
    $date_variants_selector = 'time.ecl-date-block.ecl-content-item__date';
    $time_element = $crawler->filter($date_variants_selector);
    if ($time_element->count()) {
      switch ($time_element->attr('class')) {
        case 'ecl-date-block ecl-date-block--ongoing ecl-content-item__date':
          return 'date_ongoing';

        case 'ecl-date-block ecl-date-block--cancelled ecl-content-item__date':
          return 'date_cancelled';

        case 'ecl-date-block ecl-date-block--past ecl-content-item__date':
          return 'date_past';

        default:
          return 'date';
      }
    }
    // Check whether it is a card and if so, check if it is a highlight or a
    // block.
    $card_element = $crawler->filter('article.ecl-card');
    if ($card_element->count()) {
      // Try to find an image.
      $image = $card_element->filter('div.ecl-card__image');
      if ($image->count()) {
        return 'highlight';
      }
      return 'block';
    }

    // Check whether it is a primary or secondary thumbnail.
    $primary_thumbnail = $crawler->filter('picture.ecl-content-item__picture--left');
    if ($primary_thumbnail->count()) {
      return 'thumbnail_primary';
    }
    $secondary_thumbnail = $crawler->filter('picture.ecl-content-item__picture--right');
    if ($secondary_thumbnail->count()) {
      return 'thumbnail_secondary';
    }
    // At this point, it's either a navigation or a default pattern. Currently,
    // there is no possible way to know because the only difference is whether
    // metadata is present or not and the metadata is an optional field.
    // Assume default for now.
    return 'default';
  }

  /**
   * Asserts the date block of a list item.
   *
   * @param array|null $expected_date
   *   The expected date values.
   * @param string $variant
   *   The variant of the pattern being checked.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertDate(?array $expected_date, string $variant, Crawler $crawler): void {
    $date_block_selector = 'time.ecl-date-block.ecl-content-item__date';
    switch ($variant) {
      case 'date_ongoing':
        $date_block_selector = $date_block_selector . '.ecl-date-block--ongoing';
        break;

      case 'date_past':
        $date_block_selector = $date_block_selector . '.ecl-date-block--past';
        break;

      case 'date_cancelled':
        $date_block_selector = $date_block_selector . '.ecl-date-block--cancelled';
        break;
    }
    if (is_null($expected_date)) {
      $this->assertElementNotExists($date_block_selector, $crawler);
      return;
    }
    $this->assertElementExists($date_block_selector, $crawler);
    $date_block = $crawler->filter($date_block_selector);
    self::assertEquals($expected_date['day'], $date_block->filter('span.ecl-date-block__day')->text());
    self::assertEquals($expected_date['month_name'], $date_block->filter('abbr.ecl-date-block__month')->text());
    self::assertEquals($expected_date['year'], $date_block->filter('span.ecl-date-block__year')->text());
  }

  /**
   * Asserts the image block of a thumbnail list item.
   *
   * @param array|null $expected_image
   *   The expected image values.
   * @param string $variant
   *   The variant of the pattern being checked.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertThumbnailImage(?array $expected_image, string $variant, Crawler $crawler): void {
    $variant_class = $variant === 'thumbnail_primary' ? 'picture.ecl-content-item__picture--large.ecl-content-item__picture--left' : 'picture.ecl-content-item__picture--large.ecl-content-item__picture--right';
    $image_div_selector = $variant_class . ' img.ecl-content-item__image';
    if (is_null($expected_image)) {
      $this->assertElementNotExists($image_div_selector, $crawler);
      return;
    }
    $this->assertElementExists($image_div_selector, $crawler);
    $image_div = $crawler->filter($image_div_selector);
    self::assertEquals($expected_image['alt'], $image_div->attr('alt'));
    self::assertStringContainsString($expected_image['src'], $image_div->attr('src'));
  }

  /**
   * Asserts the image block of a highlight list item.
   *
   * @param array|null $expected_image
   *   The expected image values.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertHighlightImage(?array $expected_image, Crawler $crawler): void {
    $image_div = $crawler->filter('article.ecl-card header.ecl-card__header div.ecl-card__image');
    self::assertEquals($expected_image['alt'], $image_div->attr('aria-label'));
    self::assertStringContainsString($expected_image['src'], $image_div->attr('style'));
  }

  /**
   * Asserts the description of the list item.
   *
   * @param array|null $expected
   *   The expected description values.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertDescription($expected, Crawler $crawler): void {
    $description_selector = 'article.ecl-content-item div.ecl-content-item__content-block div.ecl-content-block__description';
    if (is_null($expected)) {
      $this->assertElementNotExists($description_selector, $crawler);
      return;
    }
    $this->assertElementExists($description_selector, $crawler);
    $description_element = $crawler->filter($description_selector);
    if ($expected instanceof PatternAssertStateInterface) {
      $expected->assert($description_element->html());
      return;
    }
    self::assertEquals($expected, $description_element->filter('p')->html());
  }

  /**
   * Asserts the additional information of the list item.
   *
   * @param array|null $expected
   *   The expected additional information items.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertLists($expected, Crawler $crawler): void {
    $list_container_selector = 'div.ecl-content-block__list-container dl.ecl-description-list.ecl-content-block__list';
    if (!isset($expected['variant'])) {
      $list_container_selector = $list_container_selector . '.ecl-description-list--horizontal';
    }
    else {
      $list_container_selector = $list_container_selector . '.ecl-description-list--' . $expected['variant'];
    }
    if (empty($expected)) {
      $this->assertElementNotExists($list_container_selector, $crawler);
      return;
    }
    $list_terms = $crawler->filter($list_container_selector . ' dt.ecl-description-list__term');
    $list_definitions = $crawler->filter($list_container_selector . ' dd.ecl-description-list__definition');
    foreach ($expected['items'] as $index => $item) {
      self::assertEquals($item['label'], trim($list_terms->eq($index)->text()), \sprintf('The expected text of the term number %s does not correspond to the found term text.', $index));
      self::assertEquals($item['body'], trim($list_definitions->eq($index)->text()), \sprintf('The expected text of the definition number %s does not correspond to the found definition text.', $index));
    }
  }

  /**
   * Asserts the icon of the list item link.
   *
   * @param string|null $expected
   *   The expected icon.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertIcon(?string $expected, Crawler $crawler): void {
    $icon_selector = 'a.ecl-link.ecl-link--standalone.ecl-link--icon.ecl-link--icon-after svg.ecl-icon.ecl-icon--s.ecl-link__icon use';
    if (is_null($expected)) {
      $this->assertElementNotExists($icon_selector, $crawler);
      return;
    }
    $icon = $crawler->filter($icon_selector);
    self::assertStringContainsString($expected, $icon->attr('xlink:href'));
  }

  /**
   * Asserts the badge(s) of the list item link.
   *
   * @param array|null $expected_badges
   *   The expected badges.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertBadges($expected_badges, Crawler $crawler): void {
    $base_selector = 'article.ecl-content-item .ecl-content-block__label-container';
    if (is_null($expected_badges)) {
      $this->assertElementNotExists($base_selector, $crawler);
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
   * Asserts the primary meta items of the list item.
   *
   * @param array|null $expected_items
   *   The expected primary meta items.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertPrimaryMeta($expected_items, Crawler $crawler): void {
    if (is_null($expected_items)) {
      $this->assertElementNotExists('.ecl-content-block__primary-meta-container', $crawler);
      return;
    }
    $actual_items = $crawler->filter('li.ecl-content-block__primary-meta-item');
    self::assertCount(count($expected_items), $actual_items);
    foreach ($expected_items as $index => $expected_item) {
      self::assertEquals($expected_item, trim($actual_items->eq($index)->text()));
    }
  }

  /**
   * Asserts the secondary meta items of the list item.
   *
   * @param array|null $expected_items
   *   The expected secondary meta items.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertSecondaryMeta($expected_items, Crawler $crawler): void {
    if (is_null($expected_items)) {
      $this->assertElementNotExists('.ecl-content-block__secondary-meta-container', $crawler);
      return;
    }
    $actual_items = $crawler->filter('div.ecl-content-item__content-block ul.ecl-content-block__secondary-meta-container li.ecl-content-block__secondary-meta-item');
    self::assertCount(count($expected_items), $actual_items, 'The expected secondary meta items do not match the found items.');
    foreach ($expected_items as $index => $expected_item) {
      $info_element = $actual_items->eq($index);
      $icon_element = $info_element->filter('svg.ecl-icon.ecl-icon--s.ecl-content-block__secondary-meta-icon use');
      $this::assertStringContainsString('#' . $expected_item['icon'], $icon_element->attr('xlink:href'));
      $this->assertElementText($expected_item['text'], 'span.ecl-content-block__secondary-meta-label', $info_element);
    }
  }

}
