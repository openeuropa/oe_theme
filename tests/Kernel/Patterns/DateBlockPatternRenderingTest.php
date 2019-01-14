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
    return [
      // Test "default" variant.
      [
        'variant' => 'default',
        'date' => [
          'start' => 370173600,
          'end' => NULL,
          'timezone' => 'Europe/Brussels',
        ],
        'assertions' => [
          'count' => [
            'div.ecl-date-block.ecl-date-block--default' => 1,
            'div.ecl-date-block.ecl-date-block--cancelled' => 0,
            'div.ecl-date-block.ecl-date-block--past' => 0,
          ],
          'equals' => [
            'span.ecl-date-block__week-day' => 'Thu',
            'span.ecl-date-block__day' => '24',
            'span.ecl-date-block__month' => 'Sep',
            'span.ecl-date-block__year' => '1981',
          ],
        ],
      ],

      // Test "cancelled" variant.
      [
        'variant' => 'cancelled',
        'date' => [
          'start' => 370173600,
          'end' => NULL,
          'timezone' => 'Europe/Brussels',
        ],
        'assertions' => [
          'count' => [
            'div.ecl-date-block.ecl-date-block--default' => 0,
            'div.ecl-date-block.ecl-date-block--cancelled' => 1,
            'div.ecl-date-block.ecl-date-block--past' => 0,
          ],
          'equals' => [
            'span.ecl-date-block__week-day' => 'Thu',
            'span.ecl-date-block__day' => '24',
            'span.ecl-date-block__month' => 'Sep',
            'span.ecl-date-block__year' => '1981',
          ],
        ],
      ],

      // Test "past" variant.
      [
        'variant' => 'past',
        'date' => [
          'start' => 370173600,
          'end' => NULL,
          'timezone' => 'Europe/Brussels',
        ],
        'assertions' => [
          'count' => [
            'div.ecl-date-block.ecl-date-block--default' => 0,
            'div.ecl-date-block.ecl-date-block--cancelled' => 0,
            'div.ecl-date-block.ecl-date-block--past' => 1,
          ],
          'equals' => [
            'span.ecl-date-block__week-day' => 'Thu',
            'span.ecl-date-block__day' => '24',
            'span.ecl-date-block__month' => 'Sep',
            'span.ecl-date-block__year' => '1981',
          ],
        ],
      ],
    ];
  }

}
