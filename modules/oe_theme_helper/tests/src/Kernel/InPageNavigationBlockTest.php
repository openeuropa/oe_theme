<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\Kernel;

use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;
use Drupal\Tests\oe_theme\PatternAssertions\InPageNavigationAssert;

/**
 * Tests the inpage navigation block.
 *
 * @group batch2
 *
 * @group oe_theme_helper
 */
class InPageNavigationBlockTest extends AbstractKernelTestBase {

  /**
   * Disabled until FRONT-4076 is fixed.
   *
   * {@inheritdoc}
   */
  protected $failOnJavascriptConsoleErrors = FALSE;

  /**
   * Tests the block markup.
   */
  public function testBlockMarkup(): void {
    $build = $this->buildBlock('oe_theme_helper_inpage_navigation', []);

    $assert = new InPageNavigationAssert();
    $assert->assertPattern([
      'title' => 'Page contents',
      'list' => [],
    ], $this->renderRoot($build));

    $this->assertEquals([
      'library' => ['oe_theme/inpage_navigation'],
    ], $build['#attached']);
  }

}
