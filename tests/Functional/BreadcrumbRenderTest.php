<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

/**
 * Tests that breadcrumbs are rendered correctly.
 *
 * @group batch1
 */
class BreadcrumbRenderTest extends ContentRenderTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'config',
    'system',
    'oe_theme_helper',
    'path',
    'oe_theme_content_news',
    'block',
  ];

  /**
   * Tests that the breadcrumbs are rendered correctly for news content type.
   */
  public function testNewsBreadcrumbRendering(): void {
    // Ensure that the system breadcrumb is placed as well.
    $this->drupalPlaceBlock('system_breadcrumb_block', [
      'region' => 'page_header',
    ]);

    // Create a News node.
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

    // Create a news article with different title.
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

    $this->drupalGet($node_1->toUrl());
    $this->assertBreadcrumbs('Test news node');

    $this->drupalGet($node_2->toUrl());
    $this->assertBreadcrumbs('Test news article breadcrumb');
  }

  /**
   * Helper to assert both system and page header breadcrumbs on the page.
   *
   * @param string $last_segment_title
   *   The expected last segment.
   */
  protected function assertBreadcrumbs(string $last_segment_title): void {
    $breadcrumbs = [
      // Test the page header breadcrumb.
      '.ecl-page-header-core .ecl-breadcrumb-core',
      // Test the system breadcrumb.
      '[class="ecl-breadcrumb-core"]',
    ];

    foreach ($breadcrumbs as $breadcrumb) {
      // Check for the number of segments.
      $page_breadrumb = $this->assertSession()->elementExists('css', $breadcrumb);
      $segments = $page_breadrumb->findAll('css', '.ecl-breadcrumb-core__segment');
      $this->assertCount(3, $segments);

      // Check for the number of links.
      $links_count = $page_breadrumb->findAll('css', 'ol.ecl-breadcrumb-core__container li.ecl-breadcrumb-core__segment a.ecl-breadcrumb-core__link');
      $this->assertCount(2, $links_count);

      // Check the last segment.
      $current_page = $page_breadrumb->findAll('css', 'ol.ecl-breadcrumb-core__container li.ecl-breadcrumb-core__current-page');
      $this->assertCount(1, $current_page);
      $current_page = reset($current_page);
      $this->assertEquals($last_segment_title, trim($current_page->getText()));
    }
  }

}
