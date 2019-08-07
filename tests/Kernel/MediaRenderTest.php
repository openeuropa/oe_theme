<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\media\Entity\Media;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests that our media types render with correct markup.
 */
class MediaRenderTest extends AbstractKernelTestBase {

  /**
   * The media storage.
   *
   * @var Drupal\media\MediaStorage
   */
  protected $mediaStorage;

  /**
   * The media view builder.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $mediaViewBuilder;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'field',
    'file',
    'media',
    'oe_media',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installSchema('file', 'file_usage');
    $this->installEntitySchema('media');
    $this->installEntitySchema('file');

    $this->installConfig([
      'file',
      'media',
      'oe_media',
    ]);

    $this->mediaStorage = $this->container->get('entity_type.manager')->getStorage('media');
    $this->mediaViewBuilder = $this->container->get('entity_type.manager')->getViewBuilder('media');
  }

  /**
   * Tests that the Document media type is rendered with the correct ECL markup.
   */
  public function testDocumentMedia(): void {
    $file = file_save_data(file_get_contents(drupal_get_path('module', 'oe_media') . '/tests/fixtures/sample.pdf'), 'public://test.pdf');
    $file->setPermanent();
    $file->save();

    $media = Media::create([
      'bundle' => 'document',
      'name' => 'test document',
      'oe_media_file' => [
        'target_id' => $file->id(),
      ],
    ]);

    $media->save();

    $build = $this->mediaViewBuilder->view($media, 'default');
    $html = $this->renderRoot($build);
    $crawler = new Crawler($html);

    // File wrapper.
    $file_wrapper = $crawler->filter('.ecl-file');
    $this->assertCount(1, $file_wrapper);

    // File row.
    $file_row = $crawler->filter('.ecl-file .ecl-file__container');
    $this->assertCount(1, $file_row);

    $file_title = $file_row->filter('.ecl-file__title');
    $this->assertContains('test.pdf', $file_title->text());

    $file_info_language = $file_row->filter('.ecl-file__info div.ecl-file__language');
    $this->assertContains('English', $file_info_language->text());

    $file_info_properties = $file_row->filter('.ecl-file__info div.ecl-file__meta');
    $this->assertContains('KB - PDF)', $file_info_properties->text());

    $file_download_link = $file_row->filter('.ecl-file__download');
    $this->assertContains('/test.pdf', $file_download_link->attr('href'));
    $this->assertContains('Download', $file_download_link->text());
  }

}
