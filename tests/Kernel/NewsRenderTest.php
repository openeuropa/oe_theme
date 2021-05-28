<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\media\Entity\Media;
use Drupal\Tests\oe_theme\PatternAssertions\ListItemAssert;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * Tests the News content type rendering.
 *
 * @group batch2
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
      'meta' => 'News article | 2 April 2019',
      'image' => [
        'src' => 'example_1.jpeg',
        'alt' => '',
      ],
    ];
    $assert->assertPattern($expected_values, $html);
    $assert->assertVariant('thumbnail_primary', $html);

    // Test short title fallback.
    $node->set('oe_content_short_title', 'News short title')->save();
    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);
    $expected_values['title'] = 'News short title';
    $assert->assertPattern($expected_values, $html);

    // Unpublish the media and assert it is not rendered anymore.
    $media->set('status', 0);
    $media->save();

    // Since static cache is not cleared due to lack of requests in the test we
    // need to reset manually.
    $this->container->get('entity_type.manager')->getAccessControlHandler('media')->resetCache();

    $this->nodeViewBuilder->resetCache();
    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);

    $expected_values = [
      'title' => 'Test news node',
      'url' => '/en/node/1',
      'description' => 'Teaser',
      'meta' => 'News article | 2 April 2019',
      'image' => NULL,
    ];
    $assert->assertPattern($expected_values, $html);
    $assert->assertVariant('default', $html);

    // Publish the media.
    $media->set('status', 1);
    $media->save();

    // Since static cache is not cleared due to lack of requests in the test we
    // need to reset manually.
    $this->container->get('entity_type.manager')->getAccessControlHandler('media')->resetCache();

    // Set news type.
    $node->set('oe_news_types', 'http://publications.europa.eu/resource/authority/resource-type/PRESS_REL')->save();
    $this->nodeViewBuilder->resetCache();

    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);

    $expected_values = [
      'title' => 'Test news node',
      'url' => '/en/node/1',
      'description' => 'Teaser',
      'meta' => 'Press release | 2 April 2019',
      'image' => [
        'src' => 'example_1.jpeg',
        'alt' => '',
      ],
    ];
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

    $expected_values['meta'] = 'Factsheet, General publications | 2 April 2019';
    $assert->assertPattern($expected_values, $html);
    $assert->assertVariant('thumbnail_primary', $html);
  }

}
