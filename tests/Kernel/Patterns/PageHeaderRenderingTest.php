<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\Patterns;

use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;

/**
 * Test page header pattern rendering.
 */
class PageHeaderRenderingTest extends AbstractKernelTestBase {

  /**
   * Test pattern rendering with different branding configuration.
   *
   * @param string $branding
   *   Branding value, either "core" or "standardised".
   * @param array $pattern
   *   Pattern array.
   * @param array $assertions
   *   Test assertions.
   *
   * @dataProvider dataProvider
   */
  public function testRendering(string $branding, array $pattern, array $assertions) {
    \Drupal::configFactory()
      ->getEditable('oe_theme.settings')
      ->set('branding', $branding)->save();
    $html = $this->renderRoot($pattern);
    $this->assertRendering($html, $assertions);
  }

  /**
   * Data provider for testRendering.
   *
   * @return array
   *   An array of test data and assertions.
   */
  public function dataProvider(): array {
    return $this->getFixtureContent('patterns/page_header_rendering.yml');
  }

}
