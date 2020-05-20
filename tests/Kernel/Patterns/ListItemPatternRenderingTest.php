<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\Patterns;

use Drupal\Core\Render\Markup;
use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;

/**
 * List item pattern rendering.
 */
class ListItemPatternRenderingTest extends AbstractKernelTestBase {

  /**
   * Test markup trimming when used as "detail" field value.
   *
   * @param array $fields
   *   Pattern fields.
   * @param array $assertions
   *   Test assertions.
   *
   * @dataProvider dataProvider
   */
  public function testMarkupTrimming(array $fields, array $assertions): void {
    $pattern = [
      '#type' => 'pattern',
      '#id' => 'list_item',
      '#fields' => $fields,
    ];

    $html = $this->renderRoot($pattern);
    $this->assertRendering($html, $assertions);
  }

  /**
   * Data provider for testFilePatternRendering.
   *
   * @return array
   *   An array of test data arrays with assertions.
   */
  public function dataProvider(): array {
    return [
      'Markup passed via a render array' => [
        'fields' => [
          'title' => 'Title',
          'length' => 5,
          'detail' => [
            '#markup' => '<div class="class-name">Block content</div>',
          ],
        ],
        'assertions' => [
          'equals' => [
            '.ecl-content-item__description' => '<div class="class-name">Block...</div>',
          ],
        ],
      ],
      'Markup passed via a Makrup object' => [
        'fields' => [
          'title' => 'Title',
          'length' => 5,
          'detail' => Markup::create('<div class="class-name">Block content</div>'),
        ],
        'assertions' => [
          'equals' => [
            '.ecl-content-item__description' => '<div class="class-name">Block...</div>',
          ],
        ],
      ],
    ];
  }

}
