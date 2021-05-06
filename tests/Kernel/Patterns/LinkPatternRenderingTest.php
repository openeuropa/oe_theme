<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\Patterns;

use Drupal\Core\Url;
use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;

/**
 * Test link pattern rendering.
 *
 * @group batch2
 */
class LinkPatternRenderingTest extends AbstractKernelTestBase {

  /**
   * Test that link patterns are correctly rendered when passing an URL object.
   *
   * @throws \Exception
   */
  public function testLinkPatternRendering() {
    $pattern = [
      '#type' => 'pattern',
      '#id' => 'link',
      '#fields' => [
        'text' => 'Link text',
        'url' => Url::fromUserInput('/', [
          'attributes' => [
            'class' => ['foo'],
          ],
        ]),
      ],
    ];

    $html = $this->renderRoot($pattern);
    $this->assertEquals('<a href="/" class="foo ecl-link ecl-link--standalone">Link text</a>', trim($html));
  }

}
