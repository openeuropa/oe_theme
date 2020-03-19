<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\Paragraphs;

use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the rendering of Timeline paragraph.
 */
class TimelineParagraphTest extends ParagraphsTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'oe_paragraphs_timeline',
    'oe_content_timeline_field',
    'node',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installConfig([
      'oe_paragraphs_timeline',
      'oe_content_timeline_field',
      'node',
    ]);
  }

  /**
   * Test 'timeline' paragraph rendering.
   */
  public function testTimeline(): void {
    $paragraph = Paragraph::create([
      'type' => 'oe_timeline',
      'field_oe_timeline_expand' => '3',
      'field_oe_timeline' => [
        [
          'label' => 'Label 1',
          'title' => 'Title 1',
          'body' => 'Description 1',
        ],
        [
          'label' => 'Label 2',
          'title' => 'Title 2',
          'body' => 'Description 2',
        ],
        [
          'label' => 'Label 3',
          'title' => 'Title 3',
          'body' => 'Description 3',
        ],
        [
          'label' => 'Label 4',
          'title' => 'Title 4',
          'body' => 'Description 4',
        ],
        [
          'label' => 'Label 5',
          'title' => 'Title 5',
          'body' => 'Description 5',
        ],
        [
          'label' => 'Label 6',
          'title' => 'Title 6',
          'body' => 'Description 6',
        ],
      ],
    ]);

    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('ol.ecl-timeline2'));
    $this->assertCount(7, $crawler->filter('ol.ecl-timeline2 li.ecl-timeline2__item'));
    $this->assertCount(3, $crawler->filter('ol.ecl-timeline2 li.ecl-timeline2__item.ecl-timeline2__item--collapsed'));
    $this->assertCount(1, $crawler->filter('ol.ecl-timeline2 li.ecl-timeline2__item.ecl-timeline2__item--toggle button.ecl-button.ecl-button--secondary.ecl-timeline2__toggle'));

    $this->assertEquals('Label 1', trim($crawler->filter('ol.ecl-timeline2 li.ecl-timeline2__item:nth-child(1) div.ecl-timeline2__label')->text()));
    $this->assertEquals('Title 1', trim($crawler->filter('ol.ecl-timeline2 li.ecl-timeline2__item:nth-child(1) div.ecl-timeline2__title')->text()));
    $this->assertEquals('Description 1', trim($crawler->filter('ol.ecl-timeline2 li.ecl-timeline2__item:nth-child(1) div.ecl-timeline2__content')->text()));
    $this->assertEquals('Label 2', trim($crawler->filter('ol.ecl-timeline2 li.ecl-timeline2__item:nth-child(2) div.ecl-timeline2__label')->text()));
    $this->assertEquals('Title 2', trim($crawler->filter('ol.ecl-timeline2 li.ecl-timeline2__item:nth-child(2) div.ecl-timeline2__title')->text()));
    $this->assertEquals('Description 2', trim($crawler->filter('ol.ecl-timeline2 li.ecl-timeline2__item:nth-child(2) div.ecl-timeline2__content')->text()));
    $this->assertEquals('Label 3', trim($crawler->filter('ol.ecl-timeline2 li.ecl-timeline2__item:nth-child(3) div.ecl-timeline2__label')->text()));
    $this->assertEquals('Title 3', trim($crawler->filter('ol.ecl-timeline2 li.ecl-timeline2__item:nth-child(3) div.ecl-timeline2__title')->text()));
    $this->assertEquals('Description 3', trim($crawler->filter('ol.ecl-timeline2 li.ecl-timeline2__item:nth-child(3) div.ecl-timeline2__content')->text()));
    $this->assertEquals('Label 4', trim($crawler->filter('ol.ecl-timeline2 li.ecl-timeline2__item.ecl-timeline2__item--collapsed:nth-child(4) div.ecl-timeline2__label')->text()));
    $this->assertEquals('Title 4', trim($crawler->filter('ol.ecl-timeline2 li.ecl-timeline2__item.ecl-timeline2__item--collapsed:nth-child(4) div.ecl-timeline2__title')->text()));
    $this->assertEquals('Description 4', trim($crawler->filter('ol.ecl-timeline2 li.ecl-timeline2__item.ecl-timeline2__item--collapsed:nth-child(4) div.ecl-timeline2__content')->text()));
    $this->assertEquals('Label 5', trim($crawler->filter('ol.ecl-timeline2 li.ecl-timeline2__item.ecl-timeline2__item--collapsed:nth-child(5) div.ecl-timeline2__label')->text()));
    $this->assertEquals('Title 5', trim($crawler->filter('ol.ecl-timeline2 li.ecl-timeline2__item.ecl-timeline2__item--collapsed:nth-child(5) div.ecl-timeline2__title')->text()));
    $this->assertEquals('Description 5', trim($crawler->filter('ol.ecl-timeline2 li.ecl-timeline2__item.ecl-timeline2__item--collapsed:nth-child(5) div.ecl-timeline2__content')->text()));
    $this->assertEquals('Label 6', trim($crawler->filter('ol.ecl-timeline2 li.ecl-timeline2__item.ecl-timeline2__item--collapsed:nth-child(6) div.ecl-timeline2__label')->text()));
    $this->assertEquals('Title 6', trim($crawler->filter('ol.ecl-timeline2 li.ecl-timeline2__item.ecl-timeline2__item--collapsed:nth-child(6) div.ecl-timeline2__title')->text()));
    $this->assertEquals('Description 6', trim($crawler->filter('ol.ecl-timeline2 li.ecl-timeline2__item.ecl-timeline2__item--collapsed:nth-child(6) div.ecl-timeline2__content')->text()));
    $this->assertEquals('Show 3 more items', trim($crawler->filter('button.ecl-button.ecl-button--secondary.ecl-timeline2__toggle span.ecl-button__container span.ecl-button__label')->text()));

    $paragraph->set('field_oe_timeline_expand', '6');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(6, $crawler->filter('ol.ecl-timeline2 li.ecl-timeline2__item'));
    $this->assertCount(0, $crawler->filter('ol.ecl-timeline2 li.ecl-timeline2__item.ecl-timeline2__item--collapsed'));
    $this->assertCount(0, $crawler->filter('ol.ecl-timeline2 li.ecl-timeline2__item.ecl-timeline2__item--toggle'));
  }

}
