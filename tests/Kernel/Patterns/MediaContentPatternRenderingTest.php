<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\Patterns;

use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;

/**
 * Test media content pattern rendering.
 */
class MediaContentPatternRenderingTest extends AbstractKernelTestBase {

  /**
   * Test that media patterns are correctly rendered when passing an URL object.
   *
   * @param array $media
   *   Media data for the pattern.
   * @param array $assertions
   *   Test assertions.
   *
   * @throws \Exception
   *
   * @dataProvider dataProvider
   */
  public function testMediaPattern(array $media, array $assertions) {
    $pattern = [
      '#type' => 'pattern',
      '#id' => 'media_container',
      '#fields' => [
        'description' => $media['description'],
        'media' => $media['media'],
      ],
    ];

    $html = $this->renderRoot($pattern);
    $this->assertRendering($html, $assertions);
  }

  /**
   * Data provider for testMediaPattern.
   *
   * @return array
   *   An array of test data arrays with assertions.
   */
  public function dataProvider(): array {
    return $this->getFixtureContent('patterns/media_content_pattern.yml');
  }

}
