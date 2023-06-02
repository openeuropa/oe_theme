<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests that our content types render with correct markup.
 *
 * @todo move the individual content type tests to their own classes.
 *
 * @group batch2
 */
class LegacyContentRenderTest extends ContentRenderTestBase {

  /**
   * Tests that the Page node type is rendered with the correct ECL markup.
   */
  public function testPage(): void {
    $node = $this->nodeStorage->create([
      'type' => 'oe_news',
      'title' => 'Test page node',
      'body' => 'Body',
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
      'oe_author' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'oe_related_links' => [
        [
          'uri' => 'internal:/node',
          'title' => 'Node listing',
        ],
        [
          'uri' => 'https://example.com',
          'title' => 'External link',
        ],
      ],
    ]);
    $node->save();

    $build = $this->nodeViewBuilder->view($node);
    $html = $this->renderRoot($build);

    $crawler = new Crawler($html);

    $title = $crawler->filter('h2 a span');
    $this->assertEquals('Test page node', $title->text());

    // Body wrapper.
    $body_wrapper = $crawler->filter('.ecl');
    $this->assertCount(1, $body_wrapper);
    $this->assertStringContainsString('Body', $body_wrapper->text());

    // Related links.
    $related_links_heading = $crawler->filter('.ecl-u-type-heading-2');
    $this->assertStringContainsString('Related links', $related_links_heading->text());
    $related_links = $crawler->filter('div.ecl-u-border-bottom.ecl-u-border-color-grey-15.ecl-u-pt-m.ecl-u-pb-m a');
    $this->assertCount(2, $related_links);

    // Test short title fallback.
    $node->set('oe_content_short_title', 'Page short title')->save();
    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);
    $crawler = new Crawler($html);
    $title = $crawler->filter('div.ecl-content-item__content-block h1.ecl-content-block__title');
    $this->assertEquals('Page short title', $title->text());
  }

  /**
   * Tests that the Policy node type is rendered with the correct ECL markup.
   */
  public function testPolicy(): void {
    $node = $this->nodeStorage->create([
      'type' => 'oe_policy',
      'title' => 'Test policy node',
      'body' => 'Body',
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
      'oe_author' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
    ]);
    $node->save();

    $build = $this->nodeViewBuilder->view($node);
    $html = $this->renderRoot($build);

    $crawler = new Crawler($html);

    $title = $crawler->filter('h2 a span');
    $this->assertEquals('Test policy node', $title->text());

    // Body wrapper.
    $body_wrapper = $crawler->filter('.ecl');
    $this->assertCount(1, $body_wrapper);
    $this->assertStringContainsString('Body', $body_wrapper->text());

    // Test short title fallback.
    $node->set('oe_content_short_title', 'Policy short title')->save();
    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);
    $crawler = new Crawler($html);
    $title = $crawler->filter('div.ecl-content-item__content-block h1.ecl-content-block__title');
    $this->assertEquals('Policy short title', $title->text());
  }

  /**
   * Tests that the Publication node is rendered with the correct ECL markup.
   */
  public function testPublication(): void {
    $file = \Drupal::service('file.repository')->writeData(file_get_contents(\Drupal::service('extension.list.module')->getPath('oe_media') . '/tests/fixtures/sample.pdf'), 'public://test.pdf');
    $file->setPermanent();
    $file->save();

    $media = $this->container
      ->get('entity_type.manager')
      ->getStorage('media')->create([
        'bundle' => 'document',
        'name' => 'test document',
        'oe_media_file_type' => 'local',
        'oe_media_file' => [
          'target_id' => (int) $file->id(),
        ],
        'uid' => 0,
        'status' => 1,
      ]);

    $media->save();

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->nodeStorage->create([
      'type' => 'oe_publication',
      'title' => 'Test Publication node',
      'oe_documents' => [
        [
          'target_id' => (int) $media->id(),
        ],
      ],
      'oe_summary' => 'Summary',
      'oe_publication_date' => '2019-04-05',
      'uid' => 0,
      'status' => 1,
    ]);
    $node->save();

    $build = $this->nodeViewBuilder->view($node, 'default');
    $html = $this->renderRoot($build);

    $crawler = new Crawler($html);

    // File wrapper.
    $file_wrapper = $crawler->filter('.ecl-file');
    $this->assertCount(1, $file_wrapper);

    // File row.
    $file_row = $crawler->filter('.ecl-file .ecl-file__container');
    $this->assertCount(1, $file_row);

    $file_title = $file_row->filter('.ecl-file__title');
    $this->assertStringContainsString('test document', $file_title->text());

    $file_info_language = $file_row->filter('.ecl-file__info div.ecl-file__language');
    $this->assertStringContainsString('English', $file_info_language->text());

    $file_info_properties = $file_row->filter('.ecl-file__info div.ecl-file__meta');
    $this->assertStringContainsString('KB - PDF)', $file_info_properties->text());

    $file_download_link = $file_row->filter('.ecl-file__download');
    $this->assertStringContainsString('/test.pdf', $file_download_link->attr('href'));
    $this->assertStringContainsString('Download', $file_download_link->text());
  }

}
