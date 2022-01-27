<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\Paragraphs;

use Drupal\media\Entity\Media;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Tests\oe_theme\PatternAssertions\ListWithIllustrationAssert;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests illustration lists paragraphs.
 *
 * @group batch2
 */
class IllustrationListsParagraphsTest extends ParagraphsTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'composite_reference',
    'media',
    'file_link',
    'oe_media',
    'options',
    'oe_paragraphs_media_field_storage',
    'oe_paragraphs_illustrations_lists',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('media');

    module_load_include('install', 'media');
    media_install();
    $this->container->get('module_handler')->loadInclude('oe_paragraphs_media_field_storage', 'install');
    oe_paragraphs_media_field_storage_install(FALSE);

    $this->installConfig([
      'media',
      'options',
      'oe_media',
      'oe_paragraphs_illustrations_lists',
    ]);
  }

  /**
   * Tests the rendering of the "Illustration list with flags" paragraph.
   */
  public function testIllustrationListFlagsRendering(): void {
    // Create multiple paragraphs to be referenced in the illustration list.
    $items = [];

    $paragraph = Paragraph::create([
      'type' => 'oe_illustration_item_flag',
      'field_oe_title' => 'Term 1',
      'field_oe_text_long' => 'Description 1',
      'field_oe_flag' => 'austria',
    ]);
    $paragraph->save();
    $items[] = $paragraph;

    $paragraph = Paragraph::create([
      'type' => 'oe_illustration_item_flag',
      'field_oe_title' => 'Term 2',
      'field_oe_text_long' => '',
      'field_oe_flag' => 'belgium',
    ]);
    $paragraph->save();
    $items[] = $paragraph;

    $paragraph = Paragraph::create([
      'type' => 'oe_illustration_item_flag',
      'field_oe_title' => '',
      'field_oe_text_long' => 'Description 3',
      'field_oe_flag' => 'france',
    ]);
    $paragraph->save();
    $items[] = $paragraph;

    $paragraph = Paragraph::create([
      'type' => 'oe_illustration_item_flag',
      'field_oe_title' => '',
      'field_oe_text_long' => '',
      'field_oe_flag' => 'finland',
    ]);
    $paragraph->save();
    $items[] = $paragraph;

    $list_paragraph = Paragraph::create([
      'type' => 'oe_illustration_list_flags',
      'oe_paragraphs_variant' => 'default',
      'field_oe_title' => 'Illustration with flags test',
      'field_oe_paragraphs' => $items,
      'field_oe_illustration_columns' => 2,
      'field_oe_illustration_ratio' => 'landscape',
    ]);
    $html = $this->renderParagraph($list_paragraph);

    // Assert paragraph header.
    $crawler = new Crawler($html);
    $heading = $crawler->filter('h2.ecl-u-type-heading-2');
    $this->assertCount(1, $heading);
    $this->assertEquals('Illustration with flags test', trim($heading->text()));
    $icon = $crawler->filter('.ecl-list-illustration__icon use');
    $this->assertStringNotContainsString('-square', $icon->attr('xlink:href'));

    // Assert rendered items.
    $expected_values = [
      'column' => 2,
      'zebra' => FALSE,
      'items' => [
        [
          'title' => 'Term 1',
          'description' => 'Description 1',
          'icon' => 'austria',
        ], [
          'title' => 'Term 2',
          'icon' => 'belgium',
        ], [
          'description' => 'Description 3',
          'icon' => 'france',
        ], [
          'icon' => 'finland',
        ],
      ],
    ];
    $assert = new ListWithIllustrationAssert();
    $assert->assertPattern($expected_values, $html);

    // Assert number of columns and ratio.
    $list_paragraph->set('field_oe_illustration_columns', 4);
    $list_paragraph->set('field_oe_illustration_ratio', 'square')->save();
    $html = $this->renderParagraph($list_paragraph);
    $expected_values = [
      'column' => 4,
      'zebra' => FALSE,
      'items' => [
        [
          'title' => 'Term 1',
          'description' => 'Description 1',
          'icon' => 'austria-square',
        ], [
          'title' => 'Term 2',
          'icon' => 'belgium-square',
        ], [
          'description' => 'Description 3',
          'icon' => 'france-square',
        ], [
          'icon' => 'finland-square',
        ],
      ],
    ];
    $assert->assertPattern($expected_values, $html);
    $crawler = new Crawler($html);
    $icon = $crawler->filter('.ecl-list-illustration__icon use');
    $this->assertStringContainsString('-square', $icon->attr('xlink:href'));

    // Assert vertical variant.
    $list_paragraph->set('oe_paragraphs_variant', 'oe_illustration_vertical')->save();
    $html = $this->renderParagraph($list_paragraph);
    unset($expected_values['column']);
    $assert->assertPattern($expected_values, $html);

    // Assert vertical variant with zebra.
    $list_paragraph->set('field_oe_illustration_alternate', TRUE)->save();
    $html = $this->renderParagraph($list_paragraph);
    $expected_values['zebra'] = TRUE;
    $assert->assertPattern($expected_values, $html);
  }

  /**
   * Tests the rendering of the "Illustration list with icons" paragraph.
   */
  public function testIllustrationListIconsRendering(): void {
    $items = [];

    $paragraph = Paragraph::create([
      'type' => 'oe_illustration_item_icon',
      'field_oe_title' => 'Term 1',
      'field_oe_text_long' => 'Description 1',
      'field_oe_icon' => 'data',
    ]);
    $paragraph->save();
    $items[] = $paragraph;

    $paragraph = Paragraph::create([
      'type' => 'oe_illustration_item_icon',
      'field_oe_title' => 'Term 2',
      'field_oe_icon' => 'facebook',
    ]);
    $paragraph->save();
    $items[] = $paragraph;

    $paragraph = Paragraph::create([
      'type' => 'oe_illustration_item_icon',
      'field_oe_text_long' => 'Description 3',
      'field_oe_icon' => 'global',
    ]);
    $paragraph->save();
    $items[] = $paragraph;

    $paragraph = Paragraph::create([
      'type' => 'oe_illustration_item_icon',
      'field_oe_icon' => 'package',
    ]);
    $paragraph->save();
    $items[] = $paragraph;

    $list_paragraph = Paragraph::create([
      'type' => 'oe_illustration_list_icons',
      'oe_paragraphs_variant' => 'default',
      'field_oe_title' => 'Illustration with icons test',
      'field_oe_paragraphs' => $items,
      'field_oe_illustration_columns' => 2,
    ]);
    $html = $this->renderParagraph($list_paragraph);

    // Assert paragraph header.
    $crawler = new Crawler($html);
    $heading = $crawler->filter('h2.ecl-u-type-heading-2');
    $this->assertCount(1, $heading);
    $this->assertEquals('Illustration with icons test', trim($heading->text()));

    // Assert rendered items.
    $expected_values = [
      'column' => 2,
      'zebra' => FALSE,
      'items' => [
        [
          'title' => 'Term 1',
          'description' => 'Description 1',
          'icon' => 'data',
        ], [
          'title' => 'Term 2',
          'icon' => 'facebook',
        ], [
          'description' => 'Description 3',
          'icon' => 'global',
        ], [
          'icon' => 'package',
        ],
      ],
    ];
    $assert = new ListWithIllustrationAssert();
    $assert->assertPattern($expected_values, $html);

    // Assert number of columns.
    $list_paragraph->set('field_oe_illustration_columns', 3)->save();
    $html = $this->renderParagraph($list_paragraph);
    $expected_values['column'] = 3;
    $assert->assertPattern($expected_values, $html);

    // Assert vertical variant.
    $list_paragraph->set('oe_paragraphs_variant', 'oe_illustration_vertical')->save();
    $html = $this->renderParagraph($list_paragraph);
    unset($expected_values['column']);
    $assert->assertPattern($expected_values, $html);

    // Assert vertical variant with zebra.
    $list_paragraph->set('field_oe_illustration_alternate', TRUE)->save();
    $html = $this->renderParagraph($list_paragraph);
    $expected_values['zebra'] = TRUE;
    $assert->assertPattern($expected_values, $html);
  }

  /**
   * Tests the rendering of the "Illustration list with icons" paragraph.
   */
  public function testIllustrationListImagesRendering(): void {
    // Create media image.
    $file = file_save_data(file_get_contents(drupal_get_path('theme', 'oe_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_1.jpeg');
    $file->setPermanent();
    $file->save();

    $media = Media::create([
      'bundle' => 'image',
      'name' => 'test image',
      'oe_media_image' => [
        'target_id' => $file->id(),
        'alt' => 'Alt',
      ],
    ]);
    $media->save();

    // Create Illustration list with images paragraph.
    $items = [];
    $paragraph = Paragraph::create([
      'type' => 'oe_illustration_item_image',
      'field_oe_title' => 'Term 1',
      'field_oe_text_long' => 'Description 1',
      'field_oe_media' => [$media],
    ]);
    $paragraph->save();
    $items[] = $paragraph;

    $paragraph = Paragraph::create([
      'type' => 'oe_illustration_item_image',
      'field_oe_title' => 'Term 2',
      'field_oe_media' => [$media],
    ]);
    $paragraph->save();
    $items[] = $paragraph;

    $paragraph = Paragraph::create([
      'type' => 'oe_illustration_item_image',
      'field_oe_text_long' => 'Description 3',
      'field_oe_media' => [$media],
    ]);
    $paragraph->save();
    $items[] = $paragraph;

    $paragraph = Paragraph::create([
      'type' => 'oe_illustration_item_image',
      'field_oe_media' => [$media],
    ]);
    $paragraph->save();
    $items[] = $paragraph;

    $list_paragraph = Paragraph::create([
      'type' => 'oe_illustration_list_images',
      'oe_paragraphs_variant' => 'default',
      'field_oe_title' => 'Illustration with images test',
      'field_oe_paragraphs' => $items,
      'field_oe_illustration_columns' => 2,
      'field_oe_illustration_ratio' => 'landscape',
    ]);
    $html = $this->renderParagraph($list_paragraph);

    // Assert paragraph header.
    $crawler = new Crawler($html);
    $heading = $crawler->filter('h2.ecl-u-type-heading-2');
    $this->assertCount(1, $heading);
    $this->assertEquals('Illustration with images test', trim($heading->text()));

    // Assert rendered items.
    $expected_values = [
      'column' => 2,
      'zebra' => FALSE,
      'square_image' => FALSE,
      'items' => [
        [
          'title' => 'Term 1',
          'description' => 'Description 1',
          'image' => [
            'src' => 'example_1.jpeg',
            'alt' => 'Alt',
          ],
        ], [
          'title' => 'Term 2',
          'image' => [
            'src' => 'example_1.jpeg',
            'alt' => 'Alt',
          ],
        ], [
          'description' => 'Description 3',
          'image' => [
            'src' => 'example_1.jpeg',
            'alt' => 'Alt',
          ],
        ], [
          'image' => [
            'src' => 'example_1.jpeg',
            'alt' => 'Alt',
          ],
        ],
      ],
    ];
    $assert = new ListWithIllustrationAssert();
    $assert->assertPattern($expected_values, $html);

    // Assert number of columns and ratio.
    $list_paragraph->set('field_oe_illustration_columns', 3);
    $list_paragraph->set('field_oe_illustration_ratio', 'square')->save();
    $html = $this->renderParagraph($list_paragraph);
    $expected_values['column'] = 3;
    $expected_values['square_image'] = TRUE;
    $assert->assertPattern($expected_values, $html);

    // Assert vertical variant.
    $list_paragraph->set('oe_paragraphs_variant', 'oe_illustration_vertical')->save();
    $html = $this->renderParagraph($list_paragraph);
    unset($expected_values['column']);
    $assert->assertPattern($expected_values, $html);

    // Assert vertical variant with zebra and ratio.
    $list_paragraph->set('field_oe_illustration_alternate', TRUE);
    $list_paragraph->set('field_oe_illustration_ratio', 'landscape')->save();
    $html = $this->renderParagraph($list_paragraph);
    $expected_values['zebra'] = TRUE;
    $expected_values['square_image'] = FALSE;
    $assert->assertPattern($expected_values, $html);
  }

}
