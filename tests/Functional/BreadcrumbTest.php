<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

/**
 * Tests that breadcrumbs are cached correctly.
 *
 * @group batch1
 */
class BreadcrumbTest extends ContentRenderTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'config',
    'system',
    'oe_theme_helper',
    'path',
    'oe_theme_content_news',
    'block',
  ];


  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests that the breadcrumbs are cached correctly.
   */
  public function testBreadcrumbCaching(): void {
    // Ensure that the system breadcrumb is placed as well.
    $this->drupalPlaceBlock('system_breadcrumb_block', [
      'region' => 'page_header',
    ]);

    // Create a news node.
    /** @var \Drupal\node\Entity\Node $node */
    $node_1 = $this->getStorage('node')->create([
      'type' => 'oe_news',
      'title' => 'Test news node',
      'oe_news_types' => 'http://publications.europa.eu/resource/authority/resource-type/ARTICLE_NEWS',
      'oe_teaser' => 'News teaser',
      'oe_summary' => 'http://www.example.org is a web page',
      'body' => 'News body',
      'oe_reference_code' => 'News reference',
      'oe_publication_date' => [
        'value' => '2020-09-18',
      ],
      'oe_author' => 'http://publications.europa.eu/resource/authority/corporate-body/ACJHR',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'uid' => 0,
      'status' => 1,
    ]);
    $node_1->save();

    // Create another news node with different title.
    $node_2 = $this->getStorage('node')->create([
      'type' => 'oe_news',
      'title' => 'Test news article breadcrumb',
      'oe_news_types' => 'http://publications.europa.eu/resource/authority/resource-type/ARTICLE_NEWS',
      'oe_teaser' => 'News teaser',
      'oe_summary' => 'http://www.example.org is a web page',
      'body' => 'News body',
      'oe_reference_code' => 'News reference',
      'oe_publication_date' => [
        'value' => '2020-09-18',
      ],
      'oe_author' => 'http://publications.europa.eu/resource/authority/corporate-body/ACJHR',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'uid' => 0,
      'status' => 1,
    ]);
    $node_2->save();

    $expected = [
      'Home',
      'Node',
      'Test news node',
    ];
    $this->drupalGet($node_1->toUrl());
    $this->assertSystemBreadcrumbs($expected);

    $expected = [
      'Home',
      'Node',
      'Test news article breadcrumb',
    ];
    $this->drupalGet($node_2->toUrl());
    $this->assertSystemBreadcrumbs($expected);
  }

  /**
   * Helper to assert system breadcrumbs on the page.
   *
   * @param array $expected
   *   The expected breadcrumb titles in the expected order.
   */
  protected function assertSystemBreadcrumbs(array $expected): void {
    // Get the last segment title and the link titles.
    $last_segment_title = array_pop($expected);
    $page_breadcrumb = $this->assertSession()->elementExists('css', '[class="ecl-breadcrumb-core"]');

    // Assert the link titles.
    $links = $page_breadcrumb->findAll('css', 'a');
    $this->assertSameSize($expected, $links);
    foreach ($expected as $index => $title) {
      $this->assertEquals($title, trim($links[$index]->getText()));
    }

    // Check the last segment title.
    $current_page = $page_breadcrumb->findAll('css', 'li:last-child');
    $this->assertCount(1, $current_page);
    $current_page = reset($current_page);
    $this->assertEquals($last_segment_title, trim($current_page->getText()));
  }

}
