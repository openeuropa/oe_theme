<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\Paragraphs;

use Drupal\file\Entity\File;
use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the rendering of paragraphs types.
 */
class ParagraphsTest extends ParagraphsTestBase {

  /**
   * Test links block paragraph rendering.
   */
  public function testLinksBlock(): void {
    $paragraph = Paragraph::create([
      'type' => 'oe_links_block',
      'field_oe_text' => 'Title',
      'field_oe_links' => [
        [
          'title' => 'Link 1',
          'uri' => 'internal:/',
        ],
        [
          'title' => 'Link 2',
          'uri' => 'internal:/',
        ],
      ],
    ]);

    $paragraph->save();
    $html = $this->renderParagraph($paragraph);

    $crawler = new Crawler($html);
    $actual = $crawler->filter('div.ecl-link-block > div.ecl-link-block__title')
      ->text();
    $this->assertEquals('Title', trim($actual));

    $actual = $crawler->filter('div.ecl-link-block ul.ecl-link-block__list a.ecl-link')
      ->eq(0)
      ->text();
    $this->assertEquals('Link 1', trim($actual));

    $actual = $crawler->filter('div.ecl-link-block ul.ecl-link-block__list a.ecl-link')
      ->eq(1)
      ->text();
    $this->assertEquals('Link 2', trim($actual));
  }

  /**
   * Test accordion paragraph rendering.
   */
  public function testAccordions(): void {
    $item1 = Paragraph::create([
      'type' => 'oe_accordion_item',
      'field_oe_text' => 'Item title 1',
      'field_oe_text_long' => 'Item body 1',
      'field_oe_icon' => 'arrow-up',
    ]);
    $item1->save();

    $item2 = Paragraph::create([
      'type' => 'oe_accordion_item',
      'field_oe_text' => 'Item title 2',
      'field_oe_text_long' => 'Item body 2',
      'field_oe_icon' => 'copy',
    ]);
    $item2->save();

    $paragraph = Paragraph::create([
      'type' => 'oe_accordion',
      'field_oe_paragraphs' => [
        [
          'target_id' => $item1->id(),
          'target_revision_id' => $item1->getRevisionId(),
        ],
        [
          'target_id' => $item2->id(),
          'target_revision_id' => $item2->getRevisionId(),
        ],
      ],
    ]);
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);

    $crawler = new Crawler($html);

    $actual = $crawler->filter('button#ecl-accordion-header-paragraph-3-0')->text();
    $this->assertEquals('Item title 1', trim($actual));

    $actual = $crawler->filter('dd#ecl-accordion-panel-paragraph-3-0')->text();
    $this->assertEquals('Item body 1', trim($actual));

    $actual = $crawler->filter('button#ecl-accordion-header-paragraph-3-0 span.ecl-icon--arrow-up');
    $this->assertCount(1, $actual);

    $actual = $crawler->filter('button#ecl-accordion-header-paragraph-3-1')->text();
    $this->assertEquals('Item title 2', trim($actual));

    $actual = $crawler->filter('dd#ecl-accordion-panel-paragraph-3-1')->text();
    $this->assertEquals('Item body 2', trim($actual));

