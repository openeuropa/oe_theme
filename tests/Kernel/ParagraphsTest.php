<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class ParagraphsTests.
 *
 * @package Drupal\Tests\oe_theme\Kernel
 */
class ParagraphsTest extends AbstractKernelTest {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'paragraphs',
    'user',
    'system',
    'file',
    'field',
    'entity_reference_revisions',
    'link',
    'text',
    'filter',
    'options',
    'oe_paragraphs',
    'allowed_formats',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('paragraph');
    $this->installConfig(['oe_paragraphs', 'filter']);
  }

  /**
   * Test links block paragraph rendering.
   */
  public function testLinksBlock() {
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
  public function testAccordions() {
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

    $actual = $crawler->filter('button#ecl-accordion-header-1')->text();
    $this->assertEquals('Item title 1', trim($actual));

    $actual = $crawler->filter('dd#ecl-accordion-panel-1')->text();
    $this->assertEquals('Item body 1', trim($actual));

    $actual = $crawler->filter('button#ecl-accordion-header-1 span.ecl-icon--arrow-up');
    $this->assertCount(1, $actual);

    $actual = $crawler->filter('button#ecl-accordion-header-2')->text();
    $this->assertEquals('Item title 2', trim($actual));

    $actual = $crawler->filter('dd#ecl-accordion-panel-2')->text();
    $this->assertEquals('Item body 2', trim($actual));

    $actual = $crawler->filter('button#ecl-accordion-header-2 span.ecl-icon--copy');
    $this->assertCount(1, $actual);
  }

  /**
   * Test quote paragraph rendering.
   *
   * @dataProvider quoteDataProvider
   */
  public function testQuote($data, $expected) {
    $paragraph = Paragraph::create([
      'type' => 'oe_quote',
      'field_oe_text' => $data['attribution'],
      'field_oe_text_long' => $data['body'],
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
   * Data provider.
   */
  public function quoteDataProvider() {
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
          'body' => '<p>Quote body</p>',
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
          'body' => '<p>Quote body <a href="mailto:example@example.com">example@example.com</a></p>',
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
          'body' => '<p>Quote &lt;p&gt;body&lt;/p&gt;</p>',
        ],
      ],
    ];
  }

  /**
   * Render a paragraph.
   *
   * @param \Drupal\paragraphs\Entity\Paragraph $paragraph
   *   Paragraph entity.
   *
   * @return string
   *   Rendered output.
   *
   * @throws \Exception
   */
  protected function renderParagraph(Paragraph $paragraph) {
    $render = \Drupal::entityTypeManager()
      ->getViewBuilder('paragraph')
      ->view($paragraph, 'default');

    return $this->renderRoot($render);
  }

}
