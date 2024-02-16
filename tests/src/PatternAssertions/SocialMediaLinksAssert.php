<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\PatternAssertions;

use PHPUnit\Framework\Exception;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the social media link pattern.
 *
 * @package Drupal\Tests\oe_theme\PatternAssertions
 */
class SocialMediaLinksAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions(string $variant): array {
    return [
      'title' => [
        [$this, 'assertElementText'],
        'div' . $this->getBaseItemClass($variant) . ' p.ecl-social-media-follow__description',
      ],
      'links' => [
        [$this, 'assertLinks'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {
    $crawler = new Crawler($html);
    $field_list_container = $crawler->filter('div' . $this->getBaseItemClass($variant));
    self::assertCount(1, $field_list_container);
  }

  /**
   * Returns the base CSS selector of a pattern depending on the variant.
   *
   * @param string $variant
   *   The variant being checked.
   *
   * @return string
   *   The base selector for the variant.
   */
  protected function getBaseItemClass(string $variant): string {
    if ($variant === 'horizontal') {
      return '.ecl-social-media-follow.ecl-social-media-follow--horizontal';
    }
    return '.ecl-social-media-follow.ecl-social-media-follow--vertical';
  }

  /**
   * {@inheritdoc}
   */
  protected function getPatternVariant(string $html): string {
    $crawler = new Crawler($html);
    $field_list_container = $crawler->filter('div.ecl-social-media-follow');
    $existing_classes = $field_list_container->attr('class');
    $existing_classes = explode(' ', $existing_classes);
    if (in_array('ecl-social-media-follow--horizontal', $existing_classes)) {
      return 'horizontal';
    }
    if (in_array('ecl-social-media-follow--vertical', $existing_classes)) {
      return 'vertical';
    }
    throw new Exception('Variant of social media links pattern could not be identified.');
  }

  /**
   * Asserts the links of the social media links pattern.
   *
   * @param array $expected_items
   *   The expected item values.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertLinks(array $expected_items, Crawler $crawler): void {
    $li_items = $crawler->filter('li.ecl-social-media-follow__item');
    self::assertCount(count($expected_items), $li_items);

    foreach ($expected_items as $index => $expected_item) {
      $li_item = $li_items->eq($index);
      if (empty($expected_item['service'])) {
        // Service icon is absent.
        $link_element = $label_element = $li_item->filter('a.ecl-link.ecl-link--standalone.ecl-social-media-follow__link');
      }
      else {
        // Service icon exists.
        $link_element = $li_item->filter('a.ecl-link.ecl-link--standalone.ecl-link--icon.ecl-link--icon-before.ecl-social-media-follow__link');
        $label_element = $link_element->filter('span.ecl-link__label');
        $svg = $link_element->filter('svg.ecl-icon.ecl-icon--l.ecl-link__icon use');
        self::assertStringContainsString('icons-social-media.svg#' . $expected_item['service'], $svg->attr('xlink:href'));
      }

      self::assertEquals($expected_item['label'], trim($label_element->text()));
      self::assertEquals($expected_item['url'], $link_element->attr('href'));
    }
  }

}