    $actual = $crawler->filter('button#ecl-accordion-header-paragraph-3-1 span.ecl-icon--copy');
    $this->assertCount(1, $actual);
  }

  /**
   * Test quote paragraph rendering.
   *
   * @param array $data
   *   Array of Data tested.
   * @param array $expected
   *   Array of Data expected.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *
   * @dataProvider quoteDataProvider
   */
  public function testQuote(array $data, array $expected): void {
    $paragraph = Paragraph::create([
      'type' => 'oe_quote',
      'field_oe_text' => $data['attribution'],
      'field_oe_plain_text_long' => $data['body'],
    ]);
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);

    $crawler = new Crawler($html);

    $actual = $crawler->filter('blockquote .ecl-blockquote__body')->html();
    $this->assertEquals($expected['body'], trim($actual));

    $actual = $crawler->filter('blockquote footer.ecl-blockquote__author cite')->text();
    $this->assertEquals($expected['attribution'], trim($actual));
  }

  /**
   * Tests the list item paragraph type.
   *
   * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
   */
  public function testListItem(): void {
    file_unmanaged_copy($this->root . '/core/misc/druplicon.png', 'public://example.jpg');
    $image = File::create([
      'uri' => 'public://example.jpg',
    ]);
    $image->save();

    $paragraph = Paragraph::create([
      'type' => 'oe_list_item',
      'oe_paragraphs_variant' => 'default',
      'field_oe_title' => 'Item title',
      'field_oe_text_long' => 'Item description',
      'field_oe_link' => [
        'uri' => 'http://www.example.com/',
      ],
      'field_oe_image' => [
        'target_id' => $image->id(),
        'alt' => 'Druplicon',
      ],
      'field_oe_date' => '1981-09-24',
      'field_oe_meta' => ['Meta 1', 'Meta 2', 'Meta 3'],
    ]);
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('.ecl-list-item'));
    $this->assertEquals('Item title', trim($crawler->filter('.ecl-list-item__title')->text()));
    $this->assertEquals('Item description', trim($crawler->filter('.ecl-list-item__detail')->text()));

    $link_element = $crawler->filter('.ecl-list-item__link');
    $this->assertCount(1, $link_element);
    $this->assertEquals('http://www.example.com/', $link_element->attr('href'));

    $meta_elements = $crawler->filter('.ecl-meta__item');
    $this->assertCount(3, $meta_elements);
    $this->assertEquals('Meta 1', trim($meta_elements->getNode(0)->nodeValue));
    $this->assertEquals('Meta 2', trim($meta_elements->getNode(1)->nodeValue));
    $this->assertEquals('Meta 3', trim($meta_elements->getNode(2)->nodeValue));

    // No images should be rendered in this variant.
    $this->assertCount(0, $crawler->filter('img.ecl-image'));
    // No date should be rendered neither.
    $this->assertCount(0, $crawler->filter('.ecl-date-block__week-day'));
    $this->assertCount(0, $crawler->filter('.ecl-date-block__day'));
    $this->assertCount(0, $crawler->filter('.ecl-date-block__month'));

    // Change the variant and test that the markup changed.
    $paragraph->get('oe_paragraphs_variant')->setValue('highlight');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('.ecl-list-item.ecl-list-item--highlight'));
    $this->assertEquals('Item title', trim($crawler->filter('.ecl-list-item__title')->text()));
    // The description should not be rendered in this variant.
    $this->assertCount(0, $crawler->filter('.ecl-list-item__detail'));
    // Neither the date.
    $this->assertCount(0, $crawler->filter('.ecl-date-block__week-day'));
    $this->assertCount(0, $crawler->filter('.ecl-date-block__day'));
    $this->assertCount(0, $crawler->filter('.ecl-date-block__month'));
    // Neither the metas.
    $this->assertCount(0, $crawler->filter('.ecl-meta__item'));

    $link_element = $crawler->filter('.ecl-list-item__link');
    $this->assertCount(1, $link_element);
    $this->assertEquals('http://www.example.com/', $link_element->attr('href'));

    $image_element = $crawler->filter('.ecl-list-item__primary img.ecl-image');
    $this->assertCount(1, $image_element);
    $this->assertEquals(
      file_url_transform_relative(file_create_url($image->getFileUri())),
      $image_element->attr('src')
    );
    $this->assertEquals('Druplicon', $image_element->attr('alt'));

    // Change the variant to thumbnail primary.
    $paragraph->get('oe_paragraphs_variant')->setValue('thumbnail_primary');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('.ecl-list-item.ecl-list-item--thumbnail'));
    $this->assertEquals('Item title', trim($crawler->filter('.ecl-list-item__title')->text()));
    $this->assertEquals('Item description', trim($crawler->filter('.ecl-list-item__detail')->text()));

    $link_element = $crawler->filter('.ecl-list-item__link');
    $this->assertCount(1, $link_element);
    $this->assertEquals('http://www.example.com/', $link_element->attr('href'));

    $image_element = $crawler->filter('.ecl-list-item__primary img.ecl-image');
    $this->assertCount(1, $image_element);
    $this->assertEquals(
      file_url_transform_relative(file_create_url($image->getFileUri())),
      $image_element->attr('src')
    );
    $this->assertEquals('Druplicon', $image_element->attr('alt'));

    $meta_elements = $crawler->filter('.ecl-meta__item');
    $this->assertCount(3, $meta_elements);
    $this->assertEquals('Meta 1', trim($meta_elements->getNode(0)->nodeValue));
    $this->assertEquals('Meta 2', trim($meta_elements->getNode(1)->nodeValue));
    $this->assertEquals('Meta 3', trim($meta_elements->getNode(2)->nodeValue));

    // The secondary image markup should not be rendered.
    $this->assertCount(0, $crawler->filter('.ecl-list-item__secondary img.ecl-image'));
    // The date field should not be rendered.
    $this->assertCount(0, $crawler->filter('.ecl-date-block__week-day'));
    $this->assertCount(0, $crawler->filter('.ecl-date-block__day'));
    $this->assertCount(0, $crawler->filter('.ecl-date-block__month'));

    // Change the variant to thumbnail secondary.
    $paragraph->get('oe_paragraphs_variant')->setValue('thumbnail_secondary');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('.ecl-list-item.ecl-list-item--thumbnail'));
    $this->assertEquals('Item title', trim($crawler->filter('.ecl-list-item__title')->text()));
    $this->assertEquals('Item description', trim($crawler->filter('.ecl-list-item__detail')->text()));

    $link_element = $crawler->filter('.ecl-list-item__link');
    $this->assertCount(1, $link_element);
    $this->assertEquals('http://www.example.com/', $link_element->attr('href'));

    $image_element = $crawler->filter('.ecl-list-item__secondary img.ecl-image');
    $this->assertCount(1, $image_element);
    $this->assertEquals(
      file_url_transform_relative(file_create_url($image->getFileUri())),
      $image_element->attr('src')
    );
    $this->assertEquals('Druplicon', $image_element->attr('alt'));

    $meta_elements = $crawler->filter('.ecl-meta__item');
    $this->assertCount(3, $meta_elements);
    $this->assertEquals('Meta 1', trim($meta_elements->getNode(0)->nodeValue));
    $this->assertEquals('Meta 2', trim($meta_elements->getNode(1)->nodeValue));
    $this->assertEquals('Meta 3', trim($meta_elements->getNode(2)->nodeValue));

    // The primary image markup should not be rendered.
    $this->assertCount(0, $crawler->filter('.ecl-list-item__primary img.ecl-image'));
    // The date field should not be rendered.
    $this->assertCount(0, $crawler->filter('.ecl-date-block__week-day'));
    $this->assertCount(0, $crawler->filter('.ecl-date-block__day'));
    $this->assertCount(0, $crawler->filter('.ecl-date-block__month'));

    // Change the variant to date.
    $paragraph->get('oe_paragraphs_variant')->setValue('date');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('.ecl-list-item.ecl-list-item--date'));
    $this->assertEquals('Item title', trim($crawler->filter('.ecl-list-item__title')->text()));
    $this->assertEquals('Item description', trim($crawler->filter('.ecl-list-item__detail')->text()));

    $link_element = $crawler->filter('.ecl-list-item__link');
    $this->assertCount(1, $link_element);
    $this->assertEquals('http://www.example.com/', $link_element->attr('href'));

    $this->assertEquals('Thu', trim($crawler->filter('.ecl-date-block__week-day')->text()));
    $this->assertEquals('24', trim($crawler->filter('.ecl-date-block__day')->text()));
    $this->assertEquals('Sep', trim($crawler->filter('.ecl-date-block__month')->text()));
    $this->assertEquals('1981', trim($crawler->filter('.ecl-date-block__year')->text()));

    // No images should be rendered in this variant.
    $this->assertCount(0, $crawler->filter('img.ecl-image'));
    // Neither the metas.
    $this->assertCount(0, $crawler->filter('.ecl-meta__item'));
  }

  /**
   * Tests the list item block paragraph type.
   */
  public function testListItemBlock() {
    // Create three list items to be referenced from the list item block.
    $items = [];
    for ($i = 0; $i < 3; $i++) {
      $paragraph = Paragraph::create([
        'type' => 'oe_list_item',
        'oe_paragraphs_variant' => 'default',
        'field_oe_title' => 'Item title ' . $i,
        'field_oe_text_long' => 'Item description 1' . $i,
        'field_oe_link' => [
          'uri' => 'http://www.example.com/page/' . $i,
        ],
      ]);
      $paragraph->save();
      $items[$i] = $paragraph;
    }

    $paragraph = Paragraph::create([
      'type' => 'oe_list_item_block',
      'field_oe_list_item_block_layout' => 'one_column',
      'field_oe_title' => 'List block title',
      'field_oe_paragraphs' => $items,
      'field_oe_link' => [
        'uri' => 'http://www.example.com/',
        'title' => 'Read more',
      ],
    ]);
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertEquals('List block title', trim($crawler->filter('.ecl-heading')->text()));
    // Verify that the referenced paragraphs are being rendered.
    $this->assertCount(3, $crawler->filter('.ecl-listing .ecl-list-item'));
    // Verify that the one column variant is being rendered. No class is added
    // to this variant, so we check that neither the two or three columns class
    // modifiers are there.
    $this->assertCount(0, $crawler->filter('.ecl-listing.ecl-listing--two-columns'));
    $this->assertCount(0, $crawler->filter('.ecl-listing.ecl-listing--three-columns'));

    // Change the variant to two columns.
    $paragraph->get('field_oe_list_item_block_layout')->setValue('two_columns');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    // Verify that the referenced paragraphs are being rendered under the
    // correct variant.
    $this->assertCount(3, $crawler->filter('.ecl-listing.ecl-listing--two-columns .ecl-list-item'));

    // Change the variant to three columns.
    $paragraph->get('field_oe_list_item_block_layout')->setValue('three_columns');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(3, $crawler->filter('.ecl-listing.ecl-listing--three-columns .ecl-list-item'));
  }

  /**
   * Test quote paragraph rendering.
   */
  public function testRichText(): void {
    $body = 'Body text';

    $paragraph = Paragraph::create([
      'type' => 'oe_rich_text',
      'field_oe_text_long' => $body,
    ]);
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $actual = $crawler->filter('div.ecl-paragraph.ecl-paragraph--m p')->html();
    $this->assertEquals($body, trim($actual));

    // Add a title.
    $paragraph->get('field_oe_title')->setValue('Paragraph heading.');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertEquals('Paragraph heading.', trim($crawler->filter('h3.ecl-heading--h3')->text()));
  }

  /**
   * Data provider for the quote test method.
   *
   * @return array
   *   A set of dump data for testing.
   */
  public function quoteDataProvider(): array {
    return [
      // Test case with no data.
      [
        [
          'attribution' => '',
          'body' => '',
        ],
        [
          'attribution' => '',
          'body' => '',
        ],
      ],
      // Test case with no formatting.
      [
        [
          'attribution' => 'Quote author',
          'body' => 'Quote body',
        ],
        [
          'attribution' => 'Quote author',
          'body' => 'Quote body',
        ],
      ],
      // Test case with allowed formatting.
      [
        [
          'attribution' => 'Quote author',
          'body' => 'Quote body example@example.com',
        ],
        [
          'attribution' => 'Quote author',
          'body' => 'Quote body example@example.com',
        ],
      ],
      // Test case with not allowed formatting.
      [
        [
          'attribution' => 'Quote author',
          'body' => 'Quote <p>body</p>',
        ],
        [
          'attribution' => 'Quote author',
          'body' => 'Quote &lt;p&gt;body&lt;/p&gt;',
        ],
      ],
    ];
  }

}
