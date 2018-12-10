<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Core\Site\Settings;
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
    $crawler = new Crawler($html);

    foreach ($assertions as $selector => $value) {
      $actual = trim($crawler->filter($selector)->text());
      $this->assertEquals($value, $actual);
    }

  }

  /**
   * Data provider for testFilePatternRendering.
   *
   * @return array
   *   An array of test data arrays with assertations.
   */
  public function dataProvider(): array {
    return [
      [
        [
          'uid' => 1,
          'filename' => 'druplicon.txt',
          'filemime' => 'text/plain',
          'uri' => 'public://sample/druplicon.txt',
          'filesize' => 321,
        ],
        [
          '.ecl-file__properties' => '(321 bytes - TXT)',
          '.ecl-file__title' => 'druplicon.txt',
          'a[href="http://example.com/sample/druplicon.txt"]' => 'Download(321 bytes - TXT)',
          '.ecl-file__language' => 'English',
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
        [
          '.ecl-file__properties' => '(123 bytes - TXT)',
          '.ecl-file__title' => 'druplicon.txt',
          'a[href="http://example.com/druplicon.txt"]' => 'Download(123 bytes - TXT)',
          '.ecl-file__language' => 'English',
        ],
      ],
    ];
  }

}
