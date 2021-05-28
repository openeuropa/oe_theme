<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\Kernel\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\UrlHelper;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * Tests the featured media formatter.
 *
 * @group batch2
 */
class FeaturedMediaFormatterTest extends AbstractKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'field',
    'node',
    'media',
    'file',
    'filter',
    'image',
    'views',
    'entity_browser',
    'options',
    'media_avportal',
    'oe_media',
    'oe_media_avportal',
    'oe_media_iframe',
    'oe_media_oembed_mock',
    'media_avportal_mock',
    'oe_content_featured_media_field',
    'system',
    'file_link',
    'link',
    'options',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installSchema('file', 'file_usage');
    $this->installEntitySchema('media');
    $this->installEntitySchema('node');
    $this->installEntitySchema('file');
    $this->installConfig([
      'media',
      'image',
      'file',
      'field',
      'system',
      'oe_media',
      'media_avportal',
      'oe_media_avportal',
      'oe_media_iframe',
      'oe_content_featured_media_field',
    ]);

    // Call the install hook of the Media module.
    module_load_include('install', 'media');
    media_install();

    // Create a content type.
    $type = NodeType::create(['name' => 'Test content type', 'type' => 'test_ct']);
    $type->save();

    FieldStorageConfig::create([
      'field_name' => 'featured_media_field',
      'entity_type' => 'node',
      'type' => 'oe_featured_media',
      'cardinality' => 1,
      'entity_types' => ['node'],
    ])->save();

    FieldConfig::create([
      'label' => 'Featured media field',
      'field_name' => 'featured_media_field',
      'entity_type' => 'node',
      'bundle' => 'test_ct',
      'settings' => [
        'handler' => 'default:media',
        'handler_settings' => [
          'target_bundles' => [
            'av_portal_photo' => 'av_portal_photo',
            'av_portal_video' => 'av_portal_video',
            'image' => 'image',
            'remote_video' => 'remote_video',
            'video_iframe' => 'video_iframe',
          ],
        ],
        'sort' => [
          'field' => '_none',
        ],
        'auto_create' => '0',
      ],
      'required' => FALSE,
    ])->save();
  }

  /**
   * Test the featured media formatter.
   */
  public function testFormatter(): void {
    $this->container->get('file_system')->copy($this->root . '/core/misc/druplicon.png', 'public://example.jpg');
    $image = File::create(['uri' => 'public://example.jpg']);
    $image->save();

    $media = $this->container->get('entity_type.manager')
      ->getStorage('media')->create([
        'bundle' => 'image',
        'name' => 'Test image',
        'oe_media_image' => [
          [
            'target_id' => $image->id(),
            'alt' => 'default alt',
          ],
        ],
      ]);
    $media->save();

    $values = [
      'type' => 'test_ct',
      'title' => 'My node title',
      'featured_media_field' => [
        [
          'target_id' => $media->id(),
          'caption' => 'Image caption text',
        ],
      ],
    ];

    /** @var \Drupal\node\NodeInterface $node */
    $node = Node::create($values);
    $node->save();

    $node_storage = $this->container->get('entity_type.manager')->getStorage('node');
    $node_storage->resetCache();

    /** @var \Drupal\node\NodeInterface $node */
    $node = $node_storage->load($node->id());

    $view_builder = $this->container->get('entity_type.manager')->getViewBuilder('node');

    $build = $view_builder->viewField($node->get('featured_media_field'), [
      'type' => 'oe_theme_helper_featured_media_formatter',
    ]);

    $this->assertRendering($this->renderRoot($build), [
      'count' => [
        '.ecl-media-container .ecl-media-container__media[alt="default alt"][src="' . file_create_url('public://example.jpg') . '"]' => 1,
        '.ecl-media-container .ecl-media-container__caption' => 1,
      ],
      'equals' => [
        '.ecl-media-container__caption' => 'Image caption text',
      ],
    ]);

    $media = $this->container->get('entity_type.manager')
      ->getStorage('media')->create([
        'bundle' => 'remote_video',
        'oe_media_oembed_video' => 'https://www.youtube.com/watch?v=OkPW9mK5Vw8',
        'status' => 1,
      ]);
    $media->save();

    $values = [
      'type' => 'test_ct',
      'title' => 'My video node',
      'featured_media_field' => [
        [
          'target_id' => $media->id(),
          'caption' => 'Video caption text',
        ],
      ],
    ];

    $node = Node::create($values);
    $node->save();

    $node_storage->resetCache();
    $node = $node_storage->load($node->id());

    $build = $view_builder->viewField($node->get('featured_media_field'), [
      'type' => 'oe_theme_helper_featured_media_formatter',
    ]);

    $this->assertRendering($this->renderRoot($build), [
      'count' => [
        '.ecl-media-container .ecl-media-container__media' => 1,
        '.ecl-media-container iframe.media-oembed-content[src*="' . UrlHelper::encodePath('https://www.youtube.com/watch?v=OkPW9mK5Vw8') . '"]' => 1,
        '.ecl-media-container .ecl-media-container__caption' => 1,
      ],
      'equals' => [
        '.ecl-media-container__caption' => 'Video caption text',
      ],
    ]);

    $media = $this->container->get('entity_type.manager')
      ->getStorage('media')->create([
        'bundle' => 'av_portal_photo',
        'oe_media_avportal_photo' => 'P-038924/00-15',
      ]);
    $media->save();

    /** @var \Drupal\image\Plugin\Field\FieldType\ImageItem $thumbnail */
    $thumbnail = $media->get('thumbnail')->first();
    /** @var \Drupal\Core\Entity\Plugin\DataType\EntityAdapter $file */
    $file = $thumbnail->get('entity')->getTarget();

    $values = [
      'type' => 'test_ct',
      'title' => 'My AV portal photo node',
      'featured_media_field' => [
        [
          'target_id' => $media->id(),
          'caption' => 'Image caption text',
        ],
      ],
    ];

    /** @var \Drupal\node\NodeInterface $node */
    $node = Node::create($values);
    $node->save();

    $node_storage = $this->container->get('entity_type.manager')->getStorage('node');
    $node_storage->resetCache();

    /** @var \Drupal\node\NodeInterface $node */
    $node = $node_storage->load($node->id());

    $view_builder = $this->container->get('entity_type.manager')->getViewBuilder('node');

    $build = $view_builder->viewField($node->get('featured_media_field'), [
      'type' => 'oe_theme_helper_featured_media_formatter',
    ]);

    $this->assertRendering($this->renderRoot($build), [
      'count' => [
        '.ecl-media-container .ecl-media-container__media[alt="' . $thumbnail->get('alt')->getString() . '"][src="' . file_create_url($file->get('uri')->getString()) . '"]' => 1,
        '.ecl-media-container .ecl-media-container__caption' => 1,
      ],
      'equals' => [
        '.ecl-media-container__caption' => 'Image caption text',
      ],
    ]);

    $media = $this->container->get('entity_type.manager')
      ->getStorage('media')->create([
        'bundle' => 'av_portal_video',
        'oe_media_avportal_video' => 'I-163162',
        'status' => 1,
      ]);
    $media->save();

    $values = [
      'type' => 'test_ct',
      'title' => 'My AV portal video node',
      'featured_media_field' => [
        [
          'target_id' => $media->id(),
          'caption' => 'AV Video caption text',
        ],
      ],
    ];

    $node = Node::create($values);
    $node->save();

    $node_storage->resetCache();
    $node = $node_storage->load($node->id());

    $build = $view_builder->viewField($node->get('featured_media_field'), [
      'type' => 'oe_theme_helper_featured_media_formatter',
    ]);

    $this->assertRendering($this->renderRoot($build), [
      'count' => [
        '.ecl-media-container .ecl-media-container__media--ratio-16-9' => 1,
        '.ecl-media-container .ecl-media-container__caption' => 1,
      ],
      'equals' => [
        '.ecl-media-container__caption' => 'AV Video caption text',
      ],
      'contains' => [
        'iframe.media-avportal-content' => 'I-163162',
      ],
    ]);

    $media = $this->container->get('entity_type.manager')
      ->getStorage('media')->create([
        'bundle' => 'video_iframe',
        'oe_media_iframe' => '<iframe src="http://example.com"></iframe>',
        'oe_media_iframe_ratio' => '4_3',
        'status' => 1,
      ]);
    $media->save();

    $values = [
      'type' => 'test_ct',
      'title' => 'My iframe video node',
      'featured_media_field' => [
        [
          'target_id' => $media->id(),
          'caption' => 'Iframe Video caption text',
        ],
      ],
    ];

    $node = Node::create($values);
    $node->save();

    $node_storage->resetCache();
    $node = $node_storage->load($node->id());

    $build = $view_builder->viewField($node->get('featured_media_field'), [
      'type' => 'oe_theme_helper_featured_media_formatter',
    ]);

    $this->assertRendering($this->renderRoot($build), [
      'count' => [
        '.ecl-media-container .ecl-media-container__media--ratio-4-3' => 1,
        '.ecl-media-container .ecl-media-container__caption' => 1,
      ],
      'equals' => [
        '.ecl-media-container__caption' => 'Iframe Video caption text',
      ],
      'contains' => [
        'iframe.media-avportal-content' => 'example.com',
      ],
    ]);
  }

}
