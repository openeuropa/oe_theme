<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\Paragraphs;

use Drupal\file\Entity\File;
use Drupal\filter\Entity\FilterFormat;
use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the rendering of paragraphs types.
 *
 * @group batch1
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

    $actual = $crawler->filter('.ecl-accordion__title button.ecl-accordion__toggle span.ecl-accordion__toggle-title')->eq(0)->text();
    $this->assertEquals('Item title 1', trim($actual));

    $actual = $crawler->filter('.ecl-accordion__content')->eq(0)->text();
    $this->assertEquals('Item body 1', trim($actual));

    $actual = $crawler->filter('.ecl-accordion__title button.ecl-accordion__toggle span.ecl-accordion__toggle-title')->eq(1)->text();
    $this->assertEquals('Item title 2', trim($actual));

    $actual = $crawler->filter('.ecl-accordion__content')->eq(1)->text();
    $this->assertEquals('Item body 2', trim($actual));

    $this->assertCount(2, $crawler->filter('.ecl-accordion__title button.ecl-accordion__toggle .ecl-accordion__toggle-icon'));
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

    $actual = $crawler->filter('figure.ecl-blockquote .ecl-blockquote__body blockquote.ecl-blockquote__quote p.ecl-blockquote__citation')->html();
    $this->assertEquals($expected['body'], trim($actual));

    $actual = $crawler->filter('figure.ecl-blockquote footer.ecl-blockquote__attribution cite.ecl-blockquote__author')->text();
    $this->assertEquals($expected['attribution'], trim($actual));
  }

  /**
   * Tests the list item paragraph type.
   *
   * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
   */
  public function testListItem(): void {
    \Drupal::service('file_system')->copy($this->root . '/core/misc/druplicon.png', 'public://example.jpg');
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

    $this->assertCount(1, $crawler->filter('article.ecl-content-item'));
    $this->assertEquals('Item title', trim($crawler->filter('article.ecl-content-item div.ecl-content-item__content-block h1.ecl-content-block__title')->text()));
    $this->assertEquals('Item description', trim($crawler->filter('article.ecl-content-item div.ecl-content-item__content-block div.ecl-content-block__description')->text()));

    $link_element = $crawler->filter('article.ecl-content-item div.ecl-content-item__content-block h1.ecl-content-block__title a.ecl-link.ecl-link--standalone');
    $this->assertCount(1, $link_element);
    $this->assertEquals('http://www.example.com/', $link_element->attr('href'));

    $this->assertEquals('Meta 1', trim($crawler->filter('.ecl-content-block__primary-meta-container li.ecl-content-block__primary-meta-item:nth-child(1)')->text()));
    $this->assertEquals('Meta 2', trim($crawler->filter('.ecl-content-block__primary-meta-container li.ecl-content-block__primary-meta-item:nth-child(2)')->text()));
    $this->assertEquals('Meta 3', trim($crawler->filter('.ecl-content-block__primary-meta-container li.ecl-content-block__primary-meta-item:nth-child(3)')->text()));

    // No images should be rendered in this variant.
    $this->assertCount(0, $crawler->filter('article.ecl-content-item > picture.ecl-content-item__picture'));
    $this->assertCount(0, $crawler->filter('article.ecl-content-item > picture.ecl-content-item__picture--left'));
    $this->assertCount(0, $crawler->filter('article.ecl-content-item > picture.ecl-content-item__picture--right'));

    // No date should be rendered neither.
    $this->assertCount(0, $crawler->filter('time.ecl-date-block'));
    $this->assertCount(0, $crawler->filter('.ecl-date-block__day'));
    $this->assertCount(0, $crawler->filter('.ecl-date-block__month'));
    $this->assertCount(0, $crawler->filter('.ecl-date-block__year'));

    // Change the variant and test that the markup changed.
    $paragraph->get('oe_paragraphs_variant')->setValue('highlight');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertEquals('Item title', trim($crawler->filter('article.ecl-card div.ecl-card__body h1.ecl-content-block__title')->text()));
    $this->assertCount(1, $crawler->filter('.ecl-content-block__description'));
    $this->assertEquals('Item description', trim($crawler->filter('article.ecl-card div.ecl-card__body div.ecl-content-block__description')->text()));

    // No date should be rendered neither.
    $this->assertCount(0, $crawler->filter('time.ecl-date-block'));
    $this->assertCount(0, $crawler->filter('.ecl-date-block__day'));
    $this->assertCount(0, $crawler->filter('.ecl-date-block__month'));
    $this->assertCount(0, $crawler->filter('.ecl-date-block__year'));

    // Neither the metas.
    $this->assertCount(0, $crawler->filter('.ecl-content-block__primary-meta-container'));

    $link_element = $crawler->filter('article.ecl-card div.ecl-card__body h1.ecl-content-block__title a.ecl-link');
    $this->assertCount(1, $link_element);
    $this->assertEquals('http://www.example.com/', $link_element->attr('href'));

    $image_element = $crawler->filter('article.ecl-card div.ecl-card__image');
    $this->assertCount(1, $image_element);
    $this->assertStringContainsString(
      file_url_transform_relative(file_create_url($image->getFileUri())),
      $image_element->attr('style')
    );

    $this->assertEquals('Druplicon', $image_element->attr('aria-label'));

    // Change the variant and test that the markup changed.
    $paragraph->get('oe_paragraphs_variant')->setValue('block');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertEquals('Item title', trim($crawler->filter('article.ecl-card div.ecl-card__body h1.ecl-content-block__title')->text()));
    $this->assertEquals('Item description', trim($crawler->filter('article.ecl-card div.ecl-card__body div.ecl-content-block__description')->text()));

    // No date should be rendered neither.
    $this->assertCount(0, $crawler->filter('time.ecl-date-block'));
    $this->assertCount(0, $crawler->filter('.ecl-date-block__day'));
    $this->assertCount(0, $crawler->filter('.ecl-date-block__month'));
    $this->assertCount(0, $crawler->filter('.ecl-date-block__year'));

    // Neither the metas.
    $this->assertCount(0, $crawler->filter('.ecl-content-block__primary-meta-container'));

    $link_element = $crawler->filter('article.ecl-card div.ecl-card__body h1.ecl-content-block__title a.ecl-link');
    $this->assertCount(1, $link_element);
    $this->assertEquals('http://www.example.com/', $link_element->attr('href'));

    // No image should be rendered neither.
    $this->assertCount(0, $crawler->filter('article.ecl-card div.ecl-card__image'));

    // Change the variant to thumbnail primary.
    $paragraph->get('oe_paragraphs_variant')->setValue('thumbnail_primary');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertEquals('Item title', trim($crawler->filter('article.ecl-content-item div.ecl-content-item__content-block h1.ecl-content-block__title')->text()));
    $this->assertEquals('Item description', trim($crawler->filter('article.ecl-content-item div.ecl-content-item__content-block div.ecl-content-block__description')->text()));

    $link_element = $crawler->filter('article.ecl-content-item div.ecl-content-item__content-block h1.ecl-content-block__title a.ecl-link.ecl-link--standalone');
    $this->assertCount(1, $link_element);
    $this->assertEquals('http://www.example.com/', $link_element->attr('href'));

    $this->assertCount(1, $crawler->filter('article.ecl-content-item > picture.ecl-content-item__picture--left'));
    $this->assertCount(0, $crawler->filter('article.ecl-content-item > picture.ecl-content-item__picture--right'));
    $image_element = $crawler->filter('article.ecl-content-item > picture.ecl-content-item__picture--left img.ecl-content-item__image');
    $this->assertCount(1, $image_element);
    $this->assertStringContainsString(
      file_url_transform_relative(file_create_url($image->getFileUri())),
      $image_element->attr('src')
    );
    $this->assertEquals('Druplicon', $image_element->attr('alt'));

    $this->assertEquals('Meta 1', trim($crawler->filter('.ecl-content-block__primary-meta-container li.ecl-content-block__primary-meta-item:nth-child(1)')->text()));
    $this->assertEquals('Meta 2', trim($crawler->filter('.ecl-content-block__primary-meta-container li.ecl-content-block__primary-meta-item:nth-child(2)')->text()));
    $this->assertEquals('Meta 3', trim($crawler->filter('.ecl-content-block__primary-meta-container li.ecl-content-block__primary-meta-item:nth-child(3)')->text()));

    // No date should be rendered neither.
    $this->assertCount(0, $crawler->filter('time.ecl-date-block'));
    $this->assertCount(0, $crawler->filter('.ecl-date-block__day'));
    $this->assertCount(0, $crawler->filter('.ecl-date-block__month'));
    $this->assertCount(0, $crawler->filter('.ecl-date-block__year'));

    // Change the variant to thumbnail secondary.
    $paragraph->get('oe_paragraphs_variant')->setValue('thumbnail_secondary');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertEquals('Item title', trim($crawler->filter('article.ecl-content-item div.ecl-content-item__content-block h1.ecl-content-block__title')->text()));
    $this->assertEquals('Item description', trim($crawler->filter('article.ecl-content-item div.ecl-content-item__content-block div.ecl-content-block__description')->text()));

    $link_element = $crawler->filter('article.ecl-content-item div.ecl-content-item__content-block h1.ecl-content-block__title a.ecl-link.ecl-link--standalone');
    $this->assertCount(1, $link_element);
    $this->assertEquals('http://www.example.com/', $link_element->attr('href'));

    $this->assertCount(0, $crawler->filter('article.ecl-content-item > picture.ecl-content-item__picture--left'));
    $this->assertCount(1, $crawler->filter('article.ecl-content-item > picture.ecl-content-item__picture--right'));
    $image_element = $crawler->filter('article.ecl-content-item > picture.ecl-content-item__picture--right img.ecl-content-item__image');
    $this->assertCount(1, $image_element);
    $this->assertStringContainsString(
      file_url_transform_relative(file_create_url($image->getFileUri())),
      $image_element->attr('src')
    );
    $this->assertEquals('Druplicon', $image_element->attr('alt'));

    $this->assertEquals('Meta 1', trim($crawler->filter('.ecl-content-block__primary-meta-container li.ecl-content-block__primary-meta-item:nth-child(1)')->text()));
    $this->assertEquals('Meta 2', trim($crawler->filter('.ecl-content-block__primary-meta-container li.ecl-content-block__primary-meta-item:nth-child(2)')->text()));
    $this->assertEquals('Meta 3', trim($crawler->filter('.ecl-content-block__primary-meta-container li.ecl-content-block__primary-meta-item:nth-child(3)')->text()));

    // No date should be rendered neither.
    $this->assertCount(0, $crawler->filter('time.ecl-date-block'));
    $this->assertCount(0, $crawler->filter('.ecl-date-block__day'));
    $this->assertCount(0, $crawler->filter('.ecl-date-block__month'));
    $this->assertCount(0, $crawler->filter('.ecl-date-block__year'));

    // Change the variant to navigation.
    $paragraph->get('oe_paragraphs_variant')->setValue('navigation');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertEquals('Item title', trim($crawler->filter('article.ecl-content-item div.ecl-content-item__content-block h1.ecl-content-block__title')->text()));
    $this->assertEquals('Item description', trim($crawler->filter('article.ecl-content-item div.ecl-content-item__content-block div.ecl-content-block__description')->text()));

    $link_element = $crawler->filter('article.ecl-content-item div.ecl-content-item__content-block h1.ecl-content-block__title a.ecl-link.ecl-link--standalone');
    $this->assertCount(1, $link_element);
    $this->assertEquals('http://www.example.com/', $link_element->attr('href'));

    // No images should be rendered in this variant.
    $this->assertCount(0, $crawler->filter('article.ecl-content-item > picture.ecl-content-item__picture--left'));
    $this->assertCount(0, $crawler->filter('article.ecl-content-item > picture.ecl-content-item__picture--right'));

    // Neither the metas.
    $this->assertCount(0, $crawler->filter('.ecl-content-block__primary-meta-container'));

    // Change the variant to date.
    $paragraph->get('oe_paragraphs_variant')->setValue('date');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('time.ecl-date-block.ecl-content-item__date'));
    $this->assertEquals('Item title', trim($crawler->filter('div.ecl-content-item__content-block h1.ecl-content-block__title')->text()));
    $this->assertEquals('Item description', trim($crawler->filter('div.ecl-content-item__content-block div.ecl-content-block__description')->text()));

    $link_element = $crawler->filter('div.ecl-content-item__content-block h1.ecl-content-block__title a.ecl-link');
    $this->assertCount(1, $link_element);
    $this->assertEquals('http://www.example.com/', $link_element->attr('href'));

    $this->assertEquals('Meta 1', trim($crawler->filter('.ecl-content-block__primary-meta-container li.ecl-content-block__primary-meta-item:nth-child(1)')->text()));
    $this->assertEquals('Meta 2', trim($crawler->filter('.ecl-content-block__primary-meta-container li.ecl-content-block__primary-meta-item:nth-child(2)')->text()));
    $this->assertEquals('Meta 3', trim($crawler->filter('.ecl-content-block__primary-meta-container li.ecl-content-block__primary-meta-item:nth-child(3)')->text()));

    $this->assertCount(1, $crawler->filter('article.ecl-content-item time.ecl-date-block'));
    $this->assertEquals('24', trim($crawler->filter('article.ecl-content-item span.ecl-date-block__day')->text()));
    $this->assertEquals('Sep', trim($crawler->filter('article.ecl-content-item abbr.ecl-date-block__month')->text()));
    $this->assertEquals('1981', trim($crawler->filter('article.ecl-content-item span.ecl-date-block__year')->text()));
    $this->assertCount(1, $crawler->filter('article.ecl-content-item abbr.ecl-date-block__month[title="September"]'));

    // No images should be rendered in this variant.
    $this->assertCount(0, $crawler->filter('img.ecl-content-item__image'));
    $this->assertCount(0, $crawler->filter('picture.ecl-content-item__picture--left'));
    $this->assertCount(0, $crawler->filter('picture.ecl-content-item__picture--right'));
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
        'field_oe_text_long' => 'Item description ' . $i,
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

    $crawler = new Crawler($this->renderParagraph($paragraph));
    $this->assertEquals('List block title', trim($crawler->filter('h2.ecl-u-type-heading-2')->text()));
    // Assert that the external icon is rendered for each list item paragraph.
    $this->assertCount(3, $crawler->filter('svg.ecl-icon.ecl-icon--2xs.ecl-link__icon'));
    $this->assertEquals('<use xlink:href="/themes/custom/oe_theme/dist/ec/images/icons/sprites/icons.svg#external"></use>', $crawler->filter('svg.ecl-icon.ecl-icon--2xs.ecl-link__icon')->html());

    // Assert the external icon is rendered for the list item block.
    $this->assertCount(1, $crawler->filter('svg.ecl-icon.ecl-icon--xs.ecl-link__icon'));
    $this->assertEquals('<use xlink:href="/themes/custom/oe_theme/dist/ec/images/icons/sprites/icons.svg#external"></use>', $crawler->filter('svg.ecl-icon.ecl-icon--xs.ecl-link__icon')->html());

    // Verify that the referenced paragraphs are being rendered.
    $this->assertCount(3, $crawler->filter('div.ecl-content-item-block__item'));
    $this->assertCount(3, $crawler->filter('div.ecl-content-item-block__item article.ecl-content-item'));

    // Verify that the one column variant is being rendered. No class is added
    // to this variant, so we check that neither the two or three columns class
    // modifiers are there.
    $this->assertCount(3, $crawler->filter('div.ecl-content-item-block__item.ecl-col-12 article.ecl-content-item'));
    $this->assertCount(0, $crawler->filter('div.ecl-content-item-block__item.ecl-col-l-6 article.ecl-content-item'));
    $this->assertCount(0, $crawler->filter('div.ecl-content-item-block__item.ecl-col-l-4 article.ecl-content-item'));

    // Change the variant to two columns.
    $paragraph->get('field_oe_list_item_block_layout')->setValue('two_columns');
    $paragraph->save();

    $crawler = new Crawler($this->renderParagraph($paragraph));
    $this->assertCount(3, $crawler->filter('div.ecl-content-item-block__item.ecl-col-l-6 article.ecl-content-item'));
    $this->assertCount(0, $crawler->filter('div.ecl-content-item-block__item.ecl-col-l-4 article.ecl-content-item'));

    // Change the variant to three columns.
    $paragraph->get('field_oe_list_item_block_layout')->setValue('three_columns');
    $paragraph->save();

    $crawler = new Crawler($this->renderParagraph($paragraph));
    $this->assertCount(0, $crawler->filter('div.ecl-content-item-block__item.ecl-col-l-6 article.ecl-content-item'));
    $this->assertCount(3, $crawler->filter('div.ecl-content-item-block__item.ecl-col-l-4 article.ecl-content-item'));
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

    $actual = $crawler->filter('div.ecl p')->html();
    $this->assertEquals($body, trim($actual));

    // Add a title.
    $paragraph->get('field_oe_title')->setValue('Paragraph heading.');
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertEquals('Paragraph heading.', trim($crawler->filter('h2.ecl-u-type-heading-2')->text()));
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

  /**
   * Test 'contextual navigation' paragraph rendering.
   */
  public function testContextualNavigation(): void {
    $paragraph = Paragraph::create([
      'type' => 'oe_contextual_navigation',
      'field_oe_title' => 'Contextual navigation',
      'field_oe_links' => [
        [
          'title' => 'Link 1',
          'uri' => 'http://example.com/page-one',
        ],
        [
          'title' => 'Link 2',
          'uri' => 'http://example.com/page-two',
        ],
        [
          'title' => 'Internal link under eu domain',
          'uri' => 'http://ec.europa.eu/info',
        ],
        [
          'title' => 'Internal link',
          'uri' => 'internal:/',
        ],
      ],
      'field_oe_limit' => 1,
      'field_oe_text' => 'More links',
    ]);

    $paragraph->save();
    $html = $this->renderParagraph($paragraph);

    $crawler = new Crawler($html);
    $actual = $crawler->filter('nav.ecl-contextual-navigation > div.ecl-contextual-navigation__label')->text();
    $this->assertEquals('Contextual navigation', trim($actual));

    $link1 = $crawler->filter('nav.ecl-contextual-navigation ul.ecl-contextual-navigation__list a.ecl-contextual-navigation__link')->eq(0);
    $actual = $link1->filter('span.ecl-link__label')->text();
    $this->assertEquals('Link 1', trim($actual));
    $actual = $link1->attr('href');
    $this->assertEquals('http://example.com/page-one', trim($actual));
    $icon = $link1->filter('svg.ecl-icon.ecl-icon--2xs.ecl-link__icon')->html();
    $this->assertEquals('<use xlink:href="/themes/custom/oe_theme/dist/ec/images/icons/sprites/icons.svg#external"></use>', $icon);

    $link2 = $crawler->filter('nav.ecl-contextual-navigation ul.ecl-contextual-navigation__list a.ecl-contextual-navigation__link')->eq(1);
    $actual = $link2->filter('span.ecl-link__label')->text();
    $this->assertEquals('Link 2', trim($actual));
    $actual = $link2->attr('href');
    $this->assertEquals('http://example.com/page-two', trim($actual));
    $icon = $link2->filter('svg.ecl-icon.ecl-icon--2xs.ecl-link__icon')->html();
    $this->assertEquals('<use xlink:href="/themes/custom/oe_theme/dist/ec/images/icons/sprites/icons.svg#external"></use>', $icon);

    $actual = $crawler->filter('nav.ecl-contextual-navigation ul.ecl-contextual-navigation__list li.ecl-contextual-navigation__item--collapsed a.ecl-contextual-navigation__link')->eq(0)->text();
    $this->assertEquals('Link 2', trim($actual));

    $link3 = $crawler->filter('nav.ecl-contextual-navigation ul.ecl-contextual-navigation__list a.ecl-contextual-navigation__link')->eq(2);
    $actual = $link3->text();
    $this->assertEquals('Internal link under eu domain', trim($actual));
    $actual = $link3->attr('href');
    $this->assertEquals('http://ec.europa.eu/info', trim($actual));
    $this->assertCount(0, $link3->filter('svg.ecl-icon.ecl-icon--xs.ecl-link__icon'));

    $link4 = $crawler->filter('nav.ecl-contextual-navigation ul.ecl-contextual-navigation__list a.ecl-contextual-navigation__link')->eq(3);
    $actual = $link4->text();
    $this->assertEquals('Internal link', trim($actual));
    $actual = $link4->attr('href');
    $this->assertEquals('/', trim($actual));
    $this->assertCount(0, $link4->filter('svg.ecl-icon.ecl-icon--xs.ecl-link__icon'));

    $actual = $crawler->filter('nav.ecl-contextual-navigation ul.ecl-contextual-navigation__list button.ecl-contextual-navigation__more')->text();
    $this->assertEquals('More links', trim($actual));
  }

  /**
   * Test 'Facts and figures' paragraph rendering.
   */
  public function testFactsFigures(): void {
    // Create three Facts to be referenced from the Facts and figures paragraph.
    $items = [];
    $icons = [
      1 => 'infographic',
      2 => 'spreadsheet',
      3 => 'digital',
    ];
    for ($i = 1; $i < 4; $i++) {
      $paragraph = Paragraph::create([
        'type' => 'oe_fact',
        'field_oe_icon' => $icons[$i],
        'field_oe_title' => $i . '0 millions',
        'field_oe_subtitle' => 'Fact ' . $i,
        'field_oe_plain_text_long' => 'Fact description ' . $i,
      ]);
      $paragraph->save();
      $items[$i] = $paragraph;
    }

    // Assert paragraph in "European Commission" and "European Union" component
    // libraries.
    $test_cases = [
      'eu' => [
        'fact_icon_size' => 'l',
      ],
      'ec' => [
        'fact_icon_size' => 'm',
      ],
    ];
    foreach ($test_cases as $component_library => $icon_sizes) {
      // Set the proper component library to test paragraph rendering.
      $this->config('oe_theme.settings')
        ->set('component_library', $component_library)
        ->save();
      drupal_static_reset('theme_get_setting');

      $paragraph = Paragraph::create([
        'type' => 'oe_facts_figures',
        'field_oe_title' => 'Facts and figures',
        'field_oe_paragraphs' => $items,
        'field_oe_link' => [
          'uri' => 'http://www.example.com/',
          'title' => 'View all metrics',
        ],
      ]);
      $paragraph->save();

      $html = $this->renderParagraph($paragraph);
      $crawler = new Crawler($html);
      $this->assertFactsFigures($crawler, $component_library, $icon_sizes['fact_icon_size']);

      // Assert paragraph with empty Title and Link fields.
      $paragraph = Paragraph::create([
        'type' => 'oe_facts_figures',
        'field_oe_paragraphs' => $items,
      ]);
      $paragraph->save();
      $html = $this->renderParagraph($paragraph);
      $crawler = new Crawler($html);
      $this->assertCount(0, $crawler->filter('h2.ecl-u-type-heading-2'));
      $this->assertCount(0, $crawler->filter('div.ecl-fact-figures__view-all a.ecl-link.ecl-link--standalone.ecl-fact-figures__view-all-link'));
      $this->assertCount(1, $crawler->filter('div.ecl-fact-figures.ecl-fact-figures--col-3 div.ecl-fact-figures__items'));
    }
  }

  /**
   * Assert rendered facts and figures paragraph.
   *
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   DOM Crawler instance.
   * @param string $component_library
   *   Component library.
   * @param string $fact_icon_size
   *   Size of the icon in facts.
   */
  protected function assertFactsFigures(Crawler $crawler, string $component_library, string $fact_icon_size): void {
    $this->assertCount(1, $crawler->filter('div.ecl-fact-figures.ecl-fact-figures--col-3 div.ecl-fact-figures__items'));

    $this->assertCount(1, $crawler->filter("div.ecl-fact-figures__item:nth-child(1) svg.ecl-icon.ecl-icon--$fact_icon_size.ecl-fact-figures__icon"), $component_library);
    $this->assertCount(1, $crawler->filter("div.ecl-fact-figures__item:nth-child(2) svg.ecl-icon.ecl-icon--$fact_icon_size.ecl-fact-figures__icon"));
    $this->assertCount(1, $crawler->filter("div.ecl-fact-figures__item:nth-child(3) svg.ecl-icon.ecl-icon--$fact_icon_size.ecl-fact-figures__icon"));

    $this->assertEquals('Facts and figures', trim($crawler->filter('h2.ecl-u-type-heading-2')->text()));
    $this->assertEquals("<use xlink:href=\"/themes/custom/oe_theme/dist/$component_library/images/icons/sprites/icons.svg#infographic\"></use>", $crawler->filter("div.ecl-fact-figures__item:nth-child(1) svg.ecl-icon.ecl-icon--$fact_icon_size.ecl-fact-figures__icon")->html());
    $this->assertEquals("<use xlink:href=\"/themes/custom/oe_theme/dist/$component_library/images/icons/sprites/icons.svg#spreadsheet\"></use>", $crawler->filter("div.ecl-fact-figures__item:nth-child(2) svg.ecl-icon.ecl-icon--$fact_icon_size.ecl-fact-figures__icon")->html());
    $this->assertEquals("<use xlink:href=\"/themes/custom/oe_theme/dist/$component_library/images/icons/sprites/icons.svg#digital\"></use>", $crawler->filter("div.ecl-fact-figures__item:nth-child(3) svg.ecl-icon.ecl-icon--$fact_icon_size.ecl-fact-figures__icon")->html());
    $this->assertEquals('10 millions', trim($crawler->filter('div.ecl-fact-figures__item:nth-child(1) div.ecl-fact-figures__value')->text()));
    $this->assertEquals('20 millions', trim($crawler->filter('div.ecl-fact-figures__item:nth-child(2) div.ecl-fact-figures__value')->text()));
    $this->assertEquals('30 millions', trim($crawler->filter('div.ecl-fact-figures__item:nth-child(3) div.ecl-fact-figures__value')->text()));
    $this->assertEquals('Fact 1', trim($crawler->filter('div.ecl-fact-figures__item:nth-child(1) div.ecl-fact-figures__title')->text()));
    $this->assertEquals('Fact 2', trim($crawler->filter('div.ecl-fact-figures__item:nth-child(2) div.ecl-fact-figures__title')->text()));
    $this->assertEquals('Fact 3', trim($crawler->filter('div.ecl-fact-figures__item:nth-child(3) div.ecl-fact-figures__title')->text()));
    $this->assertEquals('Fact description 1', trim($crawler->filter('div.ecl-fact-figures__item:nth-child(1) div.ecl-fact-figures__description')->text()));
    $this->assertEquals('Fact description 2', trim($crawler->filter('div.ecl-fact-figures__item:nth-child(2) div.ecl-fact-figures__description')->text()));
    $this->assertEquals('Fact description 3', trim($crawler->filter('div.ecl-fact-figures__item:nth-child(3) div.ecl-fact-figures__description')->text()));

    $link = $crawler->filter('div.ecl-fact-figures__view-all a.ecl-link.ecl-link--standalone.ecl-link--icon.ecl-link--icon-after.ecl-fact-figures__view-all-link');
    $actual = $link->text();
    $this->assertEquals('View all metrics', trim($actual));
    $actual = $link->attr('href');
    $this->assertEquals('http://www.example.com/', trim($actual));
    $icon = $link->filter('svg.ecl-icon.ecl-icon--2xs.ecl-link__icon')->html();
    $this->assertEquals("<use xlink:href=\"/themes/custom/oe_theme/dist/$component_library/images/icons/sprites/icons.svg#external\"></use>", $icon);
  }

  /**
   * Test 'Description List' paragraph rendering.
   */
  public function testDescriptionList(): void {
    FilterFormat::create([
      'format' => 'filtered_html',
      'name' => 'Filtered HTML',
      'filters' => [
        'filter_html' => [
          'status' => 1,
          'settings' => [
            'allowed_html' => '<strong>',
          ],
        ],
      ],
    ])->save();

    FilterFormat::create([
      'format' => 'full_html',
      'name' => 'Full HTML',
    ])->save();

    $paragraph = Paragraph::create([
      'type' => 'oe_description_list',
      'field_oe_title' => 'Overview',
      'field_oe_description_list_items' => [
        [
          'term' => 'Term 1',
          'description' => 'Description 1',
        ],
        [
          'term' => 'Term 2',
          'description' => '<p>Description 2</p>',
        ],
        [
          'term' => 'Term 3',
          'description' => '<p>Description <strong>3</strong></p>',
          'format' => 'plain_text',
        ],
        [
          'term' => 'Term 4',
          'description' => '<p>Description <strong>4</strong></p>',
          'format' => 'filtered_html',
        ],
        [
          'term' => 'Term 5',
          'description' => '<p>Description <strong>5</strong></p>',
          'format' => 'full_html',
        ],
      ],
    ]);

    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, ($crawler->filter('h2.ecl-u-type-heading-2')));
    $this->assertEquals('Overview', trim($crawler->filter('h2.ecl-u-type-heading-2')->text()));
    $this->assertCount(1, $crawler->filter('dl.ecl-description-list.ecl-description-list--horizontal'));
    $this->assertCount(5, $crawler->filter('dt.ecl-description-list__term'));
    $this->assertCount(5, $crawler->filter('dd.ecl-description-list__definition'));
    $this->assertEquals('Term 1', trim($crawler->filter('dt.ecl-description-list__term:nth-child(1)')->html()));
    $this->assertEquals('<div class="ecl"><p>Description 1</p></div>', trim($crawler->filter('dd.ecl-description-list__definition:nth-child(2)')->html()));
    $this->assertEquals('Term 2', trim($crawler->filter('dt.ecl-description-list__term:nth-child(3)')->html()));
    $this->assertEquals('<div class="ecl"><p>&lt;p&gt;Description 2&lt;/p&gt;</p></div>', trim($crawler->filter('dd.ecl-description-list__definition:nth-child(4)')->html()));
    $this->assertEquals('Term 3', trim($crawler->filter('dt.ecl-description-list__term:nth-child(5)')->html()));
    $this->assertEquals('<div class="ecl"><p>&lt;p&gt;Description &lt;strong&gt;3&lt;/strong&gt;&lt;/p&gt;</p></div>', trim($crawler->filter('dd.ecl-description-list__definition:nth-child(6)')->html()));
    $this->assertEquals('Term 4', trim($crawler->filter('dt.ecl-description-list__term:nth-child(7)')->html()));
    $this->assertEquals('<div class="ecl">Description <strong>4</strong></div>', trim($crawler->filter('dd.ecl-description-list__definition:nth-child(8)')->html()));
    $this->assertEquals('Term 5', trim($crawler->filter('dt.ecl-description-list__term:nth-child(9)')->html()));
    $this->assertEquals('<div class="ecl"><p>Description <strong>5</strong></p></div>', trim($crawler->filter('dd.ecl-description-list__definition:nth-child(10)')->html()));

    // Move the last item on the first position.
    $items = $paragraph->get('field_oe_description_list_items')->getValue();
    $fifth_item = $items[4];
    $items[4] = $items[0];
    $items[0] = $fifth_item;
    $paragraph->set('field_oe_description_list_items', $items);
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);
    // Assert the order was updated.
    $this->assertEquals('Term 5', trim($crawler->filter('dt.ecl-description-list__term:nth-child(1)')->html()));
    $this->assertEquals('<div class="ecl"><p>Description <strong>5</strong></p></div>', trim($crawler->filter('dd.ecl-description-list__definition:nth-child(2)')->html()));
    $this->assertEquals('Term 1', trim($crawler->filter('dt.ecl-description-list__term:nth-child(9)')->html()));
    $this->assertEquals('<div class="ecl"><p>Description 1</p></div>', trim($crawler->filter('dd.ecl-description-list__definition:nth-child(10)')->html()));
  }

}
