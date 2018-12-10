<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\file\Entity\File;
use Drupal\oe_theme\ValueObject\FileValueObject;
use Symfony\Component\DomCrawler\Crawler;

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
   * @dataProvider dataProvider
   */
  public function testFilePatternRendering($file) {
    $pattern = [
      '#type' => 'pattern',
      '#id' => 'file',
      '#fields' => [
        'button_label' => 'Download',
        'file' => FileValueObject::fromFileEntity(File::create($file)),
      ],
    ];

    $html = $this->renderRoot($pattern);
    $crawler = new Crawler($html);

    $actual = trim($crawler->filter('.ecl-file__properties')->text());
    $this->assertEquals('(123 bytes - TXT)', $actual);
    $actual = trim($crawler->filter('.ecl-file__title')->text());
    $this->assertEquals('druplicon.txt', $actual);
    $actual = trim($crawler->filter('a.ecl-file__download')->text());
    // The screen reader sees the span sr-only text as well, not just the label.
    $this->assertEquals('Download(123 bytes - TXT)', $actual);
  }

  /**
   * Data provider for testFilePatternRendering.
   *
   * @return array
   *   An array of data arrays.
   *   The data array contains:
   *     - File entity with URI.
   *     - File entity with URL.
   */
  public function dataProvider() {
    return [
      [
        [
          'uid' => 1,
          'filename' => 'druplicon.txt',
          'filemime' => 'text/plain',
          'uri' => 'public://sample/druplicon.txt',
          'filesize' => 123,
        ],
      ],
      [
        [
          'uid' => 1,
          'filename' => 'druplicon.txt',
          'filemime' => 'text/plain',
          'uri' => 'http://example.com/druplicon.txt',
          'filesize' => 123,
        ],
      ],
    ];
  }

}
