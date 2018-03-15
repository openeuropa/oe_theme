<?php

namespace Drupal\Tests\oe_theme\Kernel;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Class RenderingTest.
 */
class RenderingTest extends AbstractKernelTest {

  /**
   * Test rendering.
   *
   * @param array $array
   *   Render array.
   * @param array $contains_string
   *   Strings that need to be present.
   * @param array $contains_element
   *   Elements that need to be present.
   *
   * @throws \Exception
   *
   * @dataProvider renderingDataProvider
   */
  public function testRendering(array $array, array $contains_string, array $contains_element) {
    $html = $this->renderRoot($array);
    $crawler = new Crawler($html);

    foreach ($contains_string as $string) {
      $this->assertContains($string, $html, sprintf('does not contain %s in %s', $string, $html));
    }

    foreach ($contains_element as $assertion) {
      $wrapper = $crawler->filter($assertion['filter']);
      $this->assertCount($assertion['expected_result'], $wrapper, sprintf('Wrong count for %s in %s', $assertion['filter'], $html));
    }
  }

  /**
   * Data provider.
   */
  public function renderingDataProvider() {
    return $this->getFixtureContent('rendering.yml');
  }

}
