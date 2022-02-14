<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\Paragraphs;

use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the "content row" paragraph.
 *
 * @group batch2
 */
class ContentRowTest extends ParagraphsTestBase {

  /**
   * Tests the rendering of the paragraph type.
   *
   * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
   */
  public function testRendering(): void {
    // Create multiple paragraphs to be referenced in the content row.
    $items = [];

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

    // Create a list item to be referenced in a list item block.
    $subitem = Paragraph::create([
      'type' => 'oe_list_item',
      'oe_paragraphs_variant' => 'default',
      'field_oe_title' => 'Sub-subitem title',
      'field_oe_link' => [
        'uri' => 'http://www.example.com/',
      ],
    ]);
    $subitem->save();
    $paragraph = Paragraph::create([
      'type' => 'oe_list_item_block',
      'field_oe_list_item_block_layout' => 'one_column',
      'field_oe_title' => 'List block title',
      'field_oe_paragraphs' => [$subitem],
    ]);
    $paragraph->save();
    $items[] = $paragraph;

    // Create a rich text paragraph without a title.
    $paragraph = Paragraph::create([
      'type' => 'oe_rich_text',
      'field_oe_text_long' => 'Rich text without title.',
    ]);
    $paragraph->save();
    $items[] = $paragraph;
    // And one with a title.
    $paragraph = Paragraph::create([
      'type' => 'oe_rich_text',
      'field_oe_title' => 'Rich text title',
      'field_oe_text_long' => 'Rich text with title.',
    ]);
    $paragraph->save();
    $items[] = $paragraph;

    // Create the main content row paragraph with a default variant.
    $paragraph = Paragraph::create([
      'type' => 'oe_content_row',
      'oe_paragraphs_variant' => 'default',
      'field_oe_title' => 'Page navigation',
      'field_oe_paragraphs' => $items,
    ]);

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    // Verify that there is a full size column rendered.
    $this->assertCount(1, $crawler->filter('.ecl-row .ecl-col-m-12'));
    // Do a smoke test that inner paragraphs are rendered.
    $this->assertStringContainsString('Links block title', $html);
    $this->assertStringContainsString('List item title', $html);
    $this->assertStringContainsString('List block title', $html);
    $this->assertStringContainsString('Rich text without title.', $html);
    $this->assertStringContainsString('Rich text with title.', $html);

    // Change variant to the inpage navigation.
    $paragraph->get('oe_paragraphs_variant')->setValue('inpage_navigation');
    $paragraph->save();
    $crawler = new Crawler($this->renderParagraph($paragraph));

    // Verify the layout.
    $left_column = $crawler->filter('.ecl-row .ecl-col-l-3.ecl-u-z-navigation');
    $right_column = $crawler->filter('.ecl-row .ecl-col-l-9');
    $this->assertCount(1, $left_column);
    $this->assertCount(1, $right_column);

    // Verify that the right column still contains all the paragraphs.
    $right_column_html = $right_column->html();
    $this->assertStringContainsString('Links block title', $right_column_html);
    $this->assertStringContainsString('List item title', $right_column_html);
    $this->assertStringContainsString('List block title', $right_column_html);
    $this->assertStringContainsString('Rich text without title.', $right_column_html);
    $this->assertStringContainsString('Rich text with title.', $right_column_html);

    // Verify that the inpage navigation title has been rendered.
    $this->assertEquals('Page navigation', trim($left_column->filter('.ecl-inpage-navigation__title')->text()));

    // Verify that the inpage navigation default title has been rendered.
    $paragraph->get('field_oe_title')->setValue('');
    $paragraph->save();
    $crawler = new Crawler($this->renderParagraph($paragraph, 'en'));

    // Assert that side-menu is correctly rendered with the default title.
    $left_column = $crawler->filter('.ecl-row .ecl-col-l-3.ecl-u-z-navigation');
    $this->assertStringContainsString('Page contents', $left_column->html());

    // The actual inpage navigation links are tested in the
    // InPageNavigationParagraphTest since they are rendered via JS.
  }

  /**
   * Asserts a navigation item title and anchor to a piece of content.
   *
   * @param \Symfony\Component\DomCrawler\Crawler $item
   *   The "<li>" navigation item.
   * @param string $title
   *   The title of the link and of the content.
   * @param \Symfony\Component\DomCrawler\Crawler $content
   *   The element containing the piece of content being referred by the link.
   */
  protected function assertNavigationItem(Crawler $item, string $title, Crawler $content) {
    $link = $item->filter('a');
    $this->assertEquals($title, trim($link->text()));
    $this->assertStringContainsString($title, $content->filter($link->attr('href'))->html());
  }

}
