<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\PatternAssertions;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the field list pattern.
 *
 * @see ./templates/patterns/field_list/field_list.ui_patterns.yml
 */
class FieldListAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions(string $variant): array {
    return [
      'items' => [
        [$this, 'assertItems'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {
    $crawler = new Crawler($html);
    $field_list_container = $crawler->filter('dl' . $this->getBaseItemClass($variant));
    self::assertCount(1, $field_list_container);
  }

  /**
   * {@inheritdoc}
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   */
  protected function getPatternVariant(string $html): string {
    $crawler = new Crawler($html);
    $field_list_container = $crawler->filter('dl.ecl-description-list');
    $existing_classes = $field_list_container->attr('class');
    $existing_classes = explode(' ', $existing_classes);
    if (in_array('ecl-description-list--default', $existing_classes)) {
      return 'default';
    }
    if (in_array('ecl-description-list--featured', $existing_classes)) {
      return 'featured_horizontal';
    }
    return 'horizontal';
  }

  /**
   * Asserts the items of the pattern.
   *
   * @param array $expected_items
   *   The expected item values.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertItems(array $expected_items, Crawler $crawler): void {
    // Assert all labels are correct.
    $expected_labels = array_column($expected_items, 'label');
    $label_items = $crawler->filter('dt.ecl-description-list__term');
    self::assertCount(count($expected_labels), $label_items);
    foreach ($expected_labels as $index => $expected_label) {
      self::assertEquals($expected_label, trim($label_items->eq($index)->text()));
    }

    // Assert all values are correct.
    $expected_values = array_column($expected_items, 'body');
    $value_items = $crawler->filter('dd.ecl-description-list__definition');
    self::assertCount(count($expected_labels), $value_items);
    foreach ($expected_values as $index => $expected_value) {
      self::assertEquals($expected_value, trim($value_items->eq($index)->text()));
    }
  }

  /**
   * Returns the base CSS selector for a field item depending on the variant.
   *
   * @param string $variant
   *   The variant being checked.
   *
   * @return string
   *   The base selector for the variant.
   */
  protected function getBaseItemClass(string $variant): string {
    switch ($variant) {
      case 'horizontal':
        return '.ecl-description-list--horizontal';

      case 'featured_horizontal':
        return '.ecl-description-list--horizontal.ecl-description-list--featured';

      default:
        return 'ecl-description-list--default';
    }
  }

}
