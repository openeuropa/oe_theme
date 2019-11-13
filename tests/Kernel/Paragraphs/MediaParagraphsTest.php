<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\Paragraphs;

use Drupal\media\Entity\Media;
use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the rendering of paragraph types with media fields.
 */
class MediaParagraphsTest extends ParagraphsTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'language',
    'content_translation',
    'paragraphs',
    'user',
    'system',
    'file',
    'field',
    'entity_reference_revisions',
    'datetime',
    'image',
    'link',
    'text',
    'filter',
    'options',
    'oe_paragraphs',
    'media',
    'oe_media',
    'oe_paragraphs_media',
    'allowed_formats',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('media');
    $this->installConfig([
      'media',
      'oe_media',
      'oe_paragraphs_media',
    ]);
  }

  /**
   * Test text with featured media paragraph rendering.
   */
  public function testTextWithMedia(): void {
    $file = file_save_data(file_get_contents(drupal_get_path('theme', 'oe_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_1.jpeg');
    $file->setPermanent();
    $file->save();

    $media = Media::create([
      'bundle' => 'image',
      'name' => 'test document',
      'oe_media_image' => [
        'target_id' => $file->id(),
        'alt' => 'Alt',
      ],
    ]);
    $media->save();

    $paragraph = Paragraph::create([
      'type' => 'oe_text_feature_media',
      'field_oe_title' => 'Title',
      'field_oe_media' => [
        'target_id' => $media->id(),
      ],
      'field_oe_plain_text_long' => 'Caption',
      'field_oe_text_long' => 'Full text',
    ]);
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    // Assert the title is rendered properly.
    $title = $crawler->filter('h2.ecl-u-type-heading-2');
    $this->assertCount(1, $title);
    $this->assertContains('Title', $title->text());

    // Assert the image is rendered properly.
    $figure = $crawler->filter('figure.ecl-media-container');
    $this->assertCount(1, $figure);
    // The image in the figure element has the source and alt defined in the
    // referenced media.
    $image = $figure->filter('.ecl-media-container__media');
    $this->assertContains('/example_1.jpeg', $image->attr('src'));
    $this->assertContains('Alt', $image->attr('alt'));
    $caption = $figure->filter('.ecl-media-container__caption');
    $this->assertContains('Caption', $caption->text());

    // Assert text is rendered properly.
    $text = $crawler->filter('.ecl-col-md-6.ecl-editor');
    $this->assertCount(1, $text);
    $this->assertContains('Full text', $text->text());
  }

}
