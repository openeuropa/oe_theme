<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\Patterns;

use Drupal\Core\Site\Settings;
use Drupal\file\Entity\File;
use Drupal\oe_theme\ValueObject\FileValueObject;
use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;

/**
 * Test file pattern rendering.
 */
class FilePatternRenderingTest extends AbstractKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'file',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('file');
    $this->installSchema('file', ['file_usage']);
  }

  /**
   * Test file pattern rendering.
   *
   * @param array $file
   *   A file array.
   * @param array $assertions
   *   Test assertions.
   *
   * @throws \Exception
   *
   * @dataProvider dataProvider
   */
  public function testFilePatternRendering(array $file, array $assertions) {
    $settings = Settings::getAll();
    $settings['file_public_base_url'] = 'http://example.com';
    new Settings($settings);

    $pattern = [
      '#type' => 'pattern',
      '#id' => 'file',
      '#fields' => [
        'button_label' => 'Download',
        'file' => FileValueObject::fromFileEntity(File::create($file)),
      ],
    ];

    $html = $this->renderRoot($pattern);
    $this->assertRendering($html, $assertions);
  }

  /**
   * Data provider for testFilePatternRendering.
   *
   * @return array
   *   An array of test data arrays with assertations.
   */
  public function dataProvider(): array {
    return $this->getFixtureContent('patterns/file_pattern_rendering.yml');
  }

}
