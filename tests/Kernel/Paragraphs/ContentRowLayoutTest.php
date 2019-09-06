<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\Paragraphs;

use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the "content row" layout paragraph.
 */
class ContentRowLayoutTest extends ParagraphsTestBase {

  /**
   * Tests the rendering of the paragraph type "content row with 3-col layout".
   *
   * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
   */
  public function testRenderingThreeColLayout(): void {
    // Create multiple paragraphs to be referenced in the content row.
    $items = [];

    // Create a links block paragraph.
    $paragraph = Paragraph::create([
      'type' => 'oe_links_block',
      'field_oe_text' => 'Links block title',
      'field_oe_links' => [
        [
          'title' => 'Link 1',
          'uri' => 'internal:/',
        ],
      ],
    ]);
    $paragraph->save();
    $items[] = $paragraph;

    // Create a list item paragraph.
    $paragraph = Paragraph::create([
      'type' => 'oe_list_item',
      'oe_paragraphs_variant' => 'default',
      'field_oe_title' => 'List item title',
      'field_oe_text_long' => 'Item description',
      'field_oe_link' => [
        'uri' => 'http://www.example.com/',
      ],
    ]);
    $paragraph->save();
    $items[] = $paragraph;

    // Create a rich text paragraph with a title.
    $paragraph = Paragraph::create([
      'type' => 'oe_rich_text',
      'field_oe_title' => 'Rich text title',
      'field_oe_text_long' => 'Rich text without title.',
    ]);
    $paragraph->save();
    $items[] = $paragraph;

    // Create the main content row paragraph with a columns layout variant.
    $paragraph = Paragraph::create([
      'type' => 'oe_content_row',
      'oe_paragraphs_variant' => 'columns_layout',
      'field_oe_title' => 'Three col layout',
      'field_oe_content_row_layout' => '3',
      'field_oe_paragraphs' => $items,
    ]);

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    // Verify the layout having three columns.
    $this->assertCount(3, $crawler->filter('.ecl-row .ecl-col-md-4'));

    // Verify that the columns contain the correct paragraph.
    $this->assertContains('Links block title', $crawler->filter('.ecl-row .ecl-col-md-4')->eq(0)->html());
    $this->assertContains('List item title', $crawler->filter('.ecl-row .ecl-col-md-4')->eq(1)->html());
    $this->assertContains('Rich text title', $crawler->filter('.ecl-row .ecl-col-md-4')->eq(2)->html());
  }

  /**
   * Tests the rendering of the paragraph type "content row with 2-col layout".
   *
   * Tests the rendering of non-equal 2-col layout.
   *
   * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
   */
  public function testRenderingTwoNonEqualColLayout(): void {
    // Create multiple paragraphs to be referenced in the content row.
    $items = [];

    // Create a links block paragraph.
    $paragraph = Paragraph::create([
      'type' => 'oe_links_block',
      'field_oe_text' => 'Links block title',
      'field_oe_links' => [
        [
          'title' => 'Link 1',
          'uri' => 'internal:/',
        ],
      ],
    ]);
    $paragraph->save();
    $items[] = $paragraph;

    // Create a list item paragraph.
    $paragraph = Paragraph::create([
      'type' => 'oe_list_item',
      'oe_paragraphs_variant' => 'default',
      'field_oe_title' => 'List item title',
      'field_oe_text_long' => 'Item description',
      'field_oe_link' => [
        'uri' => 'http://www.example.com/',
      ],
    ]);
    $paragraph->save();
    $items[] = $paragraph;

    // Create the main content row paragraph with a columns layout variant.
    $paragraph = Paragraph::create([
      'type' => 'oe_content_row',
      'oe_paragraphs_variant' => 'columns_layout',
      'field_oe_title' => 'Non-equal two col layout',
      'field_oe_content_row_layout' => '8-4',
      'field_oe_paragraphs' => $items,
    ]);

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    // Verify the layout having two non-equal columns.
    $this->assertCount(1, $crawler->filter('.ecl-row .ecl-col-md-8'));
    $this->assertCount(1, $crawler->filter('.ecl-row .ecl-col-md-4'));

    // Verify that the columns contain the correct paragraph.
    $this->assertContains('Links block title', $crawler->filter('.ecl-row .ecl-col-md-8')->html());
    $this->assertContains('List item title', $crawler->filter('.ecl-row .ecl-col-md-4')->html());
  }

}
