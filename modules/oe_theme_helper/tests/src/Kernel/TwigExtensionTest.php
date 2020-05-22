<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\Kernel;

use Drupal\Core\Render\Markup;
use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;

/**
 * Test those Twig extension filters that require Drupal to be bootstrapped.
 */
class TwigExtensionTest extends AbstractKernelTestBase {

  /**
   * Test smart_trim filter.
   *
   * @param array $variables
   *   Twig variables.
   * @param array $assertions
   *   Test assertions.
   *
   * @dataProvider smartTrimFilterDataProvider
   */
  public function testSmartTrimFilter(array $variables, array $assertions): void {
    $elements = [
      '#type' => 'inline_template',
      '#template' => '{{ content|smart_trim(length) }}',
      '#context' => [
        'content' => $variables['content'],
        'length' => $variables['length'],
      ],
    ];

    $html = $this->renderRoot($elements);
    $this->assertRendering($html, $assertions);
  }

  /**
   * Data provider for testSmartTrimFilter.
   *
   * @return array
   *   An array of test data arrays with assertions.
   */
  public function smartTrimFilterDataProvider(): array {
    return [
      'Trim a string' => [
        'variables' => [
          'length' => 25,
          'content' => 'This is a very long text that is going to be trimmed for good.',
        ],
        'assertions' => [
          'contains' => [
            'This is a very long text...',
          ],
        ],
      ],
      'Do not trim a string if length is NULL' => [
        'variables' => [
          'length' => NULL,
          'content' => 'This is a very long text that is not going to be trimmed.',
        ],
        'assertions' => [
          'contains' => [
            'This is a very long text that is not going to be trimmed.',
          ],
        ],
      ],
      'Trim a string containing malicious HTML' => [
        'variables' => [
          'length' => 5,
          'content' => '<script>document.getElementsByTagName("body").innerHTML = "Hello JavaScript!"</script>',
        ],
        'assertions' => [
          'contains' => [
            "&lt;script&gt;\n&lt;!--//--&gt;&lt;![CDATA[// &gt;&lt;!--\ndocum...\n//--&gt;&lt;!]]&gt;\n&lt;/script&gt;",
          ],
        ],
      ],
      'Trim a markup render array' => [
        'variables' => [
          'length' => 5,
          'content' => [
            '#markup' => '<div class="class-name">Block content</div>',
          ],
        ],
        'assertions' => [
          'contains' => [
            '<div class="class-name">Block...</div>',
          ],
        ],
      ],
      'Trim a Markup object' => [
        'variables' => [
          'length' => 5,
          'content' => Markup::create('<div class="class-name">Block content</div>'),
        ],
        'assertions' => [
          'contains' => [
            '<div class="class-name">Block...</div>',
          ],
        ],
      ],
      'Trim a plain_text render array' => [
        'variables' => [
          'length' => 25,
          'content' => [
            '#plain_text' => 'This is a very long text that is going to be trimmed for good.',
          ],
        ],
        'assertions' => [
          'contains' => [
            'This is a very long text...',
          ],
        ],
      ],
      'Do not trim a plain_text render array when length is NULL' => [
        'variables' => [
          'length' => NULL,
          'content' => [
            '#plain_text' => 'This is a very long text that is not going to be trimmed.',
          ],
        ],
        'assertions' => [
          'contains' => [
            'This is a very long text that is not going to be trimmed.',
          ],
        ],
      ],
      'Trim a plain_text render array that contains HTML' => [
        'variables' => [
          'length' => 20,
          'content' => [
            '#plain_text' => '<div class="class-name">Block content</div>',
          ],
        ],
        'assertions' => [
          'contains' => [
            '&lt;div class="class...',
          ],
        ],
      ],
      'Trim a render array returning a complex output' => [
        'variables' => [
          'length' => 25,
          'content' => [
            '#type' => 'pattern',
            '#id' => 'blockquote',
            '#fields' => [
              'body' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor.',
              'author' => 'John Doe',
            ],
          ],
        ],
        'assertions' => [
          'contains' => [
            '<blockquote class="ecl-blockquote"><p class="ecl-blockquote__body">Lorem ipsum dolor sit...</p></blockquote>',
          ],
        ],
      ],
    ];
  }

}
