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
   * Test date block pattern rendering.
   *
   * @param string $variant
   *   Date block variant.
   * @param int $date
   *   Date object passed as a UNIX timestamp.
   * @param array $assertions
   *   Test assertions.
   *
   * @throws \Exception
   *
   * @dataProvider fromTimestampDataProvider
   */
  public function testFromTimestamp(string $variant, int $date, array $assertions) {
    $pattern = [
      '#type' => 'pattern',
      '#id' => 'date_block',
      '#variant' => $variant,
      '#fields' => [
        'date' => DateValueObject::fromTimestamp($date),
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
          'day' => '24',
          'month' => '09',
          'year' => '1981',
        ],
        'assertions' => [
          'count' => [
            'div.ecl-date-block.ecl-date-block--default' => 1,
            'div.ecl-date-block.ecl-date-block--cancelled' => 0,
            'div.ecl-date-block.ecl-date-block--past' => 0,
          ],
          'equals' => [
            'span.ecl-date-block__week-day' => 'Thursday',
            'span.ecl-date-block__day' => '24',
            'span.ecl-date-block__month' => '09',
            'span.ecl-date-block__year' => '1981',
          ],
        ],
      ],

      // Test "cancelled" variant.
      [
        'variant' => 'cancelled',
        'date' => [
          'day' => '24',
          'month' => '09',
          'year' => '1981',
        ],
        'assertions' => [
          'count' => [
            'div.ecl-date-block.ecl-date-block--default' => 0,
            'div.ecl-date-block.ecl-date-block--cancelled' => 1,
            'div.ecl-date-block.ecl-date-block--past' => 0,
          ],
          'equals' => [
            'span.ecl-date-block__week-day' => 'Thursday',
            'span.ecl-date-block__day' => '24',
            'span.ecl-date-block__month' => '09',
            'span.ecl-date-block__year' => '1981',
          ],
        ],
      ],

      // Test "past" variant.
      [
        'variant' => 'past',
        'date' => [
          'day' => '24',
          'month' => '09',
          'year' => '1981',
        ],
        'assertions' => [
          'count' => [
            'div.ecl-date-block.ecl-date-block--default' => 0,
            'div.ecl-date-block.ecl-date-block--cancelled' => 0,
            'div.ecl-date-block.ecl-date-block--past' => 1,
          ],
          'equals' => [
            'span.ecl-date-block__week-day' => 'Thursday',
            'span.ecl-date-block__day' => '24',
            'span.ecl-date-block__month' => '09',
            'span.ecl-date-block__year' => '1981',
          ],
        ],
      ],
    ];
  }

  /**
   * Data provider for testFromTimestamp.
   *
   * @return array
   *   An array of test data arrays with assertions.
   */
  public function fromTimestampDataProvider(): array {
    return [
      // Test "default" variant.
      [
        'variant' => 'default',
        'date' => 370173600,
        'assertions' => [
          'count' => [
            'div.ecl-date-block.ecl-date-block--default' => 1,
            'div.ecl-date-block.ecl-date-block--cancelled' => 0,
            'div.ecl-date-block.ecl-date-block--past' => 0,
          ],
          'equals' => [
            'span.ecl-date-block__week-day' => 'Thursday',
            'span.ecl-date-block__day' => '24',
            'span.ecl-date-block__month' => '09',
            'span.ecl-date-block__year' => '1981',
          ],
        ],
      ],

      // Test "cancelled" variant.
      [
        'variant' => 'cancelled',
        'date' => 370173600,
        'assertions' => [
          'count' => [
            'div.ecl-date-block.ecl-date-block--default' => 0,
            'div.ecl-date-block.ecl-date-block--cancelled' => 1,
            'div.ecl-date-block.ecl-date-block--past' => 0,
          ],
          'equals' => [
            'span.ecl-date-block__week-day' => 'Thursday',
            'span.ecl-date-block__day' => '24',
            'span.ecl-date-block__month' => '09',
            'span.ecl-date-block__year' => '1981',
          ],
        ],
      ],

      // Test "past" variant.
      [
        'variant' => 'past',
        'date' => 370173600,
        'assertions' => [
          'count' => [
            'div.ecl-date-block.ecl-date-block--default' => 0,
            'div.ecl-date-block.ecl-date-block--cancelled' => 0,
            'div.ecl-date-block.ecl-date-block--past' => 1,
          ],
          'equals' => [
            'span.ecl-date-block__week-day' => 'Thursday',
            'span.ecl-date-block__day' => '24',
            'span.ecl-date-block__month' => '09',
            'span.ecl-date-block__year' => '1981',
          ],
        ],
      ],
    ];
  }

}
