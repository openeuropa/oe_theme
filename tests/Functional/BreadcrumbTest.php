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
  public static $modules = [
    'config',
    'system',
    'oe_theme_helper',
    'path',
    'oe_theme_content_news',
    'block',
  ];

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

    $this->drupalGet($node_1->toUrl());
    $this->assertSystemBreadcrumbs('Test news node');

    $this->drupalGet($node_2->toUrl());
    $this->assertSystemBreadcrumbs('Test news article breadcrumb');
  }

  /**
   * Helper to assert system breadcrumbs on the page.
   *
   * @param string $last_segment_title
   *   The expected last segment.
   */
  protected function assertSystemBreadcrumbs(string $last_segment_title): void {
    // Assert the link titles.
    $page_breadrumb = $this->assertSession()->elementExists('css', '[class="ecl-breadcrumb-core"]');
    $links = $page_breadrumb->findAll('css', 'ol.ecl-breadcrumb-core__container li.ecl-breadcrumb-core__segment a.ecl-breadcrumb-core__link');
    $this->assertCount(2, $links);
    $this->assertEquals('Home', trim($links[0]->getText()));
    $this->assertEquals('Node', trim($links[1]->getText()));

    // Check the last segment title.
    $current_page = $page_breadrumb->findAll('css', 'ol.ecl-breadcrumb-core__container li.ecl-breadcrumb-core__current-page');
    $this->assertCount(1, $current_page);
    $current_page = reset($current_page);
    $this->assertEquals($last_segment_title, trim($current_page->getText()));
  }

}
