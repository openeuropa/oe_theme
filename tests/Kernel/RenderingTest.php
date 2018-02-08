<?php

namespace Drupal\Tests\oe_theme\Kernel;

/**
 * Class RenderingTest.
 */
class RenderingTest extends AbstractKernelTest {

  /**
   * Test rendering.
   *
   * @param array $array
   *   Render array.
   * @param array $contains
   *   Contains assertions.
   * @param array $not_contains
   *   Not contains assertions.
   *
   * @dataProvider renderingDataProvider
   */
  public function testRendering(array $array, array $contains, array $not_contains) {
    $output = (string) \Drupal::service('renderer')->renderRoot($array);
    foreach ($contains as $text) {
      $this->assertContains($text, $output);
    }
    foreach ($not_contains as $text) {
      $this->assertNotContains($text, $output);
    }
  }

  /**
   * Data provider.
   */
  public function renderingDataProvider() {
    return $this->getFixtureContent('rendering.yml');
  }

}
