<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\Patterns;

use Drupal\oe_theme\ValueObject\DateValueObject;
use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;

/**
 * Test date block pattern rendering.
 */
class DateBlockPatternRenderingTest extends AbstractKernelTestBase {

  /**
   * Test date block pattern rendering, built from an array.
   *
   * @param string $variant
   *   Date block variant.
   * @param array $date
   *   Date object passed as an array.
   * @param array $assertions
   *   Test assertions.
   *
   * @throws \Exception
   *
   * @dataProvider fromArrayDataProvider
   */
  public function testFromArray(string $variant, array $date, array $assertions) {
    $pattern = [
      '#type' => 'pattern',
      '#id' => 'date_block',
      '#variant' => $variant,
      '#fields' => [
        'date' => DateValueObject::fromArray($date),
      ],
    ];

    $html = $this->renderRoot($pattern);
    $this->assertRendering($html, $assertions);
  }

  /**
   * Data provider for testFromArray.
   *
   * @return array
   *   An array of test data arrays with assertions.
   */
  public function fromArrayDataProvider(): array {
    return $this->getFixtureContent('patterns/date_block_pattern_rendering.yml');
  }

}
