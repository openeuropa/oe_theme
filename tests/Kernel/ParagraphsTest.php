<?php

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
    'oe_paragraphs',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('paragraph');
    $this->installSchema('system', ['sequences']);
    $this->installConfig(['oe_paragraphs']);
  }

  /**
   * Test links block paragraph rendering.
   */
  public function testLinksBlock() {
    $paragraph = Paragraph::create([
      'type' => 'oe_paragraphs_links_block',
      'field_oe_paragraphs_text' => 'Title',
      'field_oe_paragraphs_links' => [
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
   * Render a paragraph.
   *
   * @param \Drupal\paragraphs\Entity\Paragraph $paragraph
   *   Paragraph entity.
   *
   * @return string
   *   Rendered output.
   */
  protected function renderParagraph(Paragraph $paragraph) {
    $render = \Drupal::entityTypeManager()
      ->getViewBuilder('paragraph')
      ->view($paragraph, 'default');

    return (string) \Drupal::service('renderer')->renderRoot($render);
  }

}
