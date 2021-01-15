<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\Patterns;

use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;
use Drupal\oe_theme\ValueObject\ImageValueObject;
use Drupal\oe_theme\ValueObject\MediaValueObject;

/**
 * Test media container pattern rendering.
 */
class MediaContainerPatternRenderingTest extends AbstractKernelTestBase {

  /**
   * Test that media container pattern is correctly rendered.
   *
   * @param array $fields
   *   Media data for the pattern.
   * @param array $assertions
   *   Test assertions.
   *
   * @throws \Exception
   *
   * @dataProvider dataProvider
   */
  public function testMediaContainerPattern(array $fields, array $assertions) {
    $pattern = [
      '#type' => 'pattern',
      '#id' => 'media_container',
      '#fields' => $fields,
    ];

    $html = $this->renderRoot($pattern);
    $this->assertRendering($html, $assertions);
  }

  /**
   * Data provider for testMediaContainerPattern.
   *
   * @return array
   *   Test data and assertions.
   */
  public function dataProvider(): array {
    $data = $this->getFixtureContent('patterns/media_container_pattern.yml');

    foreach ($data as $key => $value) {
      $media = $value['fields']['media'];

      if (isset($media['image'])) {
        $media['image'] = ImageValueObject::fromArray($media['image']);
      }

      $data[$key]['fields']['media'] = MediaValueObject::fromArray($media);
    }

    return $data;
  }

}
