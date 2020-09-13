<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\Patterns;

use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;
use Drupal\oe_theme\ValueObject\ImageValueObject;
use Drupal\oe_theme\ValueObject\MediaValueObject;

/**
 * Test text featured media pattern rendering.
 */
class TextMediaRenderingTest extends AbstractKernelTestBase {

  /**
   * Test that text featured media pattern is correctly rendered.
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
      '#id' => 'text_featured_media',
      '#fields' => [
        'caption' => isset($media['caption']) ? $media['caption'] : '',
        'media' => isset($media['media']) ? $media['media'] : [],
        'title' => isset($media['title']) ? $media['title'] : '',
        'text' => isset($media['text']) ? $media['text'] : '',
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
    $data = $this->getFixtureContent('patterns/text_featured_media.yml');
    foreach ($data as $key => $value) {
      $media = $data[$key]['media'];
      if (isset($media['media']['image'])) {
        $media['media']['image'] = ImageValueObject::fromArray($media['media']['image']);
      }
      if (isset($media['media'])) {
        $data[$key]['media']['media'] = MediaValueObject::fromArray($media['media'])->getArray();
      }
    }
    return $data;
  }

}
