<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\Patterns;

use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;
use Drupal\oe_theme\ValueObject\ImageValueObject;
use Drupal\oe_theme\ValueObject\MediaValueObject;

/**
 * Test text featured media pattern rendering.
 */
class TextFeaturedMediaPatternTest extends AbstractKernelTestBase {

  /**
   * Test that text featured media pattern is correctly rendered.
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
  public function testTextFeaturedMediaPattern(array $fields, array $assertions) {
    $pattern = [
      '#type' => 'pattern',
      '#id' => 'text_featured_media',
      '#fields' => $fields,
    ];

    $html = $this->renderRoot($pattern);
    $this->assertRendering($html, $assertions);
  }

  /**
   * Data provider for testMediaPattern.
   *
   * @return array
   *   Test data and assertions.
   */
  public function dataProvider(): array {
    $data = $this->getFixtureContent('patterns/text_featured_media.yml');

    foreach ($data as $key => $value) {
      if (!isset($value['fields']['media'])) {
        continue;
      }

      $media = $value['fields']['media'];

      if (isset($media['image'])) {
        $media['image'] = ImageValueObject::fromArray($media['image']);
      }

      $data[$key]['fields']['media'] = MediaValueObject::fromArray($media);
    }

    return $data;
  }

}
