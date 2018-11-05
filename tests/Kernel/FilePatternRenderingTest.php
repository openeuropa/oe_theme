<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\file\Entity\File;
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
   */
  public function testFilePatternRendering() {
    $file = File::create([
      'uid' => 1,
      'filename' => 'druplicon.txt',
      'filemime' => 'text/plain',
      'uri' => 'http://example.com',
      'filesize' => 123,
    ]);

    $pattern = [
      '#type' => 'pattern',
      '#id' => 'file',
      '#fields' => [
        'button_label' => 'Download',
        'file' => $file,
      ],
    ];

    $html = $this->renderRoot($pattern);
    $crawler = new Crawler($html);

    $actual = trim($crawler->filter('.ecl-file__properties')->text());
    $this->assertEquals('(123 bytes - TXT)', $actual);
  }

}
