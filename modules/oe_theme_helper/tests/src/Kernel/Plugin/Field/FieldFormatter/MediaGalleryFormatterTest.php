<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\Kernel\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\media\Entity\Media;
use Drupal\Tests\media\Traits\MediaTypeCreationTrait;
use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the media gallery formatter.
 *
 * @group oe_theme
 *
 * @group batch2
 */
class MediaGalleryFormatterTest extends AbstractKernelTestBase {

  use MediaTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'entity_reference',
    'entity_test',
    'field',
    'file',
    'filter',
    'file_link',
    'link',
    'media',
    'oe_media',
    'oe_media_iframe',
    'oe_media_oembed_mock',
    'options',
    'node',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('entity_test');
    $this->installEntitySchema('media');
    $this->installEntitySchema('file');
    $this->installSchema('file', 'file_usage');

    $this->installConfig([
      'file',
      'field',
      'media',
      'oe_media',
      'oe_media_iframe',
    ]);

    // Call the install hook of the Media module.
    module_load_include('install', 'media');
    media_install();

    // Add a copyright field to some of the media bundles used in the test. Use
    // different names to make sure that the correct settings are used in the
    // formatter.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_image_copyright',
      'type' => 'string',
      'entity_type' => 'media',
      'cardinality' => 1,
    ]);
    $field_storage->save();
    FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'image',
    ])->save();

    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_remote_video_copyright',
      'type' => 'string',
      'entity_type' => 'media',
      'cardinality' => 1,
    ]);
    $field_storage->save();
    FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'remote_video',
    ])->save();

    // Add an additional string field to use as source for attributes.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_extra_title',
      'type' => 'string',
      'entity_type' => 'media',
      'cardinality' => 1,
    ]);
    $field_storage->save();
    FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'image',
    ])->save();

    // Create an entity_reference field to test the formatter.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_test',
      'type' => 'entity_reference',
      'entity_type' => 'entity_test',
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
      'settings' => [
        'target_type' => 'media',
      ],
    ]);
    $field_storage->save();
    FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'entity_test',
    ])->save();
  }

  /**
   * Test the formatter rendering.
   */
  public function testFormatter(): void {
    $filepath = \Drupal::service('extension.list.theme')->getPath('oe_theme') . '/tests/fixtures/example_1.jpeg';
    $file = \Drupal::service('file.repository')->writeData(file_get_contents($filepath), 'public://' . basename($filepath));
    $file->setPermanent();
    $file->save();

    $image_media = Media::create([
      'bundle' => 'image',
      'name' => 'Test image title',
      'oe_media_image' => [
        [
          'target_id' => $file->id(),
          // @todo Randomise to catch escaping vulnerabilities.
          'alt' => 'Alt text for test image.',
        ],
      ],
      'field_image_copyright' => 'Copyright for test image ©',
      'field_extra_title' => 'Extra image title',
    ]);
    $image_media->save();

    $video_media = Media::create([
      'bundle' => 'remote_video',
      'oe_media_oembed_video' => 'https://www.youtube.com/watch?v=1-g73ty9v04',
      'field_remote_video_copyright' => 'Copyright for test remote video ©',
    ]);
    $video_media->save();

    // Create a video iframe media. Video iframes render the markup as string
    // and not as html tag, so with this test we fully cover the iframe plugin.
    $filepath = \Drupal::service('extension.list.theme')->getPath('oe_theme') . '/tests/fixtures/placeholder.png';
    $thumbnail = \Drupal::service('file.repository')->writeData(file_get_contents($filepath), 'public://' . basename($filepath));
    $thumbnail->setPermanent();
    $thumbnail->save();
    $iframe_media = Media::create([
      'bundle' => 'video_iframe',
      'name' => 'Test video iframe title',
      'oe_media_iframe' => '<iframe src="http://example.com" width="800" height="600" allowFullScreen="true"></iframe>',
      'oe_media_iframe_thumbnail' => [
        'target_id' => $thumbnail->id(),
        // @todo Randomise to catch escaping vulnerabilities.
        'alt' => 'Alt text for test video iframe.',
      ],
    ]);
    $iframe_media->save();

    // Create a test entity and reference the three medias.
    $entity = EntityTest::create([
      'field_test' => [
        $image_media->id(),
        $video_media->id(),
        $iframe_media->id(),
      ],
    ]);
    $entity->save();

    $view_builder = \Drupal::entityTypeManager()->getViewBuilder('entity_test');
    $build = $view_builder->viewField($entity->get('field_test'), [
      'type' => 'oe_theme_helper_media_gallery',
      'label' => 'hidden',
      'settings' => [
        'bundle_settings' => [
          'image' => [
            'caption' => 'field_extra_title',
          ],
          'remote_video' => [
            'caption' => 'name',
          ],
          'video_iframe' => [
            'caption' => 'name',
          ],
        ],
      ],
    ]);
    $crawler = new Crawler($this->renderRoot($build));
    $gallery = $crawler->filter('section.ecl-gallery');
    $this->assertCount(1, $gallery);
    $items = $gallery->filter('li.ecl-gallery__item');
    $this->assertCount(3, $items);

    // Test the contents of the first item.
    $image_node = $items->first()->filter('img');
    $this->assertEquals('Alt text for test image.', $image_node->attr('alt'));
    $this->assertStringEndsWith('/example_1.jpeg', $image_node->attr('src'));
    $caption = $items->first()->filter('.ecl-gallery__description');
    $this->assertStringContainsString('Extra image title', $caption->html());
    $this->assertEmpty($caption->filter('.ecl-gallery__meta')->html());

    // Test the second gallery item.
    $this->assertStringStartsWith(
      '/media/oembed?url=https%3A//www.youtube.com/watch%3Fv%3D1-g73ty9v04&max_width=0&max_height=0&hash=',
      $items->eq(1)->filter('.ecl-gallery__item-link')->attr('data-ecl-gallery-item-embed-src')
    );

    $expected_thumbnail_name = 'FRPRzhRHyt8zGp5-d-luvJDnIb03oXDJUp5LtL4UeDI.jpg';

    $image_node = $items->eq(1)->filter('img');
    $this->assertEquals("Energy, let's save it!", $image_node->attr('alt'));
    $this->assertStringEndsWith('/oembed_thumbnails/' . $expected_thumbnail_name, $image_node->attr('src'));
    $caption = $items->eq(1)->filter('.ecl-gallery__description');
    $this->assertStringContainsString($video_media->label(), $caption->html());
    $this->assertEmpty($caption->filter('.ecl-gallery__meta')->html());

    // Test the third item.
    $this->assertEquals(
      'http://example.com',
      $items->eq(2)->filter('.ecl-gallery__item-link')->attr('data-ecl-gallery-item-embed-src')
    );
    $image_node = $items->eq(2)->filter('img');
    $this->assertEquals('Alt text for test video iframe.', $image_node->attr('alt'));
    $this->assertStringEndsWith('/placeholder.png', $image_node->attr('src'));
    $caption = $items->eq(2)->filter('.ecl-gallery__description');
    $this->assertStringContainsString('Test video iframe title', $caption->html());
    $this->assertEmpty($caption->filter('.ecl-gallery__meta')->html());

    // Test that all the cache tags are present and have bubbled up.
    $this->assertEqualsCanonicalizing([
      'file:1',
      'file:2',
      'file:3',
      'media:1',
      'media:2',
      'media:3',
    ], $build['#cache']['tags']);

    // Assert rendering when also the copyright mapping is configured.
    $build = $view_builder->viewField($entity->get('field_test'), [
      'type' => 'oe_theme_helper_media_gallery',
      'label' => 'hidden',
      'settings' => [
        'bundle_settings' => [
          'image' => [
            'caption' => 'name',
            'copyright' => 'field_image_copyright',
          ],
          'remote_video' => [
            'caption' => 'name',
            'copyright' => 'field_remote_video_copyright',
          ],
        ],
      ],
    ]);
    $crawler = new Crawler($this->renderRoot($build));
    $gallery = $crawler->filter('section.ecl-gallery');
    $items = $gallery->filter('li.ecl-gallery__item');
    $this->assertCount(3, $items);

    // Test the contents of the first item.
    $caption = $items->first()->filter('.ecl-gallery__description');
    $this->assertStringContainsString($image_media->label(), $caption->html());
    $copyright = $caption->filter('.ecl-gallery__meta');
    $this->assertCount(1, $copyright);
    $this->assertEquals('Copyright for test image ©', $copyright->html());

    // Test the second gallery item.
    $caption = $items->eq(1)->filter('.ecl-gallery__description');
    $this->assertStringContainsString($video_media->label(), $caption->html());
    $copyright = $caption->filter('.ecl-gallery__meta');
    $this->assertCount(1, $copyright);
    $this->assertEquals('Copyright for test remote video ©', $copyright->html());

    // Test rendering when:
    // - an image style is passed in the configuration;
    // - no mapping is provided for the caption attribute;
    // - a wrong field name is provided for the caption attribute.
    $build = $view_builder->viewField($entity->get('field_test'), [
      'type' => 'oe_theme_helper_media_gallery',
      'label' => 'hidden',
      'settings' => [
        'image_style' => 'medium',
        'remote_video' => [
          'caption' => 'field_non_existent',
        ],
      ],
    ]);
    $crawler = new Crawler($this->renderRoot($build));
    $gallery = $crawler->filter('section.ecl-gallery');
    $items = $gallery->filter('li.ecl-gallery__item');
    $this->assertCount(3, $items);

    // Test the contents of the first item.
    $image_node = $items->first()->filter('img');
    $this->assertEquals('Alt text for test image.', $image_node->attr('alt'));
    $this->assertStringContainsString('/files/styles/medium/public/example_1.jpeg?itok=', $image_node->attr('src'));
    $caption = $items->first()->filter('.ecl-gallery__description');
    $this->assertStringContainsString($image_media->label(), $caption->html());

    // Test the second gallery item.
    $this->assertStringStartsWith(
      '/media/oembed?url=https%3A//www.youtube.com/watch%3Fv%3D1-g73ty9v04&max_width=0&max_height=0&hash=',
      $items->eq(1)->filter('.ecl-gallery__item-link')->attr('data-ecl-gallery-item-embed-src')
    );
    $image_node = $items->eq(1)->filter('img');
    $this->assertEquals("Energy, let's save it!", $image_node->attr('alt'));
    $this->assertStringContainsString(
      '/files/styles/medium/public/oembed_thumbnails/' . $expected_thumbnail_name . '?itok=',
      $image_node->attr('src')
    );
    $caption = $items->eq(1)->filter('.ecl-gallery__description');
    $this->assertStringContainsString($video_media->label(), $caption->html());
    $this->assertEmpty($caption->filter('.ecl-gallery__meta')->html());
  }

}
