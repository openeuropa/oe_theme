<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\Paragraphs;

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
  protected static $modules = [
    'composite_reference',
    'media',
    'file_link',
    'views',
    'entity_browser',
    'media_avportal',
    'media_avportal_mock',
    'oe_media_avportal',
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

    $this->installConfig([
      'media',
      'options',
      'media_avportal',
    ]);
    $this->installEntitySchema('media');

    $this->container->get('module_handler')->loadInclude('media', 'install');
    media_install();
    $this->container->get('module_handler')->loadInclude('oe_paragraphs_media_field_storage', 'install');
    oe_paragraphs_media_field_storage_install(FALSE);
    $this->installConfig([
      'oe_media',
      'oe_media_avportal',
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
      'field_oe_subtitle' => 'Highlighted Illustration list with flags',
      'field_oe_title' => 'Illustration with flags test',
      'field_oe_paragraphs' => $items,
      'field_oe_illustration_columns' => 2,
      'field_oe_illustration_ratio' => 'landscape',
      'field_oe_center' => FALSE,
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
          'value' => 'Highlighted Illustration list with flags',
        ], [
          'title' => 'Term 2',
          'icon' => 'belgium',
          'value' => 'Highlighted Illustration list with flags',
        ], [
          'description' => 'Description 3',
          'icon' => 'france',
          'value' => 'Highlighted Illustration list with flags',
        ], [
          'icon' => 'finland',
          'value' => 'Highlighted Illustration list with flags',
        ],
      ],
      'centered' => FALSE,
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

    // Assert vertical variant with zebra and centered.
    $list_paragraph->set('field_oe_center', TRUE);
    $list_paragraph->set('field_oe_illustration_alternate', TRUE)->save();
    $html = $this->renderParagraph($list_paragraph);
    $expected_values['zebra'] = TRUE;
    $expected_values['centered'] = TRUE;
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
      'field_oe_subtitle' => 'Highlighted Illustration list with icons',
      'field_oe_title' => 'Illustration with icons test',
      'field_oe_paragraphs' => $items,
      'field_oe_illustration_columns' => 2,
      'field_oe_size' => 'small',
      'field_oe_center' => FALSE,
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
          'value' => 'Highlighted Illustration list with icons',
          'media_size' => 'l',
        ], [
          'title' => 'Term 2',
          'icon' => 'facebook',
          'value' => 'Highlighted Illustration list with icons',
          'media_size' => 'l',
        ], [
          'description' => 'Description 3',
          'icon' => 'global',
          'value' => 'Highlighted Illustration list with icons',
          'media_size' => 'l',
        ], [
          'icon' => 'package',
          'value' => 'Highlighted Illustration list with icons',
          'media_size' => 'l',
        ],
      ],
      'centered' => FALSE,
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

    // Assert vertical variant with zebra and centered.
    $list_paragraph->set('field_oe_illustration_alternate', TRUE);
    $list_paragraph->set('field_oe_center', TRUE);
    // Update the icon size to Large.
    $list_paragraph->set('field_oe_size', 'large')->save();
    $html = $this->renderParagraph($list_paragraph);
    $expected_values['zebra'] = TRUE;
    $expected_values['centered'] = TRUE;
    $expected_values['items'][0]['media_size'] = '2xl';
    $expected_values['items'][1]['media_size'] = '2xl';
    $expected_values['items'][2]['media_size'] = '2xl';
    $expected_values['items'][3]['media_size'] = '2xl';
    $assert->assertPattern($expected_values, $html);
  }

  /**
   * Tests the rendering of the "Illustration list with icons" paragraph.
   */
  public function testIllustrationListImagesRendering(): void {
    // Create media image.
    $file = $this->container->get('file.repository')->writeData(file_get_contents($this->container->get('extension.list.theme')->getPath('oe_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_1.jpeg');
    $file->setPermanent();
    $file->save();

    $media_storage = $this->container->get('entity_type.manager')->getStorage('media');
    $media_image = $media_storage->create([
      'bundle' => 'image',
      'name' => 'test image',
      'oe_media_image' => [
        'target_id' => $file->id(),
        'alt' => 'Alt',
      ],
    ]);
    $media_image->save();

    $media_av_portal_photo = $media_storage->create([
      'bundle' => 'av_portal_photo',
      'oe_media_avportal_photo' => 'P-038924/00-15',
      'uid' => 0,
      'status' => 1,
    ]);

    // Create Illustration list with images paragraph.
    $items = [];
    $paragraph = Paragraph::create([
      'type' => 'oe_illustration_item_image',
      'field_oe_title' => 'Term 1',
      'field_oe_text_long' => 'Description 1',
      'field_oe_media' => [$media_image],
    ]);
    $paragraph->save();
    $items[] = $paragraph;

    $paragraph = Paragraph::create([
      'type' => 'oe_illustration_item_image',
      'field_oe_title' => 'Term 2',
      'field_oe_media' => [$media_av_portal_photo],
    ]);
    $paragraph->save();
    $items[] = $paragraph;

    $paragraph = Paragraph::create([
      'type' => 'oe_illustration_item_image',
      'field_oe_text_long' => 'Description 3',
      'field_oe_media' => [$media_image],
    ]);
    $paragraph->save();
    $items[] = $paragraph;

    $paragraph = Paragraph::create([
      'type' => 'oe_illustration_item_image',
      'field_oe_media' => [$media_image],
    ]);
    $paragraph->save();
    $items[] = $paragraph;

    $list_paragraph = Paragraph::create([
      'type' => 'oe_illustration_list_images',
      'oe_paragraphs_variant' => 'default',
      'field_oe_subtitle' => 'Highlighted Illustration list with images',
      'field_oe_title' => 'Illustration with images test',
      'field_oe_paragraphs' => $items,
      'field_oe_illustration_columns' => 2,
      'field_oe_illustration_ratio' => 'landscape',
      // Size value will take action only for "Square" ratio.
      'field_oe_size' => 'small',
      'field_oe_center' => FALSE,
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
          'value' => 'Highlighted Illustration list with images',
        ], [
          'title' => 'Term 2',
          'image' => [
            'src' => $this->container->get('file_url_generator')->generateAbsoluteString('avportal://P-038924/00-15.jpg'),
            'alt' => 'Euro with miniature figurines',
          ],
          'value' => 'Highlighted Illustration list with images',
        ], [
          'description' => 'Description 3',
          'image' => [
            'src' => 'example_1.jpeg',
            'alt' => 'Alt',
          ],
          'value' => 'Highlighted Illustration list with images',
        ], [
          'image' => [
            'src' => 'example_1.jpeg',
            'alt' => 'Alt',
          ],
          'value' => 'Highlighted Illustration list with images',
        ],
      ],
      'centered' => FALSE,
    ];
    $assert = new ListWithIllustrationAssert();
    $assert->assertPattern($expected_values, $html);

    // Assert number of columns and ratio.
    $list_paragraph->set('field_oe_illustration_columns', 3);
    $list_paragraph->set('field_oe_illustration_ratio', 'square')->save();
    $html = $this->renderParagraph($list_paragraph);
    $expected_values['column'] = 3;
    $expected_values['square_image'] = TRUE;
    $expected_values['items'][0]['media_size'] = 's';
    $expected_values['items'][1]['media_size'] = 's';
    $expected_values['items'][2]['media_size'] = 's';
    $expected_values['items'][3]['media_size'] = 's';
    $assert->assertPattern($expected_values, $html);

    // Assert centered with medium and large size.
    $list_paragraph->set('field_oe_center', TRUE);
    $list_paragraph->set('field_oe_size', 'medium')->save();
    $html = $this->renderParagraph($list_paragraph);
    $expected_values['centered'] = TRUE;
    $expected_values['items'][0]['media_size'] = 'm';
    $expected_values['items'][1]['media_size'] = 'm';
    $expected_values['items'][2]['media_size'] = 'm';
    $expected_values['items'][3]['media_size'] = 'm';
    $assert->assertPattern($expected_values, $html);
    $list_paragraph->set('field_oe_size', 'large')->save();
    $html = $this->renderParagraph($list_paragraph);
    $expected_values['items'][0]['media_size'] = 'l';
    $expected_values['items'][1]['media_size'] = 'l';
    $expected_values['items'][2]['media_size'] = 'l';
    $expected_values['items'][3]['media_size'] = 'l';
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
    $expected_values['items'][0]['media_size'] = NULL;
    $expected_values['items'][1]['media_size'] = NULL;
    $expected_values['items'][2]['media_size'] = NULL;
    $expected_values['items'][3]['media_size'] = NULL;
    $assert->assertPattern($expected_values, $html);
  }

}
