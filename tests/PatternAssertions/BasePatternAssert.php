<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\PatternAssertions;

use PHPUnit\Framework\Assert;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Base class for asserting patterns.
 */
abstract class BasePatternAssert extends Assert {

  /**
   * Method that returns the assertions to be run by a particular pattern.
   *
   * Assertions extending this class need to return an array containing the
   * assertions to be run for every possible value that can be expected.
   *
   * @param string $variant
   *   The variant name that is being checked.
   *
   * @return array
   *   An array containing the assertions to be run.
   */
  abstract protected function getAssertions(string $variant): array;

  /**
   * Method that asserts the base elements of a rendered pattern.
   *
   * @param string $html
   *   The rendered pattern.
   * @param string $variant
   *   The variant being asserted.
   */
  abstract protected function assertBaseElements(string $html, string $variant): void;

  /**
   * Returns the variant of the provided rendered pattern.
   *
   * @param string $html
   *   The rendered pattern.
   *
   * @return string
   *   The variant of the rendered pattern.
   */
  protected function getPatternVariant(string $html): string {
    return 'default';
  }

  /**
   * Asserts that a rendered pattern is correct.
   *
   * @param array $expected
   *   An array of expected values, keyed by field name.
   * @param string $html
   *   THe rendered pattern.
   */
  public function assertPattern(array $expected, string $html): void {
    $variant = $this->getPatternVariant($html);
    $this->assertBaseElements($html, $variant);
    $map = $this->getAssertions($variant);
    $crawler = new Crawler($html);
    foreach ($expected as $name => $value) {
      if (isset($map[$name]) && is_array($map[$name]) && is_callable($map[$name][0])) {
        $callback = array_shift($map[$name]);
        $map[$name][] = $value;
        $map[$name][] = $crawler;
        call_user_func_array($callback, $map[$name]);
      }
    }
  }

  /**
   * Asserts that a rendered pattern uses a variant.
   *
   * @param string $variant
   *   The variant to check for.
   * @param string $html
   *   The rendered pattern.
   */
  public function assertVariant(string $variant, string $html): void {
    self::assertEquals($variant, $this->getPatternVariant($html));
  }

  /**
   * Asserts the value of an attribute of a particular element.
   *
   * @param string $selector
   *   The CSS selector to find the element.
   * @param string $attribute
   *   The name of the attribute to check.
   * @param string $expected
   *   The expected value.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertElementAttribute(string $selector, string $attribute, string $expected, Crawler $crawler): void {
    if (!$expected) {
      $this->assertElementNotExists($selector, $crawler);
      return;
    }
    $this->assertElementExists($selector, $crawler);
    $element = $crawler->filter($selector);
    self::assertEquals($expected, $element->attr($attribute));
  }

  /**
   * Asserts the text of a particular element.
   *
   * @param string $selector
   *   The CSS selector to find the element.
   * @param string $expected
   *   The expected value.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertElementText(string $selector, string $expected, Crawler $crawler): void {
    if (!$expected) {
      $this->assertElementNotExists($selector, $crawler);
      return;
    }
    $this->assertElementExists($selector, $crawler);
    $element = $crawler->filter($selector);
    self::assertEquals($expected, $element->text());
  }

  /**
   * Asserts the rendered html of a particular element.
   *
   * @param string $selector
   *   The CSS selector to find the element.
   * @param string $expected
   *   The expected value.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertElementHtml(string $selector, string $expected, Crawler $crawler): void {
    if (!$expected) {
      $this->assertElementNotExists($selector, $crawler);
      return;
    }
    $this->assertElementExists($selector, $crawler);
    $element = $crawler->filter($selector);
    self::assertEquals($expected, $element->html());
  }

  /**
   * Asserts that an element is present.
   *
   * @param string $selector
   *   The CSS selector to find the element.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertElementExists(string $selector, Crawler $crawler): void {
    $element = $crawler->filter($selector);
    self::assertCount(1, $element);
  }

  /**
   * Asserts that an element is not present.
   *
   * @param string $selector
   *   The CSS selector to find the element.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertElementNotExists(string $selector, Crawler $crawler): void {
    $element = $crawler->filter($selector);
    self::assertCount(0, $element);
  }

}
