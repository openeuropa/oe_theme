<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\PatternAssertions;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the Highlighted list pattern.
 *
 * @see ./templates/patterns/highlighted_list/highlighted_list.ui_patterns.yml
 */
class HighlightedListAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions($variant): array {
    return [
      'title' => [
        [$this, 'assertElementText'],
        'div#highlighted-news-block h2.ecl-u-type-heading-2',
      ],
      'highlighted_item' => [
        [$this, 'assertHighlightedItem'],
        'div#highlighted-news-block div.ecl-col-12.ecl-col-l-8',
      ],
      'items' => [
        [$this, 'assertItems'],
        'div.highlighted-news-divider div.ecl-col-12.ecl-col-l-4.ecl-u-align-self-start',
      ],
      'see_more_label' => [
        [$this, 'assertElementText'],
        'div#highlighted-news-block > a.ecl-link.ecl-link--standalone .ecl-link__label',
      ],
      'see_more_url' => [
        [$this, 'assertElementAttribute'],
        'div#highlighted-news-block > a.ecl-link.ecl-link--standalone',
        'href',
      ],
      'detail' => [
        [$this, 'assertDetail'],
        'div#highlighted-news-block div.ecl.ecl-u-mt-s',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {
    $crawler = new Crawler($html);
    $highlighted_list = $crawler->filter('div#highlighted-news-block');
    self::assertCount(1, $highlighted_list);
  }

  /**
   * Asserts the Highlighted item of the list.
   *
   * @param array|null $expected_highlighted_item
   *   The expected item array (image array, title, url, primary meta array
   *   and secondary meta).
   * @param string $selector
   *   The CSS selector to find the item.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertHighlightedItem(?array $expected_highlighted_item, string $selector, Crawler $crawler) {
    if (is_null($expected_highlighted_item)) {
      $this->assertElementNotExists($selector, $crawler);
      return;
    }
    $this->assertElementExists($selector, $crawler);
    $highlighted_wrapper = $crawler->filter($selector);
    // Assert title.
    if (isset($expected_highlighted_item['title'])) {
      // Assert url, if available.
      if (isset($expected_highlighted_item['url'])) {
        $this->assertElementText($expected_highlighted_item['title'], '.ecl-content-block__title a.ecl-link.ecl-link--standalone', $highlighted_wrapper);
        $this->assertElementAttribute($expected_highlighted_item['url'], '.ecl-content-block__title a.ecl-link.ecl-link--standalone', 'href', $highlighted_wrapper);
      }
      else {
        $this->assertElementNotExists('.ecl-content-block__title a.ecl-link.ecl-link--standalone', $highlighted_wrapper);
        $this->assertElementText($expected_highlighted_item['title'], '.ecl-content-block__title', $highlighted_wrapper);
      }
    }
    // Assert image.
    if (isset($expected_highlighted_item['image'])) {
      $this->assertElementAttribute($expected_highlighted_item['image']['src'], 'picture.ecl-content-item__picture--large img.ecl-content-item__image', 'src', $highlighted_wrapper);
      $this->assertElementAttribute($expected_highlighted_item['image']['alt'], 'picture.ecl-content-item__picture--large img.ecl-content-item__image', 'alt', $highlighted_wrapper);
    }
    else {
      $this->assertElementNotExists('picture', $highlighted_wrapper);
    }
    // Assert primary meta.
    if (isset($expected_highlighted_item['primary_meta'])) {
      $actual_items = $highlighted_wrapper->filter('li.ecl-content-block__primary-meta-item');
      self::assertCount(count($expected_highlighted_item['primary_meta']), $actual_items);
      foreach ($expected_highlighted_item['primary_meta'] as $index => $expected_item) {
        self::assertEquals($expected_item, trim($actual_items->eq($index)->text()));
      }
    }
    else {
      $this->assertElementNotExists('ul.ecl-content-block__primary-meta-container', $highlighted_wrapper);
    }
    // Assert secondary meta.
    if (isset($expected_highlighted_item['secondary_meta'])) {
      $actual_item = $highlighted_wrapper->filter('li.ecl-content-block__secondary-meta-item');
      self::assertCount(1, $actual_item);
      self::assertEquals($expected_highlighted_item['secondary_meta'], trim($actual_item->text()));
    }
    else {
      $this->assertElementNotExists('li.ecl-content-block__secondary-meta-item', $highlighted_wrapper);
    }
    // Assert description.
    if (isset($expected_highlighted_item['description'])) {
      $this->assertElementText($expected_highlighted_item['description'], '.ecl-content-block__description', $highlighted_wrapper);
    }
    else {
      $this->assertElementNotExists('.ecl-content-block__description', $highlighted_wrapper);
    }
  }

  /**
   * Asserts the items on the right side of the highlighted list.
   *
   * @param array|null $expected_items
   *   The expected items.
   * @param string $selector
   *   The CSS selector to find the items' wrapper.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   */
  protected function assertItems(?array $expected_items, string $selector, Crawler $crawler): void {
    if (is_null($expected_items)) {
      $this->assertElementNotExists($selector, $crawler);
      return;
    }
    $this->assertElementExists($selector, $crawler);
    $items_wrapper = $crawler->filter($selector);
    // Assert the number of items.
    $items = $items_wrapper->filter('div.ecl-u-mb-m');
    self::assertCount(count($expected_items), $items, 'The expected number of items does not correspond with the actual number of items in the list.');
    // Assert there is no description element for any of the items.
    $this->assertElementNotExists('.ecl-content-block__description', $items_wrapper);

    // Assert each item's info.
    foreach ($expected_items as $index => $expected_item) {
      // Assert title.
      if (isset($expected_item['title'])) {
        // Assert url, if available.
        if (isset($expected_item['url'])) {
          $this->assertElementText($expected_item['title'], '.ecl-content-block__title a.ecl-link.ecl-link--standalone', $items->eq($index));
          $this->assertElementAttribute($expected_item['url'], '.ecl-content-block__title a.ecl-link.ecl-link--standalone', 'href', $items->eq($index));
        }
        else {
          $this->assertElementNotExists('.ecl-content-block__title a.ecl-link.ecl-link--standalone', $items->eq($index));
          $this->assertElementText($expected_item['title'], '.ecl-content-block__title', $items->eq($index));
        }
      }
      // Assert primary meta.
      if (isset($expected_item['primary_meta'])) {
        $actual_items = $items->eq($index)->filter('li.ecl-content-block__primary-meta-item');
        self::assertCount(count($expected_item['primary_meta']), $actual_items);
        foreach ($expected_item['primary_meta'] as $key => $primary_item) {
          self::assertEquals($primary_item, trim($actual_items->eq($key)->text()));
        }
      }
      else {
        $this->assertElementNotExists('ul.ecl-content-block__primary-meta-container', $items->eq($index));
      }
      // Assert secondary meta.
      if (isset($expected_item['secondary_meta'])) {
        $actual_item = $items->eq($index)->filter('li.ecl-content-block__secondary-meta-item');
        self::assertCount(1, $actual_item);
        self::assertEquals($expected_item['secondary_meta'], trim($actual_item->text()));
      }
      else {
        $this->assertElementNotExists('li.ecl-content-block__secondary-meta-item', $items->eq($index));
      }
      // Assert image if it's the first item.
      if (isset($expected_item['image']) && $index === 0) {
        $this->assertElementAttribute($expected_item['image']['src'], 'picture.ecl-content-item__picture--medium img.ecl-content-item__image', 'src', $highlighted_wrapper);
        $this->assertElementAttribute($expected_item['image']['alt'], 'picture.ecl-content-item__picture--medium img.ecl-content-item__image', 'alt', $highlighted_wrapper);
      }
      else {
        $this->assertElementNotExists('picture', $items->eq($index));
      }
    }
  }

  /**
   * Asserts the detail text from the footer of the highlighted list.
   *
   * @param array|null $expected
   *   The expected description values.
   * @param string $selector
   *   The CSS selector to find the detail.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertDescription($expected, string $selector, Crawler $crawler): void {
    if (is_null($expected)) {
      $this->assertElementNotExists($selector, $crawler);
      return;
    }
    $this->assertElementExists($selector, $crawler);
    $detail_element = $crawler->filter($selector);
    if ($expected instanceof PatternAssertStateInterface) {
      $expected->assert($detail_element->html());
      return;
    }
    self::assertEquals($expected, $detail_element->filter('p')->html());
  }

}
