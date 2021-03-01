<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\media\Entity\Media;
use Drupal\Tests\oe_theme\PatternAssertions\ListItemAssert;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\user\Entity\User;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the News content type rendering.
 */
class NewsRenderTest extends ContentRenderTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    module_load_include('install', 'oe_content');
    oe_content_install();

    // Set current user to UID 1, so that by default we can access everything.
    $account = User::load(1);
    $this->setCurrentUser($account);
  }

  /**
   * Tests that the News node type is rendered with the correct ECL markup.
   */
  public function testNews(): void {
    $media = $this->container
      ->get('entity_type.manager')
      ->getStorage('media')->create([
        'bundle' => 'av_portal_photo',
        'oe_media_avportal_photo' => 'P-038924/00-15',
        'uid' => 0,
        'status' => 1,
      ]);

    $media->save();

    $node = $this->nodeStorage->create([
      'type' => 'oe_news',
      'title' => 'Test news node',
      'oe_teaser' => 'Teaser',
      'oe_summary' => 'Summary',
      'body' => 'Body',
      'oe_news_featured_media' => [
        [
          'target_id' => (int) $media->id(),
        ],
      ],
      'oe_publication_date' => '2019-04-05',
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

    // Body wrapper.
    $body_wrapper = $crawler->filter('.ecl-editor');
    $this->assertCount(1, $body_wrapper);
    $this->assertContains('Body', $body_wrapper->text());

    // Featured media.
    $image = $crawler->filter('article.ecl-u-type-paragraph picture img.ecl-u-width-100.ecl-u-height-auto');
    $this->assertContains('P-038924', $image->attr('src'));

    // Related links.
    $related_links_heading = $crawler->filter('.ecl-u-type-heading-2');
    $this->assertContains('Related links', $related_links_heading->text());
    $related_links = $crawler->filter('.ecl-list .ecl-link.ecl-link--standalone');
    $this->assertCount(2, $related_links);
    $link_one = $related_links->first();

    $this->assertContains('/en/node', trim($link_one->extract(['href'])[0]));
    $link_two = $related_links->first();
    $this->assertEquals('/en/node', trim($link_two->extract(['href'])[0]));
  }

  /**
   * Tests News node type rendered as teaser.
   */
  public function testNewsTeaser(): void {
    $file = file_save_data(file_get_contents(drupal_get_path('theme', 'oe_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_1.jpeg');
    $file->setPermanent();
    $file->save();

    $media = Media::create([
      'bundle' => 'image',
      'name' => 'test image',
      'oe_media_image' => [
        'target_id' => $file->id(),
        'alt' => 'Alt',
      ],
    ]);
    $media->save();

    $node = $this->nodeStorage->create([
      'type' => 'oe_news',
      'title' => 'Test news node',
      'oe_teaser' => 'Teaser',
      'oe_summary' => 'Summary',
      'body' => 'Body',
      'oe_news_featured_media' => [
        [
          'target_id' => (int) $media->id(),
        ],
      ],
      'oe_publication_date' => '2019-04-02',
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
      'oe_author' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'status' => 1,
    ]);
    $node->save();

    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);
    $assert = new ListItemAssert();

    $expected_values = [
      'title' => 'Test news node',
      'url' => '/en/node/1',
      'description' => 'Teaser',
      'meta' => 'News article | <time datetime="2019-04-02T12:00:00Z">2 April 2019</time>',
      'image' => [
        'src' => 'example_1.jpeg',
        'alt' => '',
      ],
    ];
    $assert->assertPattern($expected_values, $html);
    $assert->assertVariant('thumbnail_primary', $html);

    // Set news type.
    $node->set('oe_news_types', 'http://publications.europa.eu/resource/authority/resource-type/PRESS_REL')->save();
    $this->nodeViewBuilder->resetCache();

    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);

    $expected_values['meta'] = 'Press release | <time datetime="2019-04-02T12:00:00Z">2 April 2019</time>';
    $assert->assertPattern($expected_values, $html);
    $assert->assertVariant('thumbnail_primary', $html);

    // Set multiple news types.
    $node->set('oe_news_types', [
      'http://publications.europa.eu/resource/authority/resource-type/FACTSHEET',
      'http://publications.europa.eu/resource/authority/resource-type/PUB_GEN',
    ]);
    $this->nodeViewBuilder->resetCache();

    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);

    $expected_values['meta'] = 'Factsheet, General publications | <time datetime="2019-04-02T12:00:00Z">2 April 2019</time>';
    $assert->assertPattern($expected_values, $html);
    $assert->assertVariant('thumbnail_primary', $html);
  }

}
