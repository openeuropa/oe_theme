<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\PatternAssertions;

use PHPUnit\Framework\Assert;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Base class for asserting patterns.
 */
abstract class BasePatternAssert extends Assert {

//  abstract protected function getFieldXpathMapping(): array;

//  public function assertPattern(array $expected, string $html): void {
//    $crawler = new Crawler($html);
//    $map = $this->getFieldXpathMapping();
//    foreach ($expected as $name => $value) {
//      if (is_array($map[$value]) && is_callable($map[$name][0])) {
//        $callback = array_pop($map[$name]);
//        $map[$name][] = $value;
//        call_user_func_array($callback, $map[$name]);
//      }
//
//      $value = $crawler->filterXPath($map[$name]);
//
//    }
//  }

}
